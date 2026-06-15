<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/Suggestion.php';

class SuggestionsController extends Controller
{
    private Suggestion $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Suggestion();
    }

    public function index(): void
    {
        $preCategory = $_GET['category'] ?? '';
        $categories  = Suggestion::categories();
        if (!array_key_exists($preCategory, $categories)) {
            $preCategory = '';
        }

        $this->render('suggestions/index', [
            'title'       => 'Suggestions & Feedback | Tiv Heritage Archive',
            'description' => 'Share your suggestions, corrections, or feedback to help us improve the Tiv Heritage Archive.',
            'categories'  => $categories,
            'preCategory' => $preCategory,
            'currentPage' => 'suggestions',
        ]);
    }

    public function submit(): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $errors = [];

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $subject  = trim($_POST['subject'] ?? '');
        $category = $_POST['category'] ?? '';
        $message  = trim($_POST['message'] ?? '');

        if (!$fullName) {
            $errors['full_name'] = 'Full name is required.';
        }
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }
        if (!$subject) {
            $errors['subject'] = 'Subject is required.';
        }
        if (!array_key_exists($category, Suggestion::categories())) {
            $errors['category'] = 'Please select a valid category.';
        }
        if (!$message) {
            $errors['message'] = 'Message is required.';
        }

        if ($errors) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect(url('suggestions'));
            return;
        }

        $attachment = $this->handleAttachment();

        $this->model->create([
            'full_name'  => $fullName,
            'email'      => $email,
            'subject'    => $subject,
            'category'   => $category,
            'message'    => $message,
            'attachment' => $attachment,
            'status'     => 'new',
        ]);

        $this->redirect(url('suggestions/success'));
    }

    public function success(): void
    {
        $this->render('suggestions/success', [
            'title'       => 'Feedback Submitted | Tiv Heritage Archive',
            'currentPage' => 'suggestions',
        ]);
    }

    private function handleAttachment(): ?string
    {
        if (empty($_FILES['attachment']['name'])) {
            return null;
        }

        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > MAX_FILE_SIZE) {
            return null;
        }

        $allowed = array_merge(
            ALLOWED_IMAGE_EXTENSIONS,
            ['pdf', 'doc', 'docx', 'txt']
        );
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return null;
        }

        $filename = uniqid('sg_', true) . '.' . $ext;
        $dir      = UPLOADS_PATH . '/suggestions';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return 'suggestions/' . $filename;
    }
}
