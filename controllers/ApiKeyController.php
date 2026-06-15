<?php
/**
 * Public API Key Request Controller
 */

class ApiKeyController extends Controller
{
    public function showForm(): void
    {
        $this->render('api/request-key', ['title' => 'Request an API Key']);
    }

    public function requestKey(): void
    {
        if (!$this->validateCSRF()) {
            $this->redirect(url('api/request-key'));
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');

        $errors = [];
        if (strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if ($errors) {
            $this->render('api/request-key', [
                'title'   => 'Request an API Key',
                'errors'  => $errors,
                'old'     => compact('name', 'email', 'purpose'),
            ]);
            return;
        }

        $db = Database::getInstance();

        // If this email already has an active key, return it (don't create a duplicate)
        $stmt = $db->prepare(
            "SELECT api_key FROM api_keys WHERE email = ? AND status = 'active' LIMIT 1"
        );
        $stmt->execute([$email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $this->render('api/key-issued', [
                'title'   => 'Your API Key',
                'api_key' => $existing['api_key'],
                'name'    => $name,
                'is_new'  => false,
            ]);
            return;
        }

        $key = ApiAuth::generateKey();
        $db->prepare(
            "INSERT INTO api_keys (api_key, name, email, purpose)
             VALUES (?, ?, ?, ?)"
        )->execute([$key, $name, $email, $purpose ?: null]);

        $this->render('api/key-issued', [
            'title'   => 'Your API Key',
            'api_key' => $key,
            'name'    => $name,
            'is_new'  => true,
        ]);
    }
}
