<?php
/**
 * Updevix Quiz Platform - Result Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$resultId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$result = getResultById($resultId);

if (!$result || $result['user_id'] != $_SESSION['user_id']) {
    header('Location: /user/dashboard.php');
    exit;
}

$answers = getResultAnswers($resultId);
$passed = $result['percentage'] >= ($result['passing_marks'] / max($result['quiz_total_marks'], 1) * 100);

// Format time taken
$timeTaken = $result['time_taken_seconds'] ?? 0;
$minutes = floor($timeTaken / 60);
$seconds = $timeTaken % 60;
$timeFormatted = $minutes . 'm ' . $seconds . 's';

$pageTitle = 'Quiz Results';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="result-container">
    <!-- Result Hero -->
    <div class="result-hero animate-scale-in">
        <div class="result-icon <?php echo $passed ? 'pass' : 'fail'; ?>">
            <i class="fas fa-<?php echo $passed ? 'trophy' : 'times'; ?>"></i>
        </div>
        <h1><?php echo $passed ? 'Congratulations!' : 'Better Luck Next Time!'; ?></h1>
        <p style="color: var(--text-secondary); margin-bottom: 16px;">
            <?php echo sanitize($result['quiz_title']); ?>
        </p>
        <div class="score"><?php echo $result['percentage']; ?>%</div>
        <p style="color: var(--text-muted); margin-top: 8px;">
            You scored <?php echo $result['obtained_marks']; ?> out of <?php echo $result['total_marks']; ?> marks
        </p>

        <div class="result-stats">
            <div class="result-stat correct">
                <h4><?php echo $result['correct_answers']; ?></h4>
                <p>Correct</p>
            </div>
            <div class="result-stat wrong">
                <h4><?php echo $result['wrong_answers']; ?></h4>
                <p>Wrong</p>
            </div>
            <div class="result-stat skipped">
                <h4><?php echo $result['skipped_questions']; ?></h4>
                <p>Skipped</p>
            </div>
            <div class="result-stat">
                <h4><?php echo $timeFormatted; ?></h4>
                <p>Time Taken</p>
            </div>
        </div>
    </div>

    <!-- Detailed Answer Review -->
    <section class="answer-review">
        <h2 class="section-title">Answer Review</h2>
        <p class="section-subtitle">Review your answers and see the correct solutions</p>

        <?php foreach ($answers as $i => $a): 
            $status = 'skipped';
            if (!empty($a['user_answer'])) {
                $status = $a['is_correct'] ? 'correct' : 'wrong';
            }
            if ($a['question_type'] === 'coding' && !empty($a['user_answer'])) {
                $status = 'skipped'; // Coding needs manual review
            }
        ?>
            <div class="answer-card <?php echo $status; ?> animate-fade-in">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
                    <span class="question-number" style="margin-bottom: 0;">Q<?php echo $i + 1; ?></span>
                    <span class="question-type-badge <?php echo $a['question_type']; ?>"><?php echo ucfirst($a['question_type']); ?></span>
                    <span class="answer-status <?php echo $status; ?>">
                        <?php if ($status === 'correct'): ?>
                            <i class="fas fa-check"></i> Correct
                        <?php elseif ($status === 'wrong'): ?>
                            <i class="fas fa-times"></i> Wrong
                        <?php else: ?>
                            <i class="fas fa-minus"></i> <?php echo $a['question_type'] === 'coding' ? 'Pending Review' : 'Skipped'; ?>
                        <?php endif; ?>
                    </span>
                </div>

                <p class="question-text" style="font-size: 15px;"><?php echo sanitize($a['question_text']); ?></p>

                <?php if ($a['question_type'] !== 'coding'): ?>
                    <?php if (!empty($a['option_a'])): ?>
                        <div style="display: grid; gap: 8px; margin-top: 12px;">
                            <?php 
                            $options = ['A' => $a['option_a'], 'B' => $a['option_b'], 'C' => $a['option_c'], 'D' => $a['option_d']];
                            foreach ($options as $key => $value):
                                if (empty($value)) continue;
                                $isUserAnswer = (strtoupper(trim($a['user_answer'])) === $key);
                                $isCorrectAnswer = (strtoupper(trim($a['correct_answer'])) === $key);
                                $optClass = '';
                                if ($isCorrectAnswer) $optClass = 'border: 2px solid #10b981; background: rgba(16,185,129,0.05);';
                                if ($isUserAnswer && !$isCorrectAnswer) $optClass = 'border: 2px solid #ef4444; background: rgba(239,68,68,0.05);';
                            ?>
                                <div style="padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px; <?php echo $optClass; ?>">
                                    <span style="font-weight: 600; color: var(--text-muted); min-width: 20px;"><?php echo $key; ?>.</span>
                                    <span><?php echo sanitize($value); ?></span>
                                    <?php if ($isCorrectAnswer): ?>
                                        <i class="fas fa-check-circle" style="color: #10b981; margin-left: auto;"></i>
                                    <?php endif; ?>
                                    <?php if ($isUserAnswer && !$isCorrectAnswer): ?>
                                        <i class="fas fa-times-circle" style="color: #ef4444; margin-left: auto;"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 12px;">
                            <p style="font-size: 14px;"><strong>Your Answer:</strong> <?php echo sanitize($a['user_answer'] ?: 'Not answered'); ?></p>
                            <p style="font-size: 14px; color: #10b981;"><strong>Correct Answer:</strong> <?php echo sanitize($a['correct_answer']); ?></p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="margin-top: 12px;">
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">
                            <i class="fas fa-code"></i> <?php echo sanitize($a['coding_language'] ?? 'Code'); ?> - Your submission:
                        </p>
                        <pre style="background: #1e1e2e; color: #e2e8f0; padding: 16px; border-radius: 8px; font-size: 13px; overflow-x: auto; font-family: 'Fira Code', monospace;"><?php echo sanitize($a['user_answer'] ?: 'No code submitted'); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Actions -->
    <div class="text-center mt-4" style="margin-bottom: 40px;">
        <a href="/user/dashboard.php" class="btn btn-primary btn-lg">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
        <a href="/user/history.php" class="btn btn-secondary btn-lg">
            <i class="fas fa-history"></i> View All Results
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
