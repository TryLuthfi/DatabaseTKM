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

    public function detail($clusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            redirect('MyRepublik_Project');
            return;
        }

        $cluster = $this->MMyRepublik_Project->getClusterDetail($clusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail cluster MyRep tidak ditemukan.');
            redirect('MyRepublik_Project');
            return;
        }

        $data = $this->buildDetailPayload($cluster, false);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('MyRepublik_Project/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function detailLegacy($rfsClusterId = 0)
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $rfsClusterId = (int) $rfsClusterId;
        if ($rfsClusterId <= 0) {
            redirect('MyRepublik_Project');
            return;
        }

        $cluster = $this->MMyRepublik_Project->getLegacyClusterDetail($rfsClusterId);
        if (empty($cluster)) {
            $this->session->set_flashdata('error', 'Detail cluster legacy RFS tidak ditemukan.');
            redirect('MyRepublik_Project');
            return;
        }

        $data = $this->buildDetailPayload($cluster, true);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('MyRepublik_Project/detail', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function buildDetailPayload($cluster, $isLegacy)
    {
        $myrepClusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
        $rfsClusterId = (int) ($cluster['rfs_cluster_id'] ?? $cluster['legacy_rfs_cluster_id'] ?? 0);

        $data['title'] = 'Detail Project MyRep';
        $data['cluster'] = $cluster;
        $data['isLegacy'] = (bool) $isLegacy;
        $data['stageTimeline'] = $this->MMyRepublik_Project->buildStageTimeline($cluster, $isLegacy);
        $data['flowSummaries'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getFlowDocumentSummaries($myrepClusterId)
            : [];
        $data['flowDocuments'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getAllFlowDocuments($myrepClusterId)
            : [];
        $data['batchPics'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getBatchPics($myrepClusterId)
            : [];
        $data['poHeaders'] = $myrepClusterId > 0
            ? $this->MMyRepublik_Project->getPoHeadersWithTermins($myrepClusterId)
            : [];
        $data['claimRows'] = $rfsClusterId > 0
            ? $this->MMyRepublik_Project->getRfsClaims($rfsClusterId)
            : [];
        $data['packageRows'] = $rfsClusterId > 0
            ? $this->MMyRepublik_Project->getRfsPackages($rfsClusterId)
            : [];

        return $data;
    }
}
