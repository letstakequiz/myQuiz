<?php
/**
 * Updevix Quiz Platform - Helper Functions
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Register a new user
 */
function registerUser($fullName, $email, $password, $phone = null) {
    $pdo = getDBConnection();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $hashedPassword, $phone]);
    
    return ['success' => true, 'message' => 'Registration successful! Please login.', 'user_id' => $pdo->lastInsertId()];
}

/**
 * Login user
 */
function loginUser($email, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, full_name, email, password, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    
    if (!$user['is_active']) {
        return ['success' => false, 'message' => 'Your account has been deactivated.'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    
    return ['success' => true, 'user' => $user];
}

/**
 * Login admin
 */
function loginAdmin($username, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, username, full_name, password FROM admin WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['password'])) {
        return ['success' => false, 'message' => 'Invalid credentials.'];
    }
    
    return ['success' => true, 'admin' => $admin];
}

/**
 * Generate OTP
 */
function generateOTP($email, $purpose = 'password_reset') {
    $pdo = getDBConnection();
    
    // Invalidate previous OTPs
    $stmt = $pdo->prepare("UPDATE otp_verifications SET is_used = 1 WHERE email = ? AND purpose = ? AND is_used = 0");
    $stmt->execute([$email, $purpose]);
    
    $otp = str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    
    $stmt = $pdo->prepare("INSERT INTO otp_verifications (email, otp_code, purpose, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $otp, $purpose, $expiresAt]);
    
    return $otp;
}

/**
 * Verify OTP
 */
function verifyOTP($email, $otp, $purpose = 'password_reset') {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM otp_verifications WHERE email = ? AND otp_code = ? AND purpose = ? AND is_used = 0 AND expires_at > NOW()");
    $stmt->execute([$email, $otp, $purpose]);
    $record = $stmt->fetch();
    
    if (!$record) {
        return false;
    }
    
    // Mark as used
    $stmt = $pdo->prepare("UPDATE otp_verifications SET is_used = 1 WHERE id = ?");
    $stmt->execute([$record['id']]);
    
    return true;
}

/**
 * Reset password
 */
function resetPassword($email, $newPassword) {
    $pdo = getDBConnection();
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    return $stmt->execute([$hashedPassword, $email]);
}

/**
 * Get all active quizzes
 */
function getActiveQuizzes() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT q.*, 
               COUNT(qu.id) as question_count,
               a.full_name as created_by_name
        FROM quizzes q
        LEFT JOIN questions qu ON q.id = qu.quiz_id
        LEFT JOIN admin a ON q.created_by = a.id
        WHERE q.is_active = 1
        GROUP BY q.id
        ORDER BY q.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get quiz by ID
 */
function getQuizById($quizId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND is_active = 1");
    $stmt->execute([$quizId]);
    return $stmt->fetch();
}

/**
 * Get questions for a quiz
 */
function getQuizQuestions($quizId, $randomize = false) {
    $pdo = getDBConnection();
    $orderBy = $randomize ? 'RAND()' : 'sort_order ASC, id ASC';
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY $orderBy");
    $stmt->execute([$quizId]);
    return $stmt->fetchAll();
}

/**
 * Submit quiz and calculate results
 */
function submitQuiz($userId, $quizId, $userAnswers, $timeTaken) {
    $pdo = getDBConnection();
    
    $questions = getQuizQuestions($quizId);
    $quiz = getQuizById($quizId);
    
    $correct = 0;
    $wrong = 0;
    $skipped = 0;
    $totalMarks = 0;
    $obtainedMarks = 0;
    
    $pdo->beginTransaction();
    
    try {
        // Calculate results
        foreach ($questions as $q) {
            $totalMarks += $q['marks'];
            $userAnswer = isset($userAnswers[$q['id']]) ? trim($userAnswers[$q['id']]) : '';
            
            if (empty($userAnswer)) {
                $skipped++;
                continue;
            }
            
            $isCorrect = false;
            if ($q['question_type'] === 'coding') {
                // For coding questions, just store the answer (manual review needed)
                $isCorrect = false; // Admin reviews coding answers
            } else {
                $isCorrect = (strtoupper(trim($userAnswer)) === strtoupper(trim($q['correct_answer'])));
            }
            
            if ($isCorrect) {
                $correct++;
                $obtainedMarks += $q['marks'];
            } else {
                $wrong++;
            }
        }
        
        $totalQuestions = count($questions);
        $percentage = $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100, 2) : 0;
        
        // Insert result
        $stmt = $pdo->prepare("INSERT INTO results (user_id, quiz_id, total_questions, correct_answers, wrong_answers, skipped_questions, total_marks, obtained_marks, percentage, time_taken_seconds) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $quizId, $totalQuestions, $correct, $wrong, $skipped, $totalMarks, $obtainedMarks, $percentage, $timeTaken]);
        $resultId = $pdo->lastInsertId();
        
        // Insert individual answers
        $stmt = $pdo->prepare("INSERT INTO answers (result_id, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)");
        foreach ($questions as $q) {
            $userAnswer = isset($userAnswers[$q['id']]) ? trim($userAnswers[$q['id']]) : '';
            $isCorrect = 0;
            if (!empty($userAnswer) && $q['question_type'] !== 'coding') {
                $isCorrect = (strtoupper(trim($userAnswer)) === strtoupper(trim($q['correct_answer']))) ? 1 : 0;
            }
            $stmt->execute([$resultId, $q['id'], $userAnswer, $isCorrect]);
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'result_id' => $resultId,
            'total_questions' => $totalQuestions,
            'correct' => $correct,
            'wrong' => $wrong,
            'skipped' => $skipped,
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage' => $percentage,
            'passed' => $percentage >= ($quiz['passing_marks'] / $quiz['total_marks'] * 100)
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Quiz submission error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to submit quiz. Please try again.'];
    }
}

/**
 * Get result by ID
 */
function getResultById($resultId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, q.title as quiz_title, q.duration_minutes, q.passing_marks, q.total_marks as quiz_total_marks,
               u.full_name as user_name, u.email as user_email
        FROM results r
        JOIN quizzes q ON r.quiz_id = q.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$resultId]);
    return $stmt->fetch();
}

