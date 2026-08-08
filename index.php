<?php
/**
 * Tiv Culture Archive - Front Controller
 * All requests are routed through this file
 */

// Define base path
define('BASE_PATH', __DIR__);

// Load configuration files
require_once BASE_PATH . '/config/config.php';

// ============================================
// SECURITY HEADERS (set via PHP so they work
// regardless of whether mod_headers is active)
// ============================================
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https:; media-src 'self' https:;");
}
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';

// Load core classes
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/View.php';
require_once BASE_PATH . '/core/Cache.php';
require_once BASE_PATH . '/core/ApiAuth.php';

// Initialise file-based cache
Cache::init(BASE_PATH . '/storage/cache');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Create router instance
$router = new Router();

// ============================================
// DEFINE ROUTES
// ============================================

// Sitemap
$router->get('sitemap.xml', 'SitemapController', 'index');

// Public Routes
$router->get('', 'HomeController', 'index');
$router->get('about', 'HomeController', 'about');
$router->get('contact', 'HomeController', 'contact');
$router->get('privacy-policy', 'HomeController', 'privacy');
$router->get('terms-of-service', 'HomeController', 'terms');
$router->post('contact', 'HomeController', 'sendContact');
$router->post('newsletter/subscribe', 'NewsletterSubscribeController', 'subscribe');

// Archive Routes
$router->get('archive', 'ArchiveController', 'index');
$router->get('archive/{category}', 'ArchiveController', 'category');

// Detail Routes
$router->get('name/{id}', 'DetailController', 'name');
$router->get('proverb/{id}', 'DetailController', 'proverb');
$router->get('plant/{id}', 'DetailController', 'plant');
$router->get('festival/{id}', 'DetailController', 'festival');
$router->get('food/{id}', 'DetailController', 'food');
$router->get('word/{id}', 'DetailController', 'word');
$router->get('animal/{id}', 'DetailController', 'animal');

// Historical Figures Routes
$router->get('historical-figures', 'HistoricalFigureController', 'index');
$router->get('historical-figures/{category}', 'HistoricalFigureController', 'category');
$router->get('historical-figures/{category}/{subcategory}', 'HistoricalFigureController', 'subcategory');
$router->get('historical-figure/{id}', 'HistoricalFigureController', 'show');

// Timeline Routes
$router->get('timeline', 'TimelineController', 'index');
$router->get('timeline-event/{id}', 'TimelineController', 'show');

// Contribute Routes
$router->get('contribute', 'ContributeController', 'index');
$router->post('contribute', 'ContributeController', 'submit');
$router->get('contribute/success', 'ContributeController', 'success');

// Learn Routes
$router->get('learn', 'LearnController', 'index');
$router->get('learn/{id}', 'LearnController', 'show');
$router->post('learn/{id}/view',    'LearnController', 'recordView');
$router->post('learn/{id}/comment', 'LearnController', 'postComment');
$router->post('learn/{id}/react',   'LearnController', 'postReaction');

// Bible reader
$router->get('bible', 'BibleReaderController', 'index');
$router->get('bible/search', 'BibleReaderController', 'search');
$router->get('bible/verses', 'BibleReaderController', 'versesApi');
$router->get('bible/{book}/{chapter}', 'BibleReaderController', 'chapter');

// Auth Routes
$router->get('login', 'AuthController', 'loginForm');
$router->post('login', 'AuthController', 'login');
$router->get('register', 'AuthController', 'registerForm');
$router->post('register', 'AuthController', 'register');
$router->get('logout', 'AuthController', 'logout');
$router->get('forgot-password', 'AuthController', 'forgotPasswordForm');
$router->post('forgot-password', 'AuthController', 'forgotPassword');

// Profile Routes
$router->get('profile', 'ProfileController', 'index');
$router->get('profile/edit', 'ProfileController', 'edit');
$router->post('profile/edit', 'ProfileController', 'update');
$router->get('profile/submissions', 'ProfileController', 'submissions');
$router->get('profile/collection', 'ProfileController', 'collection');
$router->get('contributor', 'ContributorController', 'dashboard');
$router->get('profile/password', 'ProfileController', 'passwordForm');
$router->post('profile/password', 'ProfileController', 'updatePassword');

