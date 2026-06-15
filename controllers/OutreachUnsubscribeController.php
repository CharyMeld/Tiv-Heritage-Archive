<?php
require_once BASE_PATH . '/models/InfluentialPerson.php';

class OutreachUnsubscribeController extends Controller {

    private InfluentialPerson $people;

    public function __construct() {
        parent::__construct();
        $this->people = new InfluentialPerson();
    }

    public function unsubscribe(string $token): void {
        $person = $this->people->findByToken($token);

        if (!$person) {
            $this->render('outreach/unsubscribe', [
                'title'   => 'Unsubscribe',
                'success' => false,
                'message' => 'This unsubscribe link is invalid or has already been used.',
            ]);
            return;
        }

        if (!$person['is_unsubscribed']) {
            $this->people->update((int) $person['id'], ['is_unsubscribed' => 1]);
        }

        $this->render('outreach/unsubscribe', [
            'title'   => 'Unsubscribed',
            'success' => true,
            'name'    => $person['name'],
        ]);
    }
}
