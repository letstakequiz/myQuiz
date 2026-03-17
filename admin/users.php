<?php
/**
 * Updevix Quiz Platform - Admin Users Management
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pdo = getDBConnection();
$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$userId]);
            $success = 'User status updated.';
        }

        if ($action === 'delete') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $success = 'User deleted.';
        }
    }
}

// Get all users with quiz count
$users = $pdo->query("
    SELECT u.*, COUNT(r.id) as quiz_count,
           COALESCE(AVG(r.percentage), 0) as avg_score
    FROM users u
    LEFT JOIN results r ON u.id = r.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><i class="fas fa-users"></i> Manage Users</h1>
    <span class="badge badge-info"><?php echo count($users); ?> total users</span>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?></div>
<?php endif; ?>

<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Quizzes</th>
                    <th>Avg Score</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="9" class="text-center" style="padding: 40px; color: var(--text-muted);">No users registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?php echo $u['id']; ?></td>
                            <td><strong><?php echo sanitize($u['full_name']); ?></strong></td>
                            <td><?php echo sanitize($u['email']); ?></td>
                            <td><?php echo sanitize($u['phone'] ?? '-'); ?></td>
                            <td><?php echo $u['quiz_count']; ?></td>
                            <td>
                                <span class="badge <?php echo $u['avg_score'] >= 50 ? 'badge-success' : ($u['avg_score'] > 0 ? 'badge-warning' : 'badge-info'); ?>">
                                    <?php echo round($u['avg_score'], 1); ?>%
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $u['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="action-btn" title="Toggle Status">
                                            <i class="fas fa-<?php echo $u['is_active'] ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user and all their data?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
