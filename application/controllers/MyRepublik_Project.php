<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MyRepublik_Project extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MMyRepublik_Project');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $selectedCity = strtoupper(trim((string) $this->input->get('city')));
        $selectedStatus = strtoupper(trim((string) $this->input->get('status')));
        $metricMode = strtoupper(trim((string) $this->input->get('metric')));
        if (!in_array($metricMode, ['HP', 'PO'], true)) {
            $metricMode = 'HP';
        }

        $data['title'] = 'Dashboard MyRep';
        $data['selectedCity'] = $selectedCity;
        $data['selectedStatus'] = $selectedStatus;
        $data['metricMode'] = $metricMode;
        $data['isReady'] = $this->MMyRepublik_Project->tablesReady();
        $data['cityOptions'] = $this->MMyRepublik_Project->getCityOptions();
        $data['statusOptions'] = $this->MMyRepublik_Project->getStatusOptions();
        $data['clusterRows'] = $data['isReady']
            ? $this->MMyRepublik_Project->getClusterRows($selectedCity, $selectedStatus, $metricMode)
            : [];
        $data['overview'] = $this->MMyRepublik_Project->getOverview($data['clusterRows']);
        $data['statusCards'] = $this->MMyRepublik_Project->getStatusCards($data['clusterRows'], $metricMode);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('MyRepublik_Project/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }
}