// Admin Routes
$router->get('admin', 'AdminController', 'index');
$router->get('admin/search', 'AdminController', 'search');
$router->get('admin/pending', 'AdminController', 'pending');
$router->post('admin/pending/{id}/approve', 'AdminController', 'approve');
$router->post('admin/pending/{id}/reject', 'AdminController', 'reject');
$router->post('admin/pending/{id}/needs-revision', 'AdminController', 'needsRevision');
$router->get('admin/content/{category}', 'AdminController', 'content');
$router->get('admin/content/{category}/create', 'AdminController', 'create');
$router->post('admin/content/{category}/create', 'AdminController', 'store');
$router->get('admin/content/{category}/{id}/edit', 'AdminController', 'edit');
$router->post('admin/content/{category}/{id}/edit', 'AdminController', 'update');
$router->post('admin/content/{category}/{id}/delete', 'AdminController', 'delete');
$router->post('admin/content/words/{id}/relations', 'AdminController', 'addWordRelation');
$router->post('admin/content/words/{id}/relations/{linkId}/delete', 'AdminController', 'removeWordRelation');
$router->post('admin/festivals/{id}/gallery/upload', 'AdminController', 'uploadGalleryPhoto');
$router->post('admin/festivals/{id}/gallery/{photoId}/delete', 'AdminController', 'deleteGalleryPhoto');
$router->get('admin/bible', 'BibleController', 'index');
$router->post('admin/bible/mine', 'BibleController', 'mine');
$router->get('admin/bible/{book}/{chapter}', 'BibleController', 'chapter');
$router->post('admin/bible/{book}/{chapter}', 'BibleController', 'saveChapter');
$router->get('admin/users', 'AdminController', 'users');
$router->post('admin/users/create', 'AdminController', 'storeUser');
$router->post('admin/users/{id}/role', 'AdminController', 'updateRole');
$router->post('admin/users/{id}/delete', 'AdminController', 'deleteUser');
$router->post('admin/users/{id}/password', 'AdminController', 'resetPassword');
$router->get('admin/settings', 'AdminController', 'settings');
$router->post('admin/settings', 'AdminController', 'updateSettings');
$router->get('admin/duplicates', 'AdminController', 'duplicates');
$router->post('admin/duplicates/delete', 'AdminController', 'deleteDuplicate');
$router->post('admin/duplicates/delete-bulk', 'AdminController', 'deleteDuplicatesBulk');
$router->get('admin/content/words/bulk-upload', 'AdminController', 'bulkUploadWordsForm');
$router->post('admin/content/words/bulk-upload', 'AdminController', 'bulkUploadWords');

// Team Member Routes (Admin)
$router->get('admin/team', 'TeamController', 'index');
$router->get('admin/team/create', 'TeamController', 'create');
$router->post('admin/team/create', 'TeamController', 'store');
$router->get('admin/team/{id}/edit', 'TeamController', 'edit');
$router->post('admin/team/{id}/edit', 'TeamController', 'update');
$router->post('admin/team/{id}/delete', 'TeamController', 'delete');

// References & Contributors
$router->get('references', 'ReferencesController', 'index');
$router->get('references/{id}', 'ReferencesController', 'show');
$router->get('references/{id}/export/{format}', 'ReferencesController', 'export');
$router->get('references/{id}/print', 'ReferencesController', 'print');
$router->post('references/{id}/save', 'ReferencesController', 'save');

// Admin — Knowledge Graph (Sources & Links)
$router->get('admin/sources',                      'KnowledgeController', 'sources');
$router->get('admin/sources/create',               'KnowledgeController', 'createSource');
$router->post('admin/sources/create',              'KnowledgeController', 'storeSource');
$router->get('admin/sources/{id}/edit',            'KnowledgeController', 'editSource');
$router->post('admin/sources/{id}/edit',           'KnowledgeController', 'updateSource');
$router->post('admin/sources/{id}/delete',         'KnowledgeController', 'deleteSource');
$router->get('admin/links',                        'KnowledgeController', 'links');
$router->get('admin/links/create',                 'KnowledgeController', 'createLink');
$router->post('admin/links/create',                'KnowledgeController', 'storeLink');
$router->post('admin/links/{id}/delete',           'KnowledgeController', 'deleteLink');