/**
 * Get detailed answers for a result
 */
function getResultAnswers($resultId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT a.*, q.question_text, q.question_type, q.option_a, q.option_b, q.option_c, q.option_d, 
               q.correct_answer, q.marks, q.coding_language
        FROM answers a
        JOIN questions q ON a.question_id = q.id
        WHERE a.result_id = ?
        ORDER BY q.sort_order ASC
    ");
    $stmt->execute([$resultId]);
    return $stmt->fetchAll();
}

/**
 * Get user's quiz history
 */
function getUserResults($userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, q.title as quiz_title
        FROM results r
        JOIN quizzes q ON r.quiz_id = q.id
        WHERE r.user_id = ?
        ORDER BY r.submitted_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Check if user has already taken a quiz
 */
function hasUserTakenQuiz($userId, $quizId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM results WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$userId, $quizId]);
    return $stmt->fetch() !== false;
}

/**
 * Parse CSV file for question upload
 */
function parseCSVQuestions($filePath, $quizId) {
    $questions = [];
    $file = fopen($filePath, 'r');
    
    if ($file === false) {
        return ['success' => false, 'message' => 'Could not open file.'];
    }
    
    // Skip header row
    $header = fgetcsv($file);
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, question_type, marks, coding_language, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    $sortOrder = 1;
    
    while (($row = fgetcsv($file)) !== false) {
        if (count($row) < 7) continue;
        
        $questionText = trim($row[0]);
        $optionA = trim($row[1] ?? '');
        $optionB = trim($row[2] ?? '');
        $optionC = trim($row[3] ?? '');
        $optionD = trim($row[4] ?? '');
        $correctAnswer = trim($row[5] ?? '');
        $questionType = strtolower(trim($row[6] ?? 'mcq'));
        $marks = isset($row[7]) ? (int)$row[7] : 1;
        $codingLang = isset($row[8]) ? trim($row[8]) : null;
        
        if (empty($questionText)) continue;
        
        if (!in_array($questionType, ['mcq', 'aptitude', 'coding'])) {
            $questionType = 'mcq';
        }
        
        $stmt->execute([
            $quizId, $questionText, $optionA, $optionB, $optionC, $optionD,
            $correctAnswer, $questionType, $marks, $codingLang, $sortOrder
        ]);
        
        $count++;
        $sortOrder++;
    }
    
    fclose($file);
    
    // Update quiz total marks
    updateQuizTotalMarks($quizId);
    
    return ['success' => true, 'count' => $count, 'message' => "$count questions imported successfully."];
}

/**
 * Update quiz total marks based on questions
 */
function updateQuizTotalMarks($quizId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE quizzes SET total_marks = (SELECT COALESCE(SUM(marks), 0) FROM questions WHERE quiz_id = ?) WHERE id = ?");
    $stmt->execute([$quizId, $quizId]);
}

/**
 * Get leaderboard for a quiz
 */
function getQuizLeaderboard($quizId, $limit = 10) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name, u.email
        FROM results r
        JOIN users u ON r.user_id = u.id
        WHERE r.quiz_id = ?
        ORDER BY r.percentage DESC, r.time_taken_seconds ASC
        LIMIT ?
    ");
    $stmt->execute([$quizId, $limit]);
    return $stmt->fetchAll();
}
