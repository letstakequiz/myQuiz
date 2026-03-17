<?php
/**
 * Updevix Quiz Platform - Quiz Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quiz = getQuizById($quizId);

if (!$quiz) {
    header('Location: /user/dashboard.php');
    exit;
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $userAnswers = $_POST['answers'] ?? [];
        $timeTaken = (int)($_POST['time_taken'] ?? 0);
        
        $result = submitQuiz($_SESSION['user_id'], $quizId, $userAnswers, $timeTaken);
        
        if ($result['success']) {
            // Clear session quiz data
            sessionStorage_clear_quiz();
            header('Location: /user/result.php?id=' . $result['result_id']);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

function sessionStorage_clear_quiz() {
    // Server-side cleanup if needed
}

$questions = getQuizQuestions($quizId, (bool)$quiz['is_randomized']);

if (empty($questions)) {
    header('Location: /user/dashboard.php');
    exit;
}

$pageTitle = $quiz['title'];
$extraCSS = '<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="quiz-container">
    <!-- Quiz Header -->
    <div class="quiz-header">
        <div class="quiz-title-section">
            <h2><?php echo sanitize($quiz['title']); ?></h2>
            <p class="quiz-progress-text" id="progressText">Question 1 of <?php echo count($questions); ?></p>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="progressBar" style="width: <?php echo (1 / count($questions) * 100); ?>%"></div>
            </div>
        </div>
        <div class="quiz-timer">
            <i class="fas fa-clock"></i>
            <span id="quizTimer"><?php echo str_pad($quiz['duration_minutes'], 2, '0', STR_PAD_LEFT); ?>:00</span>
        </div>
    </div>

    <!-- Question Navigation Dots -->
    <div class="question-dots">
        <?php foreach ($questions as $i => $q): ?>
            <div class="question-dot <?php echo $i === 0 ? 'current' : ''; ?>" onclick="navigateQuestion(<?php echo $i; ?>)">
                <?php echo $i + 1; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quiz Form -->
    <form id="quizForm" method="POST" action="">
        <input type="hidden" name="submit_quiz" value="1">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="time_taken" id="timeTaken" value="0">

        <?php foreach ($questions as $i => $q): ?>
            <div class="question-card" style="display: <?php echo $i === 0 ? 'block' : 'none'; ?>;">
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 18px;">
                    <span class="question-number">
                        <i class="fas fa-question"></i> Question <?php echo $i + 1; ?>
                    </span>
                    <span class="question-type-badge <?php echo $q['question_type']; ?>">
                        <?php echo ucfirst($q['question_type']); ?>
                    </span>
                    <span style="margin-left: auto; font-size: 13px; color: var(--text-muted);">
                        <?php echo $q['marks']; ?> mark<?php echo $q['marks'] > 1 ? 's' : ''; ?>
                    </span>
                </div>

                <p class="question-text"><?php echo sanitize($q['question_text']); ?></p>

                <?php if ($q['question_type'] === 'coding'): ?>
                    <!-- Coding Question -->
                    <?php if ($q['coding_language']): ?>
                        <div class="code-editor-wrapper">
                            <div class="code-editor-header">
                                <span><i class="fas fa-code"></i> <?php echo sanitize($q['coding_language']); ?></span>
                                <span>Write your code below</span>
                            </div>
                            <textarea name="answers[<?php echo $q['id']; ?>]" class="code-editor" placeholder="// Write your <?php echo sanitize($q['coding_language']); ?> code here..."></textarea>
                        </div>
                    <?php else: ?>
                        <textarea name="answers[<?php echo $q['id']; ?>]" class="form-input" style="min-height: 150px; font-family: monospace;" placeholder="Write your answer here..."></textarea>
                    <?php endif; ?>
                <?php elseif ($q['question_type'] === 'aptitude' && empty($q['option_a'])): ?>
                    <!-- Aptitude with text input -->
                    <div class="form-group">
                        <input type="text" name="answers[<?php echo $q['id']; ?>]" class="form-input" placeholder="Enter your answer">
                    </div>
                <?php else: ?>
                    <!-- MCQ / Aptitude with options -->
                    <div class="options-grid">
                        <?php 
                        $options = ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']];
                        foreach ($options as $key => $value): 
                            if (empty($value)) continue;
                        ?>
                            <label class="option-item" onclick="selectOption(this, <?php echo $q['id']; ?>)">
                                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $key; ?>">
                                <div class="option-radio"></div>
                                <span class="option-label"><?php echo $key; ?>.</span>
                                <span class="option-text"><?php echo sanitize($value); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Navigation -->
        <div class="quiz-nav">
            <button type="button" class="btn btn-secondary" id="prevBtn" onclick="prevQuestion()" style="visibility: hidden;">
                <i class="fas fa-arrow-left"></i> Previous
            </button>
            <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextQuestion()">
                Next <i class="fas fa-arrow-right"></i>
            </button>
            <button type="button" class="btn btn-success" id="submitBtn" onclick="confirmSubmit()" style="display: none;">
                <i class="fas fa-paper-plane"></i> Submit Quiz
            </button>
        </div>
    </form>
</div>

<!-- Spinner Overlay -->
<div class="spinner-overlay" id="spinnerOverlay">
    <div class="spinner"></div>
</div>

<script>
var currentQuestion = 0;
var totalQuestions = <?php echo count($questions); ?>;
var quizDuration = <?php echo $quiz['duration_minutes']; ?>;
var startTime = Date.now();

document.addEventListener('DOMContentLoaded', function() {
    startQuizTimer(quizDuration, function() {
        alert('Time is up! Your quiz will be submitted automatically.');
        autoSubmit();
    });
    updateNavButtons();
});

function nextQuestion() {
    if (currentQuestion < totalQuestions - 1) {
        currentQuestion++;
        navigateQuestion(currentQuestion);
        updateNavButtons();
    }
}

function prevQuestion() {
    if (currentQuestion > 0) {
        currentQuestion--;
        navigateQuestion(currentQuestion);
        updateNavButtons();
    }
}

function updateNavButtons() {
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var submitBtn = document.getElementById('submitBtn');

    prevBtn.style.visibility = currentQuestion === 0 ? 'hidden' : 'visible';
    
    if (currentQuestion === totalQuestions - 1) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-flex';
    } else {
        nextBtn.style.display = 'inline-flex';
        submitBtn.style.display = 'none';
    }
}

function confirmSubmit() {
    var answered = 0;
    var cards = document.querySelectorAll('.question-card');
    cards.forEach(function(card) {
        var inputs = card.querySelectorAll('input[type="radio"]:checked, textarea, input[type="text"]');
        inputs.forEach(function(inp) {
            if (inp.type === 'radio' && inp.checked) answered++;
            else if ((inp.type === 'text' || inp.tagName === 'TEXTAREA') && inp.value.trim()) answered++;
        });
    });

    var unanswered = totalQuestions - answered;
    var msg = 'Are you sure you want to submit the quiz?';
    if (unanswered > 0) {
        msg = 'You have ' + unanswered + ' unanswered question(s). Are you sure you want to submit?';
    }

    if (confirm(msg)) {
        autoSubmit();
    }
}

function autoSubmit() {
    var elapsed = Math.floor((Date.now() - startTime) / 1000);
    document.getElementById('timeTaken').value = elapsed;
    showSpinner();
    document.getElementById('quizForm').submit();
}

// Warn before leaving
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