// Translation Routes
$router->get('translate', 'TranslationController', 'index');
$router->post('translate', 'TranslationController', 'translate');
$router->get('translate/history', 'TranslationController', 'history');
$router->get('translate/plans', 'TranslationController', 'plans');
$router->post('translate/feedback', 'TranslationController', 'feedback');
$router->post('translate/suggest-multi', 'TranslationController', 'storeMultiSuggestions');
$router->get('translate/suggest/{id}', 'TranslationController', 'suggestWord');
$router->post('translate/suggest/{id}', 'TranslationController', 'storeSuggestion');

// Admin Translation Routes
$router->get('admin/translation', 'AdminTranslationController', 'index');
$router->get('admin/translation/phrases', 'AdminTranslationController', 'phrases');
$router->get('admin/translation/phrases/create', 'AdminTranslationController', 'createPhrase');
$router->post('admin/translation/phrases/create', 'AdminTranslationController', 'storePhrase');
$router->get('admin/translation/phrases/{id}/edit', 'AdminTranslationController', 'editPhrase');
$router->post('admin/translation/phrases/{id}/edit', 'AdminTranslationController', 'updatePhrase');
$router->post('admin/translation/phrases/{id}/delete', 'AdminTranslationController', 'deletePhrase');
$router->get('admin/translation/rules', 'AdminTranslationController', 'rules');
$router->get('admin/translation/rules/create', 'AdminTranslationController', 'createRule');
$router->post('admin/translation/rules/create', 'AdminTranslationController', 'storeRule');
$router->get('admin/translation/rules/{id}/edit', 'AdminTranslationController', 'editRule');
$router->post('admin/translation/rules/{id}/edit', 'AdminTranslationController', 'updateRule');
$router->post('admin/translation/rules/{id}/delete', 'AdminTranslationController', 'deleteRule');
$router->get('admin/translation/feedback', 'AdminTranslationController', 'feedback');
$router->post('admin/translation/feedback/{id}/approve', 'AdminTranslationController', 'approveFeedback');
$router->post('admin/translation/feedback/{id}/reject', 'AdminTranslationController', 'rejectFeedback');
$router->post('admin/translation/feedback/{id}/convert', 'AdminTranslationController', 'convertFeedback');
// Missing words management
$router->get('admin/translation/missing-words', 'AdminTranslationController', 'missingWords');
$router->get('admin/translation/missing-words/{id}/add', 'AdminTranslationController', 'addWord');
$router->post('admin/translation/missing-words/{id}/add', 'AdminTranslationController', 'storeWord');
$router->post('admin/translation/missing-words/{id}/reject', 'AdminTranslationController', 'rejectWord');
// Word suggestions (community contributions for missing words)
$router->get('admin/translation/word-suggestions', 'AdminTranslationController', 'wordSuggestions');
$router->post('admin/translation/word-suggestions/{id}/approve', 'AdminTranslationController', 'approveSuggestion');
$router->post('admin/translation/word-suggestions/{id}/reject', 'AdminTranslationController', 'rejectSuggestion');

// API Routes (key required — enforced inside ApiController)
$router->get('api/search', 'ApiController', 'search');
$router->get('api/daily-word', 'ApiController', 'dailyWord');
$router->get('api/random-proverb', 'ApiController', 'randomProverb');

// API Key Request (public)
$router->get('api/request-key', 'ApiKeyController', 'showForm');
$router->post('api/request-key', 'ApiKeyController', 'requestKey');

// Admin — API Key Management
$router->get('admin/api-keys', 'AdminApiKeyController', 'index');
$router->post('admin/api-keys/{id}/revoke', 'AdminApiKeyController', 'revoke');
$router->post('admin/api-keys/{id}/restore', 'AdminApiKeyController', 'restore');
$router->post('admin/api-keys/{id}/limit', 'AdminApiKeyController', 'updateLimit');

// Charymeld AI Assistant
$router->post('charymeld/chat',  'CharymeldController', 'chat');
$router->get('charymeld/token', 'CharymeldController', 'token');

