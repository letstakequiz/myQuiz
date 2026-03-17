<?php
/**
 * Updevix Quiz Platform - Admin Upload Questions (CSV/Excel)
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pdo = getDBConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $quizId = (int)($_POST['quiz_id'] ?? 0);

        if ($quizId <= 0) {
            $error = 'Please select a quiz.';
        } elseif (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please select a valid CSV file.';
        } else {
            $file = $_FILES['csv_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
                $error = 'Only CSV and Excel files are allowed.';
            } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
                $error = 'File size exceeds the maximum limit (5MB).';
            } else {
                $uploadDir = __DIR__ . '/../uploads/questions/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $tmpFile = $file['tmp_name'];

                if ($ext === 'csv') {
                    $result = parseCSVQuestions($tmpFile, $quizId);
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'For Excel files, please save as CSV first and upload the CSV file.';
                }
            }
        }
    }
}

// Get quizzes for dropdown
$allQuizzes = $pdo->query("SELECT id, title FROM quizzes ORDER BY title")->fetchAll();

$pageTitle = 'Upload Questions';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><i class="fas fa-upload"></i> Upload Questions</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo sanitize($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo sanitize($success); ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Upload Form -->
    <div class="admin-form-card" style="max-width: none;">
        <h3><i class="fas fa-file-upload"></i> Upload CSV File</h3>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label class="form-label">Select Quiz *</label>
                <select name="quiz_id" class="form-input" required>
                    <option value="">-- Select a Quiz --</option>
                    <?php foreach ($allQuizzes as $q): ?>
                        <option value="<?php echo $q['id']; ?>"><?php echo sanitize($q['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">CSV File *</label>
                <div class="upload-zone" onclick="document.getElementById('csvFile').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h3 id="uploadFileName">Click to select file</h3>
                    <p>Supported formats: CSV (.csv)</p>
                    <p style="font-size: 12px;">Maximum file size: 5MB</p>
                </div>
                <input type="file" id="csvFile" name="csv_file" accept=".csv,.xlsx,.xls" style="display: none;" required onchange="document.getElementById('uploadFileName').textContent = this.files[0] ? this.files[0].name : 'Click to select file'">
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-upload"></i> Upload & Import Questions
            </button>
        </form>
    </div>

    <!-- CSV Format Guide -->
    <div class="admin-form-card" style="max-width: none;">
        <h3><i class="fas fa-info-circle"></i> CSV Format Guide</h3>

        <p style="color: var(--text-secondary); margin-bottom: 16px;">
            Your CSV file should have the following columns in order:
        </p>

        <div class="table-responsive" style="margin-bottom: 20px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Description</th>
                        <th>Required</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>Question</strong></td><td>The question text</td><td>Yes</td></tr>
                    <tr><td><strong>Option A</strong></td><td>First option</td><td>For MCQ</td></tr>
                    <tr><td><strong>Option B</strong></td><td>Second option</td><td>For MCQ</td></tr>
                    <tr><td><strong>Option C</strong></td><td>Third option</td><td>For MCQ</td></tr>
                    <tr><td><strong>Option D</strong></td><td>Fourth option</td><td>For MCQ</td></tr>
                    <tr><td><strong>Correct Answer</strong></td><td>A, B, C, D or text</td><td>Yes</td></tr>
                    <tr><td><strong>Question Type</strong></td><td>mcq, aptitude, coding</td><td>Yes</td></tr>
                    <tr><td><strong>Marks</strong></td><td>Points for the question</td><td>No (default: 1)</td></tr>
                    <tr><td><strong>Language</strong></td><td>For coding questions</td><td>No</td></tr>
                </tbody>
            </table>
        </div>

        <div style="background: var(--bg-body); border-radius: var(--radius-sm); padding: 16px; margin-bottom: 16px;">
            <p style="font-weight: 600; margin-bottom: 8px;">Example CSV:</p>
            <code style="font-size: 12px; line-height: 1.8; color: var(--text-secondary);">
                Question,Option A,Option B,Option C,Option D,Correct Answer,Question Type<br>
                "What is 2+2?","3","4","5","6","B","mcq"<br>
                "Write a hello world program","","","","","print('Hello')","coding"
            </code>
        </div>

        <a href="/assets/sample_questions.csv" download class="btn btn-secondary btn-sm">
            <i class="fas fa-download"></i> Download Sample CSV
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
