<?php
require_once BASE_PATH . '/models/NewsletterSubscriber.php';

/**
 * Public, unauthenticated AJAX endpoint backing the homepage welcome popup.
 * Deliberately separate from the admin marketing newsletter controllers —
 * this is the one write path into newsletter_subscribers, the single real
 * subscriber table (marketing_newsletter_issues is unrelated content, not
 * recipients).
 */
class NewsletterSubscribeController extends Controller
{
    public function subscribe(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!Security::validateCSRFToken($token)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Your session expired — please refresh the page and try again.']);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            return;
        }

        $subscribers = new NewsletterSubscriber();
        $result = $subscribers->subscribe(
            mb_strtolower($email),
            $name !== '' ? $name : null,
            'welcome_popup',
            Security::getClientIP()
        );

        $messages = [
            'new' => 'Thank you for subscribing to the Tiv Heritage Archive Newsletter!',
            'reactivated' => 'Thank you for subscribing to the Tiv Heritage Archive Newsletter!',
            'already_subscribed' => "Welcome back! You're already subscribed to our Newsletter.",
        ];

        echo json_encode([
            'success' => true,
            'outcome' => $result['outcome'],
            'message' => $messages[$result['outcome']],
        ]);
    }
}
