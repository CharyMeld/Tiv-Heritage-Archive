<?php
/**
 * Contribute Controller
 */

require_once BASE_PATH . '/models/Submission.php';
require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/TivAnimal.php';
require_once BASE_PATH . '/models/DailyWord.php';

class ContributeController extends Controller
{
    private Submission $submissionModel;

    public function __construct()
    {
        parent::__construct();
        $this->submissionModel = new Submission();
    }

    /**
     * Contribution form
     */
    public function index(): void
    {
        $this->render('contribute/index', [
            'title' => 'Contribute',
            'categories' => CONTENT_CATEGORIES,
            'currentPage' => 'contribute'
        ]);
    }

    /**
     * Handle contribution submission
     */
    public function submit(): void
    {
        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        // Validate required fields
        $errors = $this->validateRequired([
            'category' => 'Category',
            'tiv_term' => 'Tiv Term',
            'english_meaning' => 'English Meaning'
        ]);

        // Validate category
        $category = $this->post('category');
        if (!array_key_exists($category, CONTENT_CATEGORIES)) {
            $errors['category'] = 'Please select a valid category.';
        }

        if (!empty($errors)) {
            $this->storeOldInput();
            $_SESSION['errors'] = $errors;
            $this->back();
            return;
        }

        // Check if this term already exists in the main content table
        $tivTerm = $this->post('tiv_term');
        if ($this->isDuplicateContent($category, $tivTerm)) {
            $this->storeOldInput();
            $_SESSION['errors'] = ['tiv_term' => '"' . htmlspecialchars($tivTerm) . '" already exists in our archive. If you have additional information, please describe it in the description field and we will review it.'];
            $this->back();
            return;
        }

        // Prepare additional data based on category
        $additionalData = $this->getAdditionalData($category);

        // Collect source (applies to all categories)
        $source = $this->post('source');
        if (!empty($source)) {
            $additionalData['source'] = $source;
        }

        // Handle audio recording upload
        $audioData = $_POST['audio_data'] ?? '';
        if (!empty($audioData) && strpos($audioData, 'data:audio/') === 0) {
            $audioFile = $this->saveAudioFile($audioData);
            if ($audioFile) {
                $additionalData['audio_file'] = $audioFile;
            }
        }

        // Handle image upload for plant, festival, food, animal
        if (in_array($category, ['plant', 'festival', 'food', 'animal']) && !empty($_FILES['image']['name'])) {
            $prefix = match($category) { 'festival' => 'festival', 'food' => 'food', 'animal' => 'animal', default => 'plant' };
            $imageFile = $this->saveImageFile($_FILES['image'], $prefix);
            if ($imageFile) {
                $additionalData['image'] = $imageFile;
            }
        }

        // For guest submissions, require email
        if (!is_logged_in()) {
            $email = $this->post('email');
            if (!Security::validateEmail($email)) {
                $this->storeOldInput();
                $_SESSION['errors'] = ['email' => 'Please enter a valid email address.'];
                $this->back();
                return;
            }
            $additionalData['contributor_email'] = $email;
            $additionalData['contributor_name'] = $this->post('contributor_name') ?: 'Anonymous';
        }

        // Create submission
        $submissionData = [
            'user_id' => $this->user['id'] ?? null,
            'category' => $category,
            'tiv_term' => $this->post('tiv_term'),
            'english_meaning' => $this->post('english_meaning'),
            'description' => $this->post('description'),
            'additional_data' => !empty($additionalData) ? json_encode($additionalData) : null,
            'status' => 'pending'
        ];

        try {
            $this->submissionModel->create($submissionData);

            Security::logActivity(
                $this->user['id'] ?? null,
                'submission_created',
                'submission',
                null,
                null,
                ['category' => $category, 'tiv_term' => $this->post('tiv_term')]
            );

            $this->clearOldInput();
            $this->redirect(url('contribute/success'));

        } catch (Exception $e) {
            $this->storeOldInput();
            $this->flash('An error occurred. Please try again.', 'error');
            $this->back();
        }
    }

