<?php
/**
 * Updevix Quiz Platform - User Dashboard
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$quizzes = getActiveQuizzes();
$userResults = getUserResults($_SESSION['user_id']);

// Calculate stats
$totalQuizzesTaken = count($userResults);
$avgScore = 0;
$bestScore = 0;
if ($totalQuizzesTaken > 0) {
    $totalPercent = 0;
    foreach ($userResults as $r) {
        $totalPercent += $r['percentage'];
        if ($r['percentage'] > $bestScore) $bestScore = $r['percentage'];
    }
    $avgScore = round($totalPercent / $totalQuizzesTaken, 1);
}

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container">
        <div class="dashboard-welcome">
            <h1>Welcome back, <?php echo sanitize($_SESSION['user_name']); ?>! <span style="font-size: 28px;">&#128075;</span></h1>
            <p>Ready to challenge yourself? Pick a quiz and test your skills.</p>
        </div>
    </div>
</div>

<div class="container">
    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <h3><?php echo count($quizzes); ?></h3>
                <p>Available Quizzes</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3><?php echo $totalQuizzesTaken; ?></h3>
                <p>Quizzes Taken</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <h3><?php echo $avgScore; ?>%</h3>
                <p>Average Score</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-trophy"></i></div>
            <div class="stat-info">
                <h3><?php echo $bestScore; ?>%</h3>
                <p>Best Score</p>
            </div>
        </div>
    </div>

    <!-- Available Quizzes -->
    <section class="section">
        <h2 class="section-title">Available Quizzes</h2>
        <p class="section-subtitle">Choose a quiz to test your knowledge and skills</p>

        <?php if (empty($quizzes)): ?>
            <div class="card">
                <div class="card-body text-center" style="padding: 60px;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                    <h3 style="color: var(--text-muted);">No quizzes available yet</h3>
                    <p style="color: var(--text-muted);">Check back soon for new quizzes!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="quiz-grid">
                <?php foreach ($quizzes as $quiz): 
                    $taken = hasUserTakenQuiz($_SESSION['user_id'], $quiz['id']);
                ?>
                    <div class="quiz-card animate-fade-in">
                        <div class="quiz-card-header">
                            <div class="quiz-icon">
                                <i class="fas fa-<?php echo $quiz['question_count'] > 10 ? 'brain' : 'code'; ?>"></i>
                            </div>
                            <?php if ($taken): ?>
                                <span class="quiz-badge completed">Completed</span>
                            <?php else: ?>
                                <span class="quiz-badge active">Active</span>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo sanitize($quiz['title']); ?></h3>
                        <p><?php echo sanitize($quiz['description'] ?? 'No description available.'); ?></p>
                        <div class="quiz-meta">
                            <div class="quiz-meta-item">
                                <i class="fas fa-clock"></i> <?php echo $quiz['duration_minutes']; ?> min
                            </div>
                            <div class="quiz-meta-item">
                                <i class="fas fa-question-circle"></i> <?php echo $quiz['question_count']; ?> questions
                            </div>
                            <div class="quiz-meta-item">
                                <i class="fas fa-star"></i> <?php echo $quiz['total_marks']; ?> marks
                            </div>
                        </div>
                        <?php if ($taken): ?>
                            <a href="/user/history.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-eye"></i> View Results
                            </a>
                        <?php else: ?>
                            <a href="/user/quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-play"></i> Start Quiz
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
