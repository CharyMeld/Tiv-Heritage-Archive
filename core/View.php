<?php
/**
 * Tiv Culture Archive - View Engine
 * Handles template rendering
 */

class View
{
    private array $data = [];
    private string $content = '';

    /**
     * Render a view with layout
     */
    public function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $this->data = $data;

        // Extract data to variables
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = BASE_PATH . '/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: {$view}");
        }

        $this->content = ob_get_clean();

        // Render layout with content
        if ($layout) {
            $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';

            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                throw new Exception("Layout file not found: {$layout}");
            }
        } else {
            echo $this->content;
        }
    }

    /**
     * Render view without layout
     */
    public function renderPartial(string $view, array $data = []): void
    {
        $this->data = $data;
        extract($data);

        $viewFile = BASE_PATH . '/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: {$view}");
        }
    }

    /**
     * Get rendered content (used in layouts)
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Include a partial view
     */
    public function partial(string $partial, array $data = []): void
    {
        $data = array_merge($this->data, $data);
        extract($data);

        $partialFile = BASE_PATH . '/views/partials/' . $partial . '.php';

        if (file_exists($partialFile)) {
            include $partialFile;
        } else {
            throw new Exception("Partial file not found: {$partial}");
        }
    }

    /**
     * Get data value
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if data key exists
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Set page title
     */
    public function title(string $title): string
    {
        return $title . ' | ' . SITE_NAME;
    }
}

/**
 * Global view helper function
 */
function partial(string $partial, array $data = []): void
{
    global $view;
    if (isset($view) && $view instanceof View) {
        $view->partial($partial, $data);
    } else {
        $tempView = new View();
        $tempView->partial($partial, $data);
    }
}

/**
 * Generate pagination HTML
 */
function pagination(array $pagination, string $baseUrl): string
{
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav class="pagination">';
    $separator = strpos($baseUrl, '?') !== false ? '&' : '?';

    // Previous button
    if ($pagination['has_prev']) {
        $prevPage = $pagination['current_page'] - 1;
        $html .= '<a href="' . $baseUrl . $separator . 'page=' . $prevPage . '" class="pagination-link pagination-prev">&laquo; Previous</a>';
    }

    // Page numbers
    $html .= '<span class="pagination-info">Page ' . $pagination['current_page'] . ' of ' . $pagination['total_pages'] . '</span>';

    // Next button
    if ($pagination['has_next']) {
        $nextPage = $pagination['current_page'] + 1;
        $html .= '<a href="' . $baseUrl . $separator . 'page=' . $nextPage . '" class="pagination-link pagination-next">Next &raquo;</a>';
    }

    $html .= '</nav>';

    return $html;
}
