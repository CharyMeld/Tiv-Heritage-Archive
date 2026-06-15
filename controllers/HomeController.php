<?php
/**
 * Home Controller
 */

require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/BibleVerse.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/LearningVideo.php';
require_once BASE_PATH . '/models/TivAnimal.php';
require_once BASE_PATH . '/models/TeamMember.php';

class HomeController extends Controller
{
    private DailyWord $dailyWordModel;

    public function __construct()
    {
        parent::__construct();
        $this->dailyWordModel = new DailyWord();
    }

    /**
     * Homepage
     */
    public function index(): void
    {
        $dailyWord = $this->dailyWordModel->getToday();

        // Cache homepage featured rows for 30 minutes
        $featured = Cache::remember('home_featured', 1800, function () {
            $nameModel          = new TivName();
            $proverbModel       = new TivProverb();
            $plantModel         = new TivPlant();
            $festivalModel      = new TivFestival();
            $foodModel          = new TivFood();
            $animalModel        = new TivAnimal();
            $learningVideoModel = new LearningVideo();

            return [
                'featuredNames'         => $nameModel->recent(10),
                'featuredProverbs'      => $proverbModel->recent(10),
                'featuredPlants'        => $plantModel->recent(10),
                'featuredFestivals'     => $festivalModel->recentWithCover(8),
                'featuredFoods'         => $foodModel->recent(10),
                'featuredAnimals'       => $animalModel->recent(10),
                'learningVideos'        => $learningVideoModel->getFeatured(8),
                'festivalGalleryPhotos' => $festivalModel->getGalleryForHomepage(30),
            ];
        });

        $teamModel  = new TeamMember();
        $bibleModel = new BibleVerse();

        $dailyVerse  = $bibleModel->getDailyVerse();
        $verseTotal  = $bibleModel->count();
        // Always seed from Genesis 1:1 (offset 0).
        // The JS picks up from localStorage so returning visitors resume where they left off.
        $verseBatch  = $bibleModel->getVersesBatch(0, 20);

        $this->render('home/index', array_merge($featured, [
            'title'       => SITE_NAME . ' - ' . SITE_TAGLINE,
            'dailyWord'   => $dailyWord,
            'dailyVerse'  => $dailyVerse,
            'verseBatch'  => $verseBatch,
            'verseTotal'  => $verseTotal,
            'teamMembers' => $teamModel->getActive(),
            'currentPage' => 'home',
        ]));
    }

    /**
     * About page
     */
    public function about(): void
    {
        $teamModel = new TeamMember();
        $this->render('home/about', [
            'title'       => 'About Us',
            'teamMembers' => $teamModel->getActive(),
            'currentPage' => 'about',
        ]);
    }

    /**
     * Contact page
     */
    public function contact(): void
    {
        $this->render('home/contact', [
            'title' => 'Contact Us',
            'currentPage' => 'contact'
        ]);
    }

    /**
     * Handle contact form submission
     */
    public function sendContact(): void
    {
        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $errors = $this->validateRequired([
            'name' => 'Name',
            'email' => 'Email',
            'message' => 'Message'
        ]);

        $email = $this->post('email');
        if (!Security::validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (!empty($errors)) {
            $this->storeOldInput();
            $_SESSION['errors'] = $errors;
            $this->back();
            return;
        }

        // In a production environment, you would send an email here
        // For now, we'll just log the contact and show success

        Security::logActivity(
            $this->user['id'] ?? null,
            'contact_form_submission',
            'contact',
            null,
            null,
            [
                'name' => $this->post('name'),
                'email' => $email,
                'subject' => $this->post('subject')
            ]
        );

        $this->clearOldInput();
        $this->flash('Thank you for your message! We will get back to you soon.', 'success');
        $this->redirect(url('contact'));
    }
}
