<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SuperAdmin_MyRep_CityMapping extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MSuperAdmin_MyRep_Config');
    }

    public function index()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $data['title'] = 'MyRep City PIC Mapping';
        $data['judul'] = 'MyRep City PIC Mapping';
        $data['tablesReady'] = $this->MSuperAdmin_MyRep_Config->checkTablesReady();
        $data['cityPicRows'] = $this->MSuperAdmin_MyRep_Config->getCityPicMappings();
        $data['roleColumns'] = $this->MSuperAdmin_MyRep_Config->getCityPicRoleColumns();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('SuperAdmin_MyRep_CityMapping/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveBulk()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $rows = (array) $this->input->post('rows');
        if (empty($rows)) {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'Tidak ada perubahan yang dikirim.');
            redirect('SuperAdmin_MyRep_CityMapping');
            return;
        }

        $result = $this->MSuperAdmin_MyRep_Config->saveCityPicMappingsBulk($rows);
        if (!empty($result['ok'])) {
            $this->session->set_flashdata('status', 'sukses_edit');
            $this->session->set_flashdata('error_log', (int) ($result['updated'] ?? 0) . ' kota berhasil diperbarui.');
            redirect('SuperAdmin_MyRep_CityMapping');
            return;
        }

        $failed = (array) ($result['failed'] ?? []);
        $this->session->set_flashdata('status', 'gagal_edit');
        $this->session->set_flashdata(
            'error_log',
            'Sebagian gagal disimpan. Baris bermasalah: ' . (empty($failed) ? '-' : implode(', ', $failed))
        );
        redirect('SuperAdmin_MyRep_CityMapping');
    }

    public function saveCity()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $payload = [
            'id' => (int) $this->input->post('id'),
            'regional_name' => $this->input->post('regional_name'),
            'area' => $this->input->post('area'),
            'province_name' => $this->input->post('province_name'),
            'city_name' => $this->input->post('city_name'),
            'team_name' => $this->input->post('team_name'),
            'chief' => $this->input->post('chief'),
            'copy_from_id' => $this->input->post('copy_from_id'),
            'is_active' => $this->input->post('is_active') !== null ? 1 : 0,
        ];

        $result = $this->MSuperAdmin_MyRep_Config->saveCityMapping($payload);
        $this->session->set_flashdata('status', !empty($result['ok']) ? 'sukses_edit' : 'gagal_edit');
        $this->session->set_flashdata('error_log', (string) ($result['message'] ?? ''));
        redirect('SuperAdmin_MyRep_CityMapping');
    }

    public function deleteCity($id = 0)
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $result = $this->MSuperAdmin_MyRep_Config->deleteCityMapping((int) $id);
        $this->session->set_flashdata('status', !empty($result['ok']) ? 'sukses_edit' : 'gagal_edit');
        $this->session->set_flashdata('error_log', (string) ($result['message'] ?? ''));
        redirect('SuperAdmin_MyRep_CityMapping');
    }

    public function userOptions()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $term = trim((string) $this->input->get('q'));
        $page = max(1, (int) $this->input->get('page'));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $rows = $this->MSuperAdmin_MyRep_Config->searchUserOptions($term, $limit + 1, $offset);
        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            array_pop($rows);
        }

        $results = [];
        foreach ($rows as $row) {
            $nik = trim((string) ($row['nik'] ?? ''));
            if ($nik === '') {
                continue;
            }

            $name = trim((string) ($row['nama_karyawan'] ?? ''));
            $results[] = [
                'id' => $nik,
                'text' => $name !== '' ? $name : $nik,
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'results' => $results,
                'pagination' => ['more' => $hasMore],
            ]));
    }

    public function regencyOptions()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $term = trim((string) $this->input->get('q'));
        $page = max(1, (int) $this->input->get('page'));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $rows = $this->MSuperAdmin_MyRep_Config->searchRegencyOptions($term, $limit + 1, $offset);
        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            array_pop($rows);
        }

        $results = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $provinceName = trim((string) ($row['province_name'] ?? ''));
            $mappingDefault = (array) ($row['mapping_default'] ?? []);
            $mappingDefaultText = trim((string) ($mappingDefault['regional_name'] ?? '') . ' / ' . (string) ($mappingDefault['province_name'] ?? '') . ' / ' . (string) ($mappingDefault['city_name'] ?? ''), ' /');
            $results[] = [
                'id' => (string) ($row['id'] ?? ''),
                'text' => $provinceName !== '' ? $name . ' - ' . $provinceName : $name,
                'city_name' => $name,
                'province_name' => $provinceName,
                'province_alias' => (string) ($row['province_alias'] ?? $provinceName),
                'default_regional_name' => (string) ($mappingDefault['regional_name'] ?? ''),
                'default_area' => (string) ($mappingDefault['area'] ?? ''),
                'default_copy_from_id' => (string) ($mappingDefault['id'] ?? ''),
                'default_copy_from_text' => $mappingDefaultText,
            ];
        }

        $this->jsonSelect2($results, $hasMore);
    }

    public function cityMappingOptions()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $term = trim((string) $this->input->get('q'));
        $page = max(1, (int) $this->input->get('page'));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $rows = $this->MSuperAdmin_MyRep_Config->searchCityMappingOptions($term, $limit + 1, $offset);
        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            array_pop($rows);
        }

        $results = [];
        foreach ($rows as $row) {
            $cityName = trim((string) ($row['city_name'] ?? ''));
            if ($cityName === '') {
                continue;
            }
            $results[] = [
                'id' => (string) ($row['id'] ?? ''),
                'text' => trim((string) ($row['regional_name'] ?? '') . ' / ' . (string) ($row['province_name'] ?? '') . ' / ' . $cityName, ' /'),
            ];
        }

        $this->jsonSelect2($results, $hasMore);
    }

    private function jsonSelect2(array $results, $hasMore)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'results' => $results,
                'pagination' => ['more' => (bool) $hasMore],
            ]));
    }

    private function validateSuperAdminSession()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return false;
        }

        if ((string) $this->session->userdata('nama_level') !== 'Super Admin') {
            show_404();
            return false;
        }

        return true;
    }
}

