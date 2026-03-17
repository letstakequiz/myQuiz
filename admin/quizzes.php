<?php
/**
 * Updevix Quiz Platform - Admin Quizzes Management
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

        if ($action === 'create') {
            $title = sanitize($_POST['title'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $duration = (int)($_POST['duration_minutes'] ?? 30);
            $passingMarks = (int)($_POST['passing_marks'] ?? 0);
            $isRandomized = isset($_POST['is_randomized']) ? 1 : 0;

            if (empty($title)) {
                $error = 'Quiz title is required.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO quizzes (title, description, duration_minutes, passing_marks, is_randomized, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $duration, $passingMarks, $isRandomized, $_SESSION['admin_id']]);
                $success = 'Quiz created successfully!';
            }
        }

        if ($action === 'toggle') {
            $quizId = (int)($_POST['quiz_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE quizzes SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$quizId]);
            $success = 'Quiz status updated.';
        }

        if ($action === 'delete') {
            $quizId = (int)($_POST['quiz_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
            $stmt->execute([$quizId]);
            $success = 'Quiz deleted successfully.';
        }
    }
}

// Get all quizzes
$quizzes = $pdo->query("
    SELECT q.*, COUNT(qu.id) as question_count, a.full_name as created_by_name
    FROM quizzes q
    LEFT JOIN questions qu ON q.id = qu.quiz_id
    LEFT JOIN admin a ON q.created_by = a.id
    GROUP BY q.id
    ORDER BY q.created_at DESC
")->fetchAll();

$pageTitle = 'Manage Quizzes';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><i class="fas fa-clipboard-list"></i> Manage Quizzes</h1>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">
        <i class="fas fa-plus"></i> Create Quiz
    </button>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?></div>
<?php endif; ?>

<!-- Quizzes Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Duration</th>
                    <th>Questions</th>
                    <th>Total Marks</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quizzes)): ?>
                    <tr><td colspan="8" class="text-center" style="padding: 40px; color: var(--text-muted);">No quizzes yet. Create your first quiz!</td></tr>
                <?php else: ?>
                    <?php foreach ($quizzes as $q): ?>
                        <tr>
                            <td>#<?php echo $q['id']; ?></td>
                            <td>
                                <strong><?php echo sanitize($q['title']); ?></strong>
                                <?php if ($q['is_randomized']): ?>
                                    <span class="badge badge-info" style="font-size: 10px;">Randomized</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $q['duration_minutes']; ?> min</td>
                            <td><?php echo $q['question_count']; ?></td>
                            <td><?php echo $q['total_marks']; ?></td>
                            <td>
                                <span class="badge <?php echo $q['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $q['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($q['created_at'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="/admin/questions.php?quiz_id=<?php echo $q['id']; ?>" class="action-btn" title="Manage Questions">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="quiz_id" value="<?php echo $q['id']; ?>">
                                        <button type="submit" class="action-btn" title="Toggle Status">
                                            <i class="fas fa-<?php echo $q['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this quiz and all its questions?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="quiz_id" value="<?php echo $q['id']; ?>">
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

<!-- Create Quiz Modal -->
<div id="createModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div class="admin-form-card animate-scale-in" style="max-width: 600px; width: 100%;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="margin:0; border:none; padding:0;"><i class="fas fa-plus-circle"></i> Create New Quiz</h3>
            <button onclick="document.getElementById('createModal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label class="form-label">Quiz Title *</label>
                <input type="text" name="title" class="form-input" placeholder="e.g., Java Programming Quiz" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" placeholder="Brief description of the quiz..." rows="3"></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" class="form-input" value="30" min="5" max="180">
                </div>
                <div class="form-group">
                    <label class="form-label">Passing Marks</label>
                    <input type="number" name="passing_marks" class="form-input" value="5" min="0">
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_randomized" value="1">
                    <span class="form-label" style="margin:0;">Randomize question order</span>
                </label>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Quiz</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