// Content Structure Routes (Language, Literature, Culture, History)
$router->get('{section:language|literature|culture|history}', 'ContentCategoryController', 'section');
$router->get('{section:language|literature|culture|history}/{sub}', 'ContentCategoryController', 'subcategory');

// Content Item Detail
$router->get('content-item/{id}', 'ContentCategoryController', 'itemDetail');

// Public content submission (anyone can submit, admin reviews)
$router->get('submit/{section}/{sub}', 'PublicContentSubmitController', 'form');
$router->post('submit/{section}/{sub}', 'PublicContentSubmitController', 'submit');
$router->get('submit/success', 'PublicContentSubmitController', 'success');

// Admin Alphabet Manager (vowels / consonants / digraphs)
$router->get('admin/alphabet',                  'AdminAlphabetController', 'index');
$router->get('admin/alphabet/create',           'AdminAlphabetController', 'create');
$router->post('admin/alphabet/create',          'AdminAlphabetController', 'store');
$router->get('admin/alphabet/{id}/edit',        'AdminAlphabetController', 'edit');
$router->post('admin/alphabet/{id}/edit',       'AdminAlphabetController', 'update');
$router->post('admin/alphabet/{id}/delete',     'AdminAlphabetController', 'delete');

// Admin Grammar Manager (nouns / pronouns / verbs / adjectives / sentence structure / questions)
$router->get('admin/grammar',                  'AdminGrammarController', 'index');
$router->get('admin/grammar/create',           'AdminGrammarController', 'create');
$router->post('admin/grammar/create',          'AdminGrammarController', 'store');
$router->get('admin/grammar/{id}/edit',        'AdminGrammarController', 'edit');
$router->post('admin/grammar/{id}/edit',       'AdminGrammarController', 'update');
$router->post('admin/grammar/{id}/delete',     'AdminGrammarController', 'delete');

// Admin Historical Figures CRUD
$router->get('admin/historical-figures',                                 'AdminHistoricalFigureController', 'index');
$router->get('admin/historical-figures/create',                         'AdminHistoricalFigureController', 'create');
$router->post('admin/historical-figures/create',                        'AdminHistoricalFigureController', 'store');
$router->get('admin/historical-figures/{id}/edit',                      'AdminHistoricalFigureController', 'edit');
$router->post('admin/historical-figures/{id}/edit',                     'AdminHistoricalFigureController', 'update');
$router->post('admin/historical-figures/{id}/delete',                   'AdminHistoricalFigureController', 'delete');
$router->post('admin/historical-figures/{id}/gallery/upload',           'AdminHistoricalFigureController', 'uploadGalleryPhoto');
$router->post('admin/historical-figures/{id}/gallery/{photoId}/delete', 'AdminHistoricalFigureController', 'deleteGalleryPhoto');

// Admin — Archive Intelligence (search index rebuild)
$router->get('admin/intelligence',         'ArchiveIntelligenceController', 'index');
$router->post('admin/intelligence/reindex', 'ArchiveIntelligenceController', 'reindex');

// Admin Content Items CRUD
$router->get('admin/content-items/{section}/{sub}', 'AdminContentItemController', 'index');
$router->get('admin/content-items/{section}/{sub}/create', 'AdminContentItemController', 'create');
$router->post('admin/content-items/{section}/{sub}/create', 'AdminContentItemController', 'store');
$router->get('admin/content-items/{section}/{sub}/{id}/edit', 'AdminContentItemController', 'edit');
$router->post('admin/content-items/{section}/{sub}/{id}/edit', 'AdminContentItemController', 'update');
$router->post('admin/content-items/{section}/{sub}/{id}/delete', 'AdminContentItemController', 'delete');

// Admin — review public submissions
$router->get('admin/content-items/{section}/{sub}/submissions', 'AdminContentItemController', 'submissions');
$router->post('admin/content-items/{section}/{sub}/submissions/{id}/approve', 'AdminContentItemController', 'approveSubmission');
$router->post('admin/content-items/{section}/{sub}/submissions/{id}/reject', 'AdminContentItemController', 'rejectSubmission');

