<?php
/**
 * Updevix Quiz Platform - Admin Result Detail View
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$resultId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$result = getResultById($resultId);

if (!$result) {
    header('Location: /admin/results.php');
    exit;
}

$answers = getResultAnswers($resultId);
$passed = $result['percentage'] >= ($result['passing_marks'] / max($result['quiz_total_marks'], 1) * 100);

$timeTaken = $result['time_taken_seconds'] ?? 0;
$minutes = floor($timeTaken / 60);
$seconds = $timeTaken % 60;

$pageTitle = 'Result Detail';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><i class="fas fa-chart-bar"></i> Result Detail</h1>
    <a href="/admin/results.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Results
    </a>
</div>

<!-- Result Summary -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div class="card">
        <div class="card-header"><i class="fas fa-user"></i> Student Info</div>
        <div class="card-body">
            <p><strong>Name:</strong> <?php echo sanitize($result['user_name']); ?></p>
            <p><strong>Email:</strong> <?php echo sanitize($result['user_email']); ?></p>
            <p><strong>Quiz:</strong> <?php echo sanitize($result['quiz_title']); ?></p>
            <p><strong>Submitted:</strong> <?php echo date('M d, Y h:i A', strtotime($result['submitted_at'])); ?></p>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><i class="fas fa-chart-pie"></i> Score Summary</div>
        <div class="card-body">
            <div class="dashboard-stats" style="margin-bottom: 0;">
                <div class="stat-card" style="border: none; padding: 12px; box-shadow: none;">
                    <div class="stat-info">
                        <h3 style="color: <?php echo $passed ? '#10b981' : '#ef4444'; ?>;"><?php echo $result['percentage']; ?>%</h3>
                        <p><?php echo $passed ? 'Passed' : 'Failed'; ?></p>
                    </div>
                </div>
                <div class="stat-card" style="border: none; padding: 12px; box-shadow: none;">
                    <div class="stat-info">
                        <h3><?php echo $result['obtained_marks']; ?>/<?php echo $result['total_marks']; ?></h3>
                        <p>Marks</p>
                    </div>
                </div>
                <div class="stat-card" style="border: none; padding: 12px; box-shadow: none;">
                    <div class="stat-info">
                        <h3><?php echo $minutes; ?>m <?php echo $seconds; ?>s</h3>
                        <p>Time Taken</p>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 16px; margin-top: 12px;">
                <span class="badge badge-success"><i class="fas fa-check"></i> <?php echo $result['correct_answers']; ?> Correct</span>
                <span class="badge badge-danger"><i class="fas fa-times"></i> <?php echo $result['wrong_answers']; ?> Wrong</span>
                <span class="badge badge-warning"><i class="fas fa-minus"></i> <?php echo $result['skipped_questions']; ?> Skipped</span>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Answers -->
<div class="admin-table-card">
    <div class="admin-table-header">
        <h3><i class="fas fa-list-ol"></i> Question-wise Analysis</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Type</th>
                    <th>Student's Answer</th>
                    <th>Correct Answer</th>
                    <th>Status</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($answers as $i => $a):
                    $status = 'skipped';
                    $statusClass = 'badge-warning';
                    if (!empty($a['user_answer'])) {
                        if ($a['question_type'] === 'coding') {
                            $status = 'review';
                            $statusClass = 'badge-info';
                        } elseif ($a['is_correct']) {
                            $status = 'correct';
                            $statusClass = 'badge-success';
                        } else {
                            $status = 'wrong';
                            $statusClass = 'badge-danger';
                        }
                    }
                ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td style="max-width: 300px;"><?php echo sanitize(mb_substr($a['question_text'], 0, 80)); ?><?php echo mb_strlen($a['question_text']) > 80 ? '...' : ''; ?></td>
                        <td><span class="badge badge-info"><?php echo ucfirst($a['question_type']); ?></span></td>
                        <td>
                            <?php if ($a['question_type'] === 'coding'): ?>
                                <details>
                                    <summary style="cursor: pointer; color: var(--primary);">View Code</summary>
                                    <pre style="background: #1e1e2e; color: #e2e8f0; padding: 12px; border-radius: 6px; font-size: 12px; margin-top: 8px; max-width: 300px; overflow-x: auto;"><?php echo sanitize($a['user_answer'] ?: 'No submission'); ?></pre>
                                </details>
                            <?php else: ?>
                                <?php echo sanitize($a['user_answer'] ?: '-'); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo sanitize(mb_substr($a['correct_answer'], 0, 50)); ?></td>
                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span></td>
                        <td><?php echo $a['is_correct'] ? $a['marks'] : 0; ?>/<?php echo $a['marks']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
