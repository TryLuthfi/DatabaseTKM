<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PO_Breakdown extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        enforce_database_all_po_access();
        $this->load->model('MPO_Monitor');
        $this->MPO_Monitor->ensureStandaloneSchema();
    }

    public function index()
    {
        $termBreakdown = $this->MPO_Monitor->getBowheerTermBreakdown();
        $summary = $this->buildSummary($termBreakdown);

        $data = [
            'title' => 'PO Breakdown',
            'termBreakdown' => $termBreakdown,
            'summary' => $summary,
        ];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('PO_Breakdown/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function buildSummary(array $rows)
    {
        $summary = [
            'project_count' => count($rows),
            'total_po' => 0,
            'term_value' => 0,
            'invoiced_amount' => 0,
            'remaining' => 0,
        ];

        foreach ($rows as $row) {
            $summary['total_po'] += (int) ($row['total_po'] ?? 0);
            foreach (($row['terms'] ?? []) as $term) {
                $summary['term_value'] += (float) ($term['term_value'] ?? 0);
                $summary['invoiced_amount'] += (float) ($term['invoiced_amount'] ?? 0);
                $summary['remaining'] += (float) ($term['remaining'] ?? 0);
            }
        }

        return $summary;
    }
}