// Community Routes
$router->get('community', 'CommunityController', 'index');
$router->get('community/join', 'CommunityController', 'joinForm');
$router->post('community/join', 'CommunityController', 'submitApplication');
$router->get('community/join/success', 'CommunityController', 'joinSuccess');
$router->get('community/member/{id}', 'CommunityController', 'show');

// Suggestions & Feedback Routes
$router->get('suggestions', 'SuggestionsController', 'index');
$router->post('suggestions', 'SuggestionsController', 'submit');
$router->get('suggestions/success', 'SuggestionsController', 'success');

// Admin Community Routes
$router->get('admin/community/applications', 'AdminCommunityController', 'applications');
$router->get('admin/community/applications/export', 'AdminCommunityController', 'exportApplications');
$router->get('admin/community/applications/{id}', 'AdminCommunityController', 'viewApplication');
$router->post('admin/community/applications/{id}/approve', 'AdminCommunityController', 'approve');
$router->post('admin/community/applications/{id}/reject', 'AdminCommunityController', 'reject');
$router->get('admin/community/members', 'AdminCommunityController', 'members');
$router->get('admin/community/members/export', 'AdminCommunityController', 'exportMembers');
$router->get('admin/community/members/{id}/edit', 'AdminCommunityController', 'editMember');
$router->post('admin/community/members/{id}/edit', 'AdminCommunityController', 'updateMember');
$router->post('admin/community/members/{id}/feature', 'AdminCommunityController', 'featureMember');
$router->post('admin/community/members/{id}/toggle', 'AdminCommunityController', 'toggleMember');
$router->post('admin/community/members/{id}/remove', 'AdminCommunityController', 'removeMember');

// Admin Contributors Routes
$router->get('admin/contributors', 'AdminContributorController', 'index');
$router->get('admin/contributors/{id}', 'AdminContributorController', 'show');
$router->post('admin/contributors/{id}/pay', 'AdminContributorController', 'generatePayment');

// Admin Suggestions Routes
$router->get('admin/suggestions', 'AdminSuggestionsController', 'index');
$router->get('admin/suggestions/export', 'AdminSuggestionsController', 'export');
$router->get('admin/suggestions/{id}', 'AdminSuggestionsController', 'view');
$router->post('admin/suggestions/{id}/status', 'AdminSuggestionsController', 'updateStatus');
$router->post('admin/suggestions/{id}/reply', 'AdminSuggestionsController', 'reply');

// ── Influential People & Outreach Module ──────────────────────────────────────

// Public: nominate an influential person
$router->get('nominate-influential', 'NominateController', 'index');
$router->post('nominate-influential', 'NominateController', 'submit');
$router->get('nominate-influential/success', 'NominateController', 'success');

// Public: unsubscribe from outreach emails
$router->get('outreach/unsubscribe/{token}', 'OutreachUnsubscribeController', 'unsubscribe');

// Admin: Outreach Dashboard
$router->get('admin/outreach', 'AdminInfluentialController', 'index');

// Admin: Influential People CRUD
$router->get('admin/outreach/people', 'AdminInfluentialController', 'people');
$router->get('admin/outreach/people/create', 'AdminInfluentialController', 'createPerson');
$router->post('admin/outreach/people/create', 'AdminInfluentialController', 'storePerson');
$router->get('admin/outreach/people/{id}/edit', 'AdminInfluentialController', 'editPerson');
$router->post('admin/outreach/people/{id}/edit', 'AdminInfluentialController', 'updatePerson');
$router->post('admin/outreach/people/{id}/delete', 'AdminInfluentialController', 'deletePerson');
$router->get('admin/outreach/people/{id}/send', 'AdminInfluentialController', 'sendPersonForm');
$router->post('admin/outreach/people/{id}/send', 'AdminInfluentialController', 'sendToPerson');

// Admin: Nominations
$router->get('admin/outreach/nominations', 'AdminInfluentialController', 'nominations');
$router->get('admin/outreach/nominations/{id}', 'AdminInfluentialController', 'viewNomination');
$router->post('admin/outreach/nominations/{id}/approve', 'AdminInfluentialController', 'approveNomination');
$router->post('admin/outreach/nominations/{id}/reject', 'AdminInfluentialController', 'rejectNomination');

