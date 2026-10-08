<?php
require_once BASE_PATH . '/models/EmailUnsubscribe.php';

class NewsletterUnsubscribeController extends Controller {

    private EmailUnsubscribe $unsubscribes;

    public function __construct() {
        parent::__construct();
        $this->unsubscribes = new EmailUnsubscribe();
    }

    public function unsubscribe(string $token): void {
        $row = $this->unsubscribes->markUnsubscribed($token);

        if (!$row) {
            $this->render('newsletter/unsubscribe', [
                'title'   => 'Unsubscribe',
                'success' => false,
                'message' => 'This unsubscribe link is invalid.',
            ]);
            return;
        }

        $this->render('newsletter/unsubscribe', [
            'title'   => 'Unsubscribed',
            'success' => true,
            'email'   => $row['email'],
        ]);
    }
}
