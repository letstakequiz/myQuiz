<?php
/**
 * Updevix Quiz Platform - Admin Results View
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pdo = getDBConnection();

// Filter by quiz
$filterQuiz = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

$query = "
    SELECT r.*, u.full_name, u.email, q.title as quiz_title
    FROM results r
    JOIN users u ON r.user_id = u.id
    JOIN quizzes q ON r.quiz_id = q.id
";

if ($filterQuiz > 0) {
    $query .= " WHERE r.quiz_id = " . $filterQuiz;
}

$query .= " ORDER BY r.submitted_at DESC";

$results = $pdo->query($query)->fetchAll();

// Get quizzes for filter
$allQuizzes = $pdo->query("SELECT id, title FROM quizzes ORDER BY title")->fetchAll();

$pageTitle = 'View Results';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><i class="fas fa-chart-bar"></i> Quiz Results</h1>
    <span class="badge badge-info"><?php echo count($results); ?> results</span>
</div>

<!-- Filter -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0; flex: 1; min-width: 250px;">
                <label class="form-label">Filter by Quiz</label>
                <select name="quiz_id" class="form-input" onchange="this.form.submit()">
                    <option value="">All Quizzes</option>
                    <?php foreach ($allQuizzes as $q): ?>
                        <option value="<?php echo $q['id']; ?>" <?php echo $filterQuiz == $q['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($q['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Quiz</th>
                    <th>Score</th>
                    <th>Correct</th>
                    <th>Wrong</th>
                    <th>Skipped</th>
                    <th>Time</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="10" class="text-center" style="padding: 40px; color: var(--text-muted);">No results found.</td></tr>
                <?php else: ?>
                    <?php foreach ($results as $r):
                        $mins = floor(($r['time_taken_seconds'] ?? 0) / 60);
                        $secs = ($r['time_taken_seconds'] ?? 0) % 60;
                    ?>
                        <tr>
                            <td>#<?php echo $r['id']; ?></td>
                            <td>
                                <strong><?php echo sanitize($r['full_name']); ?></strong>
                                <br><small style="color: var(--text-muted);"><?php echo sanitize($r['email']); ?></small>
                            </td>
                            <td><?php echo sanitize($r['quiz_title']); ?></td>
                            <td>
                                <span class="badge <?php echo $r['percentage'] >= 50 ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $r['percentage']; ?>%
                                </span>
                                <br><small><?php echo $r['obtained_marks']; ?>/<?php echo $r['total_marks']; ?></small>
                            </td>
                            <td style="color: #10b981; font-weight: 600;"><?php echo $r['correct_answers']; ?></td>
                            <td style="color: #ef4444; font-weight: 600;"><?php echo $r['wrong_answers']; ?></td>
                            <td style="color: #f59e0b; font-weight: 600;"><?php echo $r['skipped_questions']; ?></td>
                            <td><?php echo $mins; ?>m <?php echo $secs; ?>s</td>
                            <td><?php echo date('M d, Y h:i A', strtotime($r['submitted_at'])); ?></td>
                            <td>
                                <a href="/admin/result-detail.php?id=<?php echo $r['id']; ?>" class="action-btn" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