// Admin: Email Templates
$router->get('admin/outreach/templates', 'AdminInfluentialController', 'templates');
$router->get('admin/outreach/templates/create', 'AdminInfluentialController', 'createTemplate');
$router->post('admin/outreach/templates/create', 'AdminInfluentialController', 'storeTemplate');
$router->get('admin/outreach/templates/{id}/edit', 'AdminInfluentialController', 'editTemplate');
$router->post('admin/outreach/templates/{id}/edit', 'AdminInfluentialController', 'updateTemplate');
$router->post('admin/outreach/templates/{id}/delete', 'AdminInfluentialController', 'deleteTemplate');

// Admin: Email Campaigns
$router->get('admin/outreach/campaigns', 'AdminInfluentialController', 'campaigns');
$router->get('admin/outreach/campaigns/create', 'AdminInfluentialController', 'createCampaign');
$router->post('admin/outreach/campaigns/create', 'AdminInfluentialController', 'storeCampaign');
$router->get('admin/outreach/campaigns/{id}', 'AdminInfluentialController', 'viewCampaign');
$router->post('admin/outreach/campaigns/{id}/send', 'AdminInfluentialController', 'sendCampaign');

// Admin: Discovery Assistant
$router->get('admin/outreach/discovery/search', 'AdminInfluentialController', 'discoverySearch');
$router->get('admin/outreach/discovery', 'AdminInfluentialController', 'discovery');

// ── AI Content Marketing Engine ─────────────────────────────────────────────

// Admin: Dashboard
$router->get('admin/marketing', 'AdminMarketingController', 'index');
$router->get('admin/marketing/activity', 'AdminMarketingController', 'activity');

// Admin: Content Generator
$router->get('admin/marketing/generator', 'AdminMarketingController', 'generator');
$router->post('admin/marketing/generate', 'AdminMarketingController', 'generate');
$router->get('admin/marketing/posts', 'AdminMarketingController', 'posts');
$router->get('admin/marketing/posts/{id}', 'AdminMarketingController', 'preview');
$router->post('admin/marketing/posts/{id}/approve', 'AdminMarketingController', 'approve');
$router->post('admin/marketing/posts/{id}/reject', 'AdminMarketingController', 'reject');
$router->post('admin/marketing/posts/{id}/delete', 'AdminMarketingController', 'deletePost');

// Admin: Prompt Templates
$router->get('admin/marketing/templates', 'AdminMarketingController', 'templates');
$router->get('admin/marketing/templates/create', 'AdminMarketingController', 'createTemplate');
$router->post('admin/marketing/templates/create', 'AdminMarketingController', 'storeTemplate');
$router->get('admin/marketing/templates/{id}/edit', 'AdminMarketingController', 'editTemplate');
$router->post('admin/marketing/templates/{id}/edit', 'AdminMarketingController', 'updateTemplate');
$router->post('admin/marketing/templates/{id}/delete', 'AdminMarketingController', 'deleteTemplate');
$router->post('admin/marketing/templates/{id}/default', 'AdminMarketingController', 'setDefaultTemplate');

// Admin: Image Generator
$router->get('admin/marketing/images', 'AdminMarketingController', 'images');
$router->get('admin/marketing/posts/{postId}/images', 'AdminMarketingController', 'imagePicker');
$router->post('admin/marketing/images/generate', 'AdminMarketingController', 'generateImage');
$router->post('admin/marketing/images/{id}/delete', 'AdminMarketingController', 'deleteImage');

// Admin: Content Calendar / Scheduled Posts
$router->get('admin/marketing/calendar', 'AdminMarketingScheduleController', 'calendar');
$router->get('admin/marketing/scheduled', 'AdminMarketingScheduleController', 'scheduled');
$router->get('admin/marketing/posts/{postId}/schedule', 'AdminMarketingScheduleController', 'createSchedule');
$router->post('admin/marketing/schedule/create', 'AdminMarketingScheduleController', 'storeSchedule');
$router->get('admin/marketing/schedule/{id}/edit', 'AdminMarketingScheduleController', 'editSchedule');
$router->post('admin/marketing/schedule/{id}/edit', 'AdminMarketingScheduleController', 'updateSchedule');
$router->post('admin/marketing/schedule/{id}/cancel', 'AdminMarketingScheduleController', 'cancelSchedule');

