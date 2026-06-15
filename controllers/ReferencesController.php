<?php
/**
 * References & Contributors Controller
 */

require_once BASE_PATH . '/models/Source.php';

class ReferencesController extends Controller
{
    public function index(): void
    {
        $model   = new Source();
        $grouped = $model->getGroupedByType();
        $total   = $model->count();

        $this->render('references/index', [
            'title'       => 'References & Contributors',
            'description' => 'All sources, references, and community contributors to the Tiv Heritage Archive',
            'grouped'     => $grouped,
            'total'       => $total,
            'typeLabels'  => Source::$typeLabels,
            'typeIcons'   => Source::$typeIcons,
            'currentPage' => 'references',
        ]);
    }
}
