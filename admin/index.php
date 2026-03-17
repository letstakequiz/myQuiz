<?php
/**
 * Updevix Quiz Platform - Admin Dashboard
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pdo = getDBConnection();

// Get stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalQuizzes = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
$totalQuestions = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$totalResults = $pdo->query("SELECT COUNT(*) FROM results")->fetchColumn();

// Recent results
$recentResults = $pdo->query("
    SELECT r.*, u.full_name, u.email, q.title as quiz_title
    FROM results r
    JOIN users u ON r.user_id = u.id
    JOIN quizzes q ON r.quiz_id = q.id
    ORDER BY r.submitted_at DESC
    LIMIT 10
")->fetchAll();

// Recent users
$recentUsers = $pdo->query("
    SELECT * FROM users ORDER BY created_at DESC LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
</div>

<!-- Stats -->
<div class="admin-stats">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalUsers; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-clipboard-list"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalQuizzes; ?></h3>
            <p>Total Quizzes</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-question-circle"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalQuestions; ?></h3>
            <p>Total Questions</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalResults; ?></h3>
            <p>Quiz Attempts</p>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Recent Results -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h3><i class="fas fa-history"></i> Recent Quiz Attempts</h3>
            <a href="/admin/results.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Quiz</th>
                        <th>Score</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentResults)): ?>
                        <tr><td colspan="4" class="text-center" style="padding: 30px; color: var(--text-muted);">No quiz attempts yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentResults as $r): ?>
                            <tr>
                                <td>
                                    <strong><?php echo sanitize($r['full_name']); ?></strong>
                                    <br><small style="color: var(--text-muted);"><?php echo sanitize($r['email']); ?></small>
                                </td>
                                <td><?php echo sanitize($r['quiz_title']); ?></td>
                                <td>
                                    <span class="badge <?php echo $r['percentage'] >= 50 ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $r['percentage']; ?>%
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($r['submitted_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h3><i class="fas fa-user-plus"></i> New Users</h3>
            <a href="/admin/users.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentUsers)): ?>
                        <tr><td colspan="2" class="text-center" style="padding: 30px; color: var(--text-muted);">No users yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td>
                                    <strong><?php echo sanitize($u['full_name']); ?></strong>
                                    <br><small style="color: var(--text-muted);"><?php echo sanitize($u['email']); ?></small>
                                </td>
                                <td><?php echo date('M d', strtotime($u['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