// Public: UTM click-tracking redirect
$router->get('go/{code}', 'MarketingRedirectController', 'redirect');

// Admin: Analytics / Traffic Reports / UTM Links
$router->get('admin/marketing/analytics', 'AdminMarketingAnalyticsController', 'index');
$router->get('admin/marketing/traffic', 'AdminMarketingAnalyticsController', 'traffic');
$router->get('admin/marketing/links', 'AdminMarketingAnalyticsController', 'links');
$router->get('admin/marketing/links/create', 'AdminMarketingAnalyticsController', 'createLink');
$router->post('admin/marketing/links/create', 'AdminMarketingAnalyticsController', 'storeLink');
$router->post('admin/marketing/links/{id}/delete', 'AdminMarketingAnalyticsController', 'deleteLink');

// Admin: Newsletter
$router->get('admin/marketing/newsletter', 'AdminMarketingScheduleController', 'newsletter');
$router->post('admin/marketing/newsletter/generate', 'AdminMarketingScheduleController', 'generateNewsletter');
$router->get('admin/marketing/newsletter/subscribers', 'AdminMarketingScheduleController', 'subscribers');
$router->get('admin/marketing/newsletter/{id}', 'AdminMarketingScheduleController', 'viewNewsletter');
$router->post('admin/marketing/newsletter/{id}/approve', 'AdminMarketingScheduleController', 'approveNewsletter');
$router->get('admin/marketing/newsletter/{id}/html', 'AdminMarketingScheduleController', 'downloadNewsletterHtml');
$router->get('admin/marketing/newsletter/{id}/text', 'AdminMarketingScheduleController', 'downloadNewsletterText');

// Admin: Facebook Settings / Connection / Preview / Queue
$router->get('admin/marketing/facebook/settings', 'AdminMarketingFacebookController', 'settings');
$router->post('admin/marketing/facebook/settings', 'AdminMarketingFacebookController', 'saveSettings');
$router->post('admin/marketing/facebook/test', 'AdminMarketingFacebookController', 'testConnection');
$router->post('admin/marketing/instagram/test', 'AdminMarketingFacebookController', 'testInstagramConnection');
$router->get('admin/marketing/facebook/preview/{postId}', 'AdminMarketingFacebookController', 'postPreview');
$router->get('admin/marketing/facebook/queue', 'AdminMarketingFacebookController', 'queue');
$router->post('admin/marketing/facebook/retry/{scheduleId}', 'AdminMarketingFacebookController', 'retry');

// Admin: Email Campaigns (stub)
$router->get('admin/marketing/campaigns', 'AdminMarketingController', 'emailCampaignsStub');

// Admin: Social Media Captions
$router->get('admin/marketing/social', 'AdminMarketingSocialController', 'index');
$router->get('admin/marketing/social/{postId}', 'AdminMarketingSocialController', 'view');
$router->post('admin/marketing/social/{postId}/generate', 'AdminMarketingSocialController', 'generateCaption');
$router->post('admin/marketing/social/caption/{id}/update', 'AdminMarketingSocialController', 'updateCaption');
$router->post('admin/marketing/social/caption/{id}/regenerate', 'AdminMarketingSocialController', 'regenerateCaption');

// ─────────────────────────────────────────────────────────────────────────────

// ============================================
// DISPATCH REQUEST
// ============================================

// Get the URL from query string
$url = $_GET['url'] ?? '';
$url = trim($url, '/');

// Bot / scraper protection on archive content routes
$_protectedPrefixes = ['archive', 'name/', 'proverb/', 'plant/', 'festival/', 'food/', 'word/', 'animal/', 'learn', 'historical-figures', 'historical-figure/', 'timeline', 'timeline-event/', 'references'];
foreach ($_protectedPrefixes as $_prefix) {
    if ($url === rtrim($_prefix, '/') || strpos($url, $_prefix) === 0) {
        ApiAuth::protectPage();
        break;
    }
}
unset($_protectedPrefixes, $_prefix);

// Dispatch the route
$router->dispatch($url);
