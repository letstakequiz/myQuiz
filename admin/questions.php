<?php
/**
 * Updevix Quiz Platform - Admin Questions Management
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pdo = getDBConnection();
$error = '';
$success = '';

$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $qQuizId = (int)($_POST['quiz_id'] ?? 0);
            $questionText = sanitize($_POST['question_text'] ?? '');
            $questionType = sanitize($_POST['question_type'] ?? 'mcq');
            $optionA = sanitize($_POST['option_a'] ?? '');
            $optionB = sanitize($_POST['option_b'] ?? '');
            $optionC = sanitize($_POST['option_c'] ?? '');
            $optionD = sanitize($_POST['option_d'] ?? '');
            $correctAnswer = sanitize($_POST['correct_answer'] ?? '');
            $marks = (int)($_POST['marks'] ?? 1);
            $codingLang = sanitize($_POST['coding_language'] ?? '');

            if (empty($questionText) || empty($correctAnswer)) {
                $error = 'Question text and correct answer are required.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, question_type, option_a, option_b, option_c, option_d, correct_answer, marks, coding_language) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$qQuizId, $questionText, $questionType, $optionA, $optionB, $optionC, $optionD, $correctAnswer, $marks, $codingLang ?: null]);
                updateQuizTotalMarks($qQuizId);
                $success = 'Question added successfully!';
                $quizId = $qQuizId;
            }
        }

        if ($action === 'delete') {
            $questionId = (int)($_POST['question_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT quiz_id FROM questions WHERE id = ?");
            $stmt->execute([$questionId]);
            $q = $stmt->fetch();

            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
            $stmt->execute([$questionId]);

            if ($q) {
                updateQuizTotalMarks($q['quiz_id']);
                $quizId = $q['quiz_id'];
            }
            $success = 'Question deleted.';
        }
    }
}

// Get quizzes for dropdown
$allQuizzes = $pdo->query("SELECT id, title FROM quizzes ORDER BY title")->fetchAll();

// Get questions
$questions = [];
$currentQuiz = null;
if ($quizId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
    $currentQuiz = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll();
}

$pageTitle = 'Manage Questions';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><i class="fas fa-question-circle"></i> Manage Questions</h1>
    <?php if ($quizId > 0): ?>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
            <i class="fas fa-plus"></i> Add Question
        </button>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?></div>
<?php endif; ?>

<!-- Quiz Selector -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0; flex: 1; min-width: 250px;">
                <label class="form-label">Select Quiz</label>
                <select name="quiz_id" class="form-input" onchange="this.form.submit()">
                    <option value="">-- Select a Quiz --</option>
                    <?php foreach ($allQuizzes as $q): ?>
                        <option value="<?php echo $q['id']; ?>" <?php echo $quizId == $q['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($q['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($quizId > 0 && $currentQuiz): ?>
    <div style="margin-bottom: 16px; color: var(--text-muted); font-size: 14px;">
        Showing <?php echo count($questions); ?> question(s) for <strong><?php echo sanitize($currentQuiz['title']); ?></strong>
    </div>

    <!-- Questions Table -->
    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Type</th>
                        <th>Correct Answer</th>
                        <th>Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($questions)): ?>
                        <tr><td colspan="6" class="text-center" style="padding: 40px; color: var(--text-muted);">No questions yet. Add some questions!</td></tr>
                    <?php else: ?>
                        <?php foreach ($questions as $i => $q): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td style="max-width: 300px;">
                                    <?php echo sanitize(mb_substr($q['question_text'], 0, 100)); ?>
                                    <?php echo mb_strlen($q['question_text']) > 100 ? '...' : ''; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $q['question_type'] === 'mcq' ? 'badge-info' : ($q['question_type'] === 'coding' ? 'badge-warning' : 'badge-success'); ?>">
                                        <?php echo ucfirst($q['question_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo sanitize(mb_substr($q['correct_answer'], 0, 50)); ?></td>
                                <td><?php echo $q['marks']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this question?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                        <button type="submit" class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php elseif ($quizId === 0): ?>
    <div class="card">
        <div class="card-body text-center" style="padding: 60px;">
            <i class="fas fa-hand-pointer" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
            <h3 style="color: var(--text-muted);">Select a quiz to manage its questions</h3>
        </div>
    </div>
<?php endif; ?>

<!-- Add Question Modal -->
<?php if ($quizId > 0): ?>
<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px; overflow-y:auto;">
    <div class="admin-form-card animate-scale-in" style="max-width: 700px; width: 100%; margin: 20px auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="margin:0; border:none; padding:0;"><i class="fas fa-plus-circle"></i> Add Question</h3>
            <button onclick="document.getElementById('addModal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">

            <div class="form-group">
                <label class="form-label">Question Text *</label>
                <textarea name="question_text" class="form-input" rows="3" placeholder="Enter the question..." required></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Question Type</label>
                    <select name="question_type" class="form-input" id="questionType" onchange="toggleOptions()">
                        <option value="mcq">MCQ</option>
                        <option value="aptitude">Aptitude</option>
                        <option value="coding">Coding</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Marks</label>
                    <input type="number" name="marks" class="form-input" value="1" min="1">
                </div>
            </div>

            <div id="optionsSection">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Option A</label>
                        <input type="text" name="option_a" class="form-input" placeholder="Option A">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option B</label>
                        <input type="text" name="option_b" class="form-input" placeholder="Option B">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option C</label>
                        <input type="text" name="option_c" class="form-input" placeholder="Option C">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option D</label>
                        <input type="text" name="option_d" class="form-input" placeholder="Option D">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Correct Answer *</label>
                <input type="text" name="correct_answer" class="form-input" placeholder="e.g., A, B, C, D or full answer text" required>
                <p class="form-hint">For MCQ: Enter the option letter (A, B, C, or D). For coding/aptitude: Enter the expected answer.</p>
            </div>

            <div class="form-group" id="codingLangSection" style="display:none;">
                <label class="form-label">Programming Language</label>
                <input type="text" name="coding_language" class="form-input" placeholder="e.g., Java, Python, C++">
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Question</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleOptions() {
    var type = document.getElementById('questionType').value;
    var options = document.getElementById('optionsSection');
    var codingLang = document.getElementById('codingLangSection');
    
    if (type === 'coding') {
        options.style.display = 'none';
        codingLang.style.display = 'block';
    } else {
        options.style.display = 'block';
        codingLang.style.display = 'none';
    }
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
