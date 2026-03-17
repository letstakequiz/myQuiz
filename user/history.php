<?php
/**
 * Updevix Quiz Platform - User Quiz History
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$results = getUserResults($_SESSION['user_id']);

$pageTitle = 'My Results';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container section">
    <h2 class="section-title">My Quiz History</h2>
    <p class="section-subtitle">View all your quiz attempts and scores</p>

    <?php if (empty($results)): ?>
        <div class="card">
            <div class="card-body text-center" style="padding: 60px;">
                <i class="fas fa-clipboard-list" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                <h3 style="color: var(--text-muted);">No quiz attempts yet</h3>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Take your first quiz to see your results here.</p>
                <a href="/user/dashboard.php" class="btn btn-primary">Browse Quizzes</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Quiz</th>
                        <th>Score</th>
                        <th>Correct</th>
                        <th>Wrong</th>
                        <th>Skipped</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $i => $r): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><strong><?php echo sanitize($r['quiz_title']); ?></strong></td>
                            <td>
                                <span class="badge <?php echo $r['percentage'] >= 50 ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $r['percentage']; ?>%
                                </span>
                            </td>
                            <td style="color: #10b981;"><?php echo $r['correct_answers']; ?></td>
                            <td style="color: #ef4444;"><?php echo $r['wrong_answers']; ?></td>
                            <td style="color: #f59e0b;"><?php echo $r['skipped_questions']; ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($r['submitted_at'])); ?></td>
                            <td>
                                <a href="/user/result.php?id=<?php echo $r['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
