<?php
/**
 * TableViewModel - Modern Professional UI
 * Grayscale palette (#cccccc, #dddddd)
 */
class TableViewModel extends CI_Model
{
    private $modelFolderName = 'entities';
    private $statusArray = array(1 => 'enabled', 0 => 'disabled');
    private $booleanArray = array('' => '', 0 => 'No', 1 => 'Yes');
    private $defaultPagingLength = 20;
    private $idKey = 'id';

    function __construct()
    {
        parent::__construct();
        $this->load->model('crud');
        $this->load->model('tableActionModel');
        $this->load->helper('string');
    }

    public function getFilteredTableHtml($model, $conditionArray, &$message = '', $exclusionArray = array(), $action = null, $paged = true, $start = 0, $length = NULL, $resolve = true, $sort = " order by id desc ", $fields = false) {
        $page = (int)(isset($_GET['p_start']) && is_numeric($_GET['p_start'])) ? $_GET['p_start'] : 1;
        $length = (isset($_GET['p_len']) && is_numeric($_GET['p_len'])) ? $_GET['p_len'] : ($length ?: $this->defaultPagingLength);

        $this->load->model($this->modelFolderName . '/' . $model);
        if ($paged) {
            $start = $page > 1 ? ($page - 1) * $length : 0;
        }

        $data = $this->$model->getWhere($conditionArray, $totalLength, $start, $length, $resolve, $sort);
        return $this->loadTable($model, $data, $totalLength, $exclusionArray, $action, $paged, $page, $length, true, $fields);
    }

    private function loadTable($model, $data, $totalRow, $exclusionArray, $action, $paged, $page, $length, $removeId = true, $fields = false) {
        if (empty($data)) {
            return "<div style='padding: 3rem; text-align: center; border: 1px solid #dddddd; color: #777777;'>No records found in the archive.</div>";
        }

        $actionArray = (is_array($action) && empty($action)) ? $model::$tableAction : $action;
        $header = $fields ? array_map('stripFields', $fields) : $this->getHeader($model, $exclusionArray, $removeId);

        $result  = "<div class='table-container' style='overflow-x: auto; border: 1px solid #dddddd; background: #ffffff;'>";
        $result .= "<table class='table' style='width: 100%; border-collapse: collapse; font-size: 0.9375rem;'>";
        
        // Header
        $result .= "<thead style='background: #f8f8f8; border-bottom: 1px solid #dddddd;'><tr>";
        $result .= "<th style='padding: 1rem; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; color: #333333;'>S/N</th>";
        foreach ($header as $h) {
            $result .= "<th style='padding: 1rem; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; color: #333333;'>$h</th>";
        }
        if ($action !== false) {
            $result .= "<th style='padding: 1rem; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; color: #333333;'>Action</th>";
        }
        $result .= "</tr></thead>";

        // Body
        $result .= "<tbody>";
        foreach ($data as $i => $row) {
            $sn = ($paged) ? (($page - 1) * $length) + $i + 1 : $i + 1;
            $result .= "<tr style='border-bottom: 1px solid #f0f0f0; transition: background 0.2s;' onmouseover=\"this.style.background='#fcfcfc'\" onmouseout=\"this.style.background='transparent'\">";
            $result .= "<td style='padding: 1rem; color: #777777;'>$sn</td>";
            
            $labels = $fields ?: array_keys($model::$labelArray);
            foreach ($labels as $key) {
                if ($key === $this->idKey || in_array($key, $exclusionArray)) continue;
                $value = $row->$key ?? '';
                $result .= "<td style='padding: 1rem; color: #222222;'>$value</td>";
            }

            if (!empty($actionArray)) {
                $result .= "<td style='padding: 1rem;'>";
                foreach ($actionArray as $label => $link) {
                    $id = $row->{$this->idKey};
                    $result .= "<a href='".base_url("$link/$id")."' style='display: inline-block; padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; border: 1px solid #cccccc; color: #333333; margin-right: 0.5rem;'>$label</a>";
                }
                $result .= "</td>";
            }
            $result .= "</tr>";
        }
        $result .= "</tbody></table></div>";

        if ($paged && $totalRow > $length) {
            $result .= $this->generatePagedFooter($totalRow, $page, $length);
        }

        return $result;
    }

    private function generatePagedFooter($totalRow, $page, $length) {
        $totalPages = ceil($totalRow / $length);
        $result = "<div style='margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; color: #777777;'>";
        $result .= "<div>Showing " . (($page - 1) * $length + 1) . " to " . min($page * $length, $totalRow) . " of $totalRow records</div>";
        $result .= "<div style='display: flex; gap: 0.5rem;'>";
        
        for ($i = 1; $i <= $totalPages; $i++) {
            $activeStyle = ($i == $page) ? "background: #333333; color: #ffffff; border-color: #333333;" : "background: #ffffff; color: #333333; border-color: #dddddd;";
            $result .= "<a href='?p_start=$i&p_len=$length' style='display: inline-block; padding: 0.5rem 0.8rem; border: 1px solid; text-decoration: none; font-weight: 600; $activeStyle'>$i</a>";
        }
        
        $result .= "</div></div>";
        return $result;
    }

    private function getHeader($model, $exclusionArray, $removeid) {
        $result = [];
        foreach ($model::$labelArray as $key => $label) {
            if ($key === $this->idKey || in_array($key, $exclusionArray)) continue;
            $result[] = $label ?: str_replace('_', ' ', $key);
        }
        return $result;
    }
}