    /**
     * Save uploaded image file — returns filename on success, null on failure
     */
    private function saveImageFile(array $file, string $prefix = 'plant'): ?string
    {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return null;

        if ($file['size'] > MAX_FILE_SIZE) return null;

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) return null;

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) return null;

        $uploadPath = UPLOADS_PATH . '/images';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

        $filename    = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
        $destination = $uploadPath . '/' . $filename;

        return move_uploaded_file($file['tmp_name'], $destination) ? $filename : null;
    }

    /**
     * Save base64 audio data to file
     */
    private function saveAudioFile(string $audioData): ?string
    {
        // Ensure audio uploads directory exists
        if (!is_dir(AUDIO_UPLOADS_PATH)) {
            mkdir(AUDIO_UPLOADS_PATH, 0755, true);
        }

        // Extract MIME type and base64 data
        if (preg_match('/^data:(audio\/[^;]+);base64,(.+)$/', $audioData, $matches)) {
            $mimeType = $matches[1];
            $base64Data = $matches[2];

            // Validate MIME type
            if (!in_array($mimeType, ALLOWED_AUDIO_TYPES)) {
                return null;
            }

            // Decode base64 data
            $binaryData = base64_decode($base64Data);
            if ($binaryData === false) {
                return null;
            }

            // Check file size
            if (strlen($binaryData) > MAX_AUDIO_SIZE) {
                return null;
            }

            // Determine file extension
            $extension = 'webm';
            if ($mimeType === 'audio/mp3' || $mimeType === 'audio/mpeg') {
                $extension = 'mp3';
            } elseif ($mimeType === 'audio/ogg') {
                $extension = 'ogg';
            } elseif ($mimeType === 'audio/wav') {
                $extension = 'wav';
            }

            // Generate unique filename
            $filename = 'pronunciation_' . uniqid() . '_' . time() . '.' . $extension;
            $filepath = AUDIO_UPLOADS_PATH . '/' . $filename;

            // Save file
            if (file_put_contents($filepath, $binaryData)) {
                return $filename;
            }
        }

        return null;
    }

    /**
     * Success page after submission
     */
    public function success(): void
    {
        $this->render('contribute/success', [
            'title' => 'Thank You',
            'currentPage' => 'contribute'
        ]);
    }

    /**
     * Check if the Tiv term already exists in the matching content table
     */
    private function isDuplicateContent(string $category, string $term): bool
    {
        $checks = [
            'name'     => [new TivName(),     'tiv_name'],
            'proverb'  => [new TivProverb(),  'tiv_text'],
            'plant'    => [new TivPlant(),    'tiv_name'],
            'festival' => [new TivFestival(), 'tiv_name'],
            'food'     => [new TivFood(),     'tiv_name'],
            'animal'   => [new TivAnimal(),   'tiv_name'],
            'word'     => [new DailyWord(),   'tiv_word'],
        ];

        if (!isset($checks[$category])) return false;

        [$model, $field] = $checks[$category];
        return $model->existsByField($field, $term);
    }

    /**
     * Get additional data fields based on category
     */
    private function getAdditionalData(string $category): array
    {
        $data = [];

        switch ($category) {
            case 'name':
                $data['gender'] = $this->post('gender', 'unisex');
                $data['pronunciation'] = $this->post('pronunciation');
                $data['origin_story'] = $this->post('origin_story');
                break;

            case 'proverb':
                $data['deeper_meaning'] = $this->post('deeper_meaning');
                $data['usage_context'] = $this->post('usage_context');
                break;

            case 'plant':
                $data['english_name'] = $this->post('english_name');
                $data['scientific_name'] = $this->post('scientific_name');
                $data['medicinal_uses'] = $this->post('medicinal_uses');
                $data['food_uses'] = $this->post('food_uses');
                $data['ritual_uses'] = $this->post('ritual_uses');
                break;

            case 'festival':
                $data['english_name'] = $this->post('english_name');
                $data['timing'] = $this->post('timing');
                $data['duration'] = $this->post('duration');
                $data['activities'] = $this->post('activities');
                $data['location'] = $this->post('location');
                break;

            case 'food':
                $data['english_name'] = $this->post('english_name');
                $data['ingredients'] = $this->post('ingredients');
                $data['preparation_method'] = $this->post('preparation_method');
                $data['serving_suggestions'] = $this->post('serving_suggestions');
                break;

            case 'animal':
                $data['english_name'] = $this->post('name') ?: $this->post('english_name');
                $data['animal_type'] = $this->post('animal_type', 'wild');
                $data['cultural_use'] = $this->post('cultural_use');
                break;

            case 'word':
                $data['part_of_speech'] = $this->post('part_of_speech', 'noun');
                $data['pronunciation'] = $this->post('pronunciation');
                $data['example_tiv'] = $this->post('example_tiv');
                $data['example_english'] = $this->post('example_english');
                break;
        }

        // Remove empty values
        return array_filter($data, fn($v) => $v !== null && $v !== '');
    }
}
