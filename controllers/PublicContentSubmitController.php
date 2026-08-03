<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/ContentSubmission.php';
require_once BASE_PATH . '/models/ContentItem.php';

class PublicContentSubmitController extends Controller
{
    private array $sectionMeta = [
        'language'   => ['title' => 'Language',   'color' => '#5C3A21', 'icon' => '&#128172;'],
        'literature' => ['title' => 'Literature',  'color' => '#4a7c59', 'icon' => '&#128212;'],
        'culture'    => ['title' => 'Culture',     'color' => '#8B5E3C', 'icon' => '&#127981;'],
        'history'    => ['title' => 'History',     'color' => '#6B4226', 'icon' => '&#128336;'],
        'archive'    => ['title' => 'Archive',     'color' => '#5C3A21', 'icon' => '&#128452;'],
    ];

    private array $validSubs = [
        'language'   => ['alphabet'],
        'literature' => ['folktales', 'stories', 'poems'],
        'culture'    => ['traditions', 'attire', 'marriage-customs'],
        'history'    => ['origins', 'migration', 'timeline'],
        'archive'    => ['documents', 'audio', 'publications'],
    ];

    public function form(string $section, string $sub): void
    {
        if (!$this->valid($section, $sub)) {
            $this->redirect(url('/'));
            return;
        }

        $errors  = $_SESSION['errors']    ?? [];
        $old     = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        $this->render('content/submit-form', [
            'title'       => 'Submit ' . ContentItem::subcategoryLabel($sub) . ' | Tiv Heritage Archive',
            'section'     => $section,
            'sub'         => $sub,
            'sectionMeta' => $this->sectionMeta[$section],
            'subLabel'    => ContentItem::subcategoryLabel($sub),
            'errors'      => $errors,
            'old'         => $old,
            'currentPage' => $section,
        ]);
    }

    public function submit(string $section, string $sub): void
    {
        if (!$this->validateCSRF() || !$this->valid($section, $sub)) {
            $this->back();
            return;
        }

        $errors = [];
        $name   = trim($_POST['submitter_name']  ?? '');
        $email  = trim($_POST['submitter_email'] ?? '');
        $title  = trim($_POST['title']           ?? '');

        if (!$name)  $errors['submitter_name']  = 'Your name is required.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
                     $errors['submitter_email'] = 'A valid email address is required.';
        if (!$title) $errors['title']           = 'An English title is required.';

        if ($errors) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect(url("submit/{$section}/{$sub}"));
            return;
        }

        $mediaFile = null;
        $mediaType = 'none';

        if (!empty($_FILES['media_file']['name']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->handleUpload('media_file', "submissions/{$section}/{$sub}");
            if ($uploaded) {
                $mediaFile = $uploaded['path'];
                $mediaType = $uploaded['type'];
            }
        }

        $model = new ContentSubmission();
        $model->create([
            'section'         => $section,
            'subcategory'     => $sub,
            'title'           => $title,
            'tiv_title'       => trim($_POST['tiv_title']    ?? '') ?: null,
            'excerpt'         => trim($_POST['excerpt']       ?? '') ?: null,
            'tiv_excerpt'     => trim($_POST['tiv_excerpt']   ?? '') ?: null,
            'content'         => trim($_POST['content']       ?? '') ?: null,
            'tiv_content'     => trim($_POST['tiv_content']   ?? '') ?: null,
            'media_file'      => $mediaFile,
            'media_type'      => $mediaType,
            'submitter_name'  => $name,
            'submitter_email' => $email,
            'status'          => 'pending',
        ]);

        $this->redirect(url("submit/success?section={$section}&sub={$sub}"));
    }

    public function success(): void
    {
        $section  = $_GET['section'] ?? '';
        $sub      = $_GET['sub']     ?? '';

        $this->render('content/submit-success', [
            'title'    => 'Submission Received | Tiv Heritage Archive',
            'section'  => $section,
            'sub'      => $sub,
            'subLabel' => $sub ? ContentItem::subcategoryLabel($sub) : 'Content',
        ]);
    }

    private function valid(string $section, string $sub): bool
    {
        return isset($this->validSubs[$section]) && in_array($sub, $this->validSubs[$section]);
    }

    private function handleUpload(string $field, string $subDir): ?array
    {
        $file = $_FILES[$field];
        if ($file['size'] > MAX_FILE_SIZE) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $audioTypes = ['audio/mpeg','audio/ogg','audio/wav','audio/mp4','audio/webm'];
        $docTypes   = ['application/pdf','application/msword',
                       'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (in_array($mime, ALLOWED_IMAGE_TYPES))  $type = 'image';
        elseif (in_array($mime, $audioTypes))       $type = 'audio';
        elseif (in_array($mime, $docTypes))         $type = 'document';
        else                                        return null;

        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $name = uniqid('cs_', true) . '.' . $ext;
        $dir  = UPLOADS_PATH . '/content/' . $subDir;
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) return null;
        return ['path' => 'content/' . $subDir . '/' . $name, 'type' => $type];
    }
}
