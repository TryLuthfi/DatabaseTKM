<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ListArea extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MListArea');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        $filters = [
            'regional' => strtoupper(trim((string) $this->input->get('regional'))),
            'province_id' => trim((string) $this->input->get('province_id')),
            'regency_id' => trim((string) $this->input->get('regency_id')),
            'district_id' => trim((string) $this->input->get('district_id')),
            'village_id' => trim((string) $this->input->get('village_id')),
            'keyword' => trim((string) $this->input->get('keyword')),
        ];

        $data['title'] = 'Master Area';
        $data['judul'] = 'Master Area';
        $data['filters'] = $filters;
        $data['isReady'] = $this->MListArea->wilayahTablesReady();
        $data['summary'] = $data['isReady'] ? $this->MListArea->getSummary() : [];
        $data['regionalOptions'] = $data['isReady'] ? $this->MListArea->getRegionalOptions() : [];
        $data['provinceOptions'] = $data['isReady'] ? $this->MListArea->getProvinceOptions('', $filters['regional']) : [];
        $data['regencyOptions'] = ($data['isReady'] && $filters['province_id'] !== '')
            ? $this->MListArea->getRegencyOptions($filters['province_id'], '', $filters['regional'])
            : [];
        $data['districtOptions'] = ($data['isReady'] && $filters['regency_id'] !== '')
            ? $this->MListArea->getDistrictOptions($filters['regency_id'])
            : [];
        $data['villageOptions'] = ($data['isReady'] && $filters['district_id'] !== '')
            ? $this->MListArea->getVillageOptions($filters['district_id'])
            : [];
        $data['provinceRows'] = $data['isReady'] ? $this->MListArea->getProvinceRows($filters) : [];
        $data['regencyRows'] = $data['isReady'] ? $this->MListArea->getRegencyRows($filters) : [];
        $data['districtRows'] = $data['isReady'] ? $this->MListArea->getDistrictRows($filters) : [];
        $data['villageRows'] = $data['isReady'] ? $this->MListArea->getVillageRows($filters) : [];
        $data['maxRows'] = MListArea::MAX_TABLE_ROWS;

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('ListArea/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveProvince()
    {
        $this->guard();
        $payload = [
            'id' => $this->normalizeCode($this->input->post('id'), 2),
            'name' => $this->normalizeName($this->input->post('name')),
        ];

        if ($payload['id'] === '' || $payload['name'] === '') {
            $this->flashAndBack('error', 'Kode dan nama provinsi wajib diisi.');
            return;
        }

        $result = $this->MListArea->insertProvince($payload);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Provinsi berhasil ditambahkan.' : 'Gagal menambahkan provinsi. Pastikan kode belum digunakan.');
    }

    public function updateProvince($id = '')
    {
        $this->guard();
        $id = $this->normalizeCode($id, 2);
        $name = $this->normalizeName($this->input->post('name'));
        if ($id === '' || $name === '') {
            $this->flashAndBack('error', 'Data provinsi belum lengkap.');
            return;
        }

        $result = $this->MListArea->updateProvince($id, ['name' => $name]);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Provinsi berhasil diperbarui.' : 'Gagal memperbarui provinsi.');
    }

    public function deleteProvince($id = '')
    {
        $this->guard();
        $result = $this->MListArea->deleteProvince($this->normalizeCode($id, 2));
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Provinsi berhasil dihapus.' : 'Provinsi tidak bisa dihapus karena masih memiliki data turunan atau sedang digunakan.');
    }

    public function saveRegency()
    {
        $this->guard();
        $payload = [
            'id' => $this->normalizeCode($this->input->post('id'), 4),
            'province_id' => $this->normalizeCode($this->input->post('province_id'), 2),
            'name' => $this->normalizeName($this->input->post('name')),
        ];

        if ($payload['id'] === '' || $payload['province_id'] === '' || $payload['name'] === '') {
            $this->flashAndBack('error', 'Kode, provinsi, dan nama kab/kota wajib diisi.');
            return;
        }

        $result = $this->MListArea->insertRegency($payload);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Kab/Kota berhasil ditambahkan.' : 'Gagal menambahkan kab/kota. Pastikan kode belum digunakan dan provinsi valid.');
    }

    public function updateRegency($id = '')
    {
        $this->guard();
        $id = $this->normalizeCode($id, 4);
        $payload = [
            'province_id' => $this->normalizeCode($this->input->post('province_id'), 2),
            'name' => $this->normalizeName($this->input->post('name')),
        ];

        if ($id === '' || $payload['province_id'] === '' || $payload['name'] === '') {
            $this->flashAndBack('error', 'Data kab/kota belum lengkap.');
            return;
        }

        $result = $this->MListArea->updateRegency($id, $payload);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Kab/Kota berhasil diperbarui.' : 'Gagal memperbarui kab/kota.');
    }

    public function deleteRegency($id = '')
    {
        $this->guard();
        $result = $this->MListArea->deleteRegency($this->normalizeCode($id, 4));
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Kab/Kota berhasil dihapus.' : 'Kab/Kota tidak bisa dihapus karena masih memiliki kecamatan atau sedang digunakan.');
    }

    public function saveDistrict()
    {
        $this->guard();
        $payload = [
            'id' => $this->normalizeCode($this->input->post('id'), 7),
            'regency_id' => $this->normalizeCode($this->input->post('regency_id'), 4),
            'name' => $this->normalizeName($this->input->post('name')),
        ];

        if ($payload['id'] === '' || $payload['regency_id'] === '' || $payload['name'] === '') {
            $this->flashAndBack('error', 'Kode, kab/kota, dan nama kecamatan wajib diisi.');
            return;
        }

        $result = $this->MListArea->insertDistrict($payload);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Kecamatan berhasil ditambahkan.' : 'Gagal menambahkan kecamatan. Pastikan kode belum digunakan dan kab/kota valid.');
    }

    public function updateDistrict($id = '')
    {
        $this->guard();
        $id = $this->normalizeCode($id, 7);
        $payload = [
            'regency_id' => $this->normalizeCode($this->input->post('regency_id'), 4),
            'name' => $this->normalizeName($this->input->post('name')),
        ];

        if ($id === '' || $payload['regency_id'] === '' || $payload['name'] === '') {
            $this->flashAndBack('error', 'Data kecamatan belum lengkap.');
            return;
        }

        $result = $this->MListArea->updateDistrict($id, $payload);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Kecamatan berhasil diperbarui.' : 'Gagal memperbarui kecamatan.');
    }

    public function deleteDistrict($id = '')
    {
        $this->guard();
        $result = $this->MListArea->deleteDistrict($this->normalizeCode($id, 7));
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Kecamatan berhasil dihapus.' : 'Kecamatan tidak bisa dihapus karena masih memiliki desa/kelurahan atau sedang digunakan.');
    }

    public function saveVillage()
    {
        $this->guard();
        $payload = [
            'id' => $this->normalizeCode($this->input->post('id'), 10),
            'district_id' => $this->normalizeCode($this->input->post('district_id'), 7),
            'name' => $this->normalizeName($this->input->post('name')),
        ];

        if ($payload['id'] === '' || $payload['district_id'] === '' || $payload['name'] === '') {
            $this->flashAndBack('error', 'Kode, kecamatan, dan nama desa/kelurahan wajib diisi.');
            return;
        }

        $result = $this->MListArea->insertVillage($payload);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Desa/Kelurahan berhasil ditambahkan.' : 'Gagal menambahkan desa/kelurahan. Pastikan kode belum digunakan dan kecamatan valid.');
    }

    public function updateVillage($id = '')
    {
        $this->guard();
        $id = $this->normalizeCode($id, 10);
        $payload = [
            'district_id' => $this->normalizeCode($this->input->post('district_id'), 7),
            'name' => $this->normalizeName($this->input->post('name')),
        ];

        if ($id === '' || $payload['district_id'] === '' || $payload['name'] === '') {
            $this->flashAndBack('error', 'Data desa/kelurahan belum lengkap.');
            return;
        }

        $result = $this->MListArea->updateVillage($id, $payload);
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Desa/Kelurahan berhasil diperbarui.' : 'Gagal memperbarui desa/kelurahan.');
    }

    public function deleteVillage($id = '')
    {
        $this->guard();
        $result = $this->MListArea->deleteVillage($this->normalizeCode($id, 10));
        $this->flashAndBack($result ? 'success' : 'error', $result ? 'Desa/Kelurahan berhasil dihapus.' : 'Desa/Kelurahan tidak bisa dihapus karena sedang digunakan.');
    }

    public function getProvinceOptions()
    {
        $this->jsonOptions($this->MListArea->getProvinceOptions($this->input->get('q'), $this->input->get('regional')));
    }

    public function getRegencyOptions()
    {
        $this->jsonOptions($this->MListArea->getRegencyOptions($this->input->get('province_id'), $this->input->get('q'), $this->input->get('regional')));
    }

    public function getDistrictOptions()
    {
        $this->jsonOptions($this->MListArea->getDistrictOptions($this->input->get('regency_id'), $this->input->get('q')));
    }

    public function getVillageOptions()
    {
        $this->jsonOptions($this->MListArea->getVillageOptions($this->input->get('district_id'), $this->input->get('q')));
    }

    private function guard()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            exit;
        }
    }

    private function flashAndBack($type, $message)
    {
        $this->session->set_flashdata($type, $message);
        redirect('ListArea' . $this->currentQueryString());
    }

    private function currentQueryString()
    {
        $query = trim((string) $this->input->post('query_string'));
        if ($query === '') {
            $query = trim((string) $this->input->get('query_string'));
        }
        if ($query === '') {
            return '';
        }

        return '?' . ltrim($query, '?');
    }

    private function normalizeCode($value, $maxLength)
    {
        $value = preg_replace('/[^0-9]/', '', (string) $value);
        return substr($value, 0, (int) $maxLength);
    }

    private function normalizeName($value)
    {
        $value = strtoupper(trim((string) $value));
        return preg_replace('/\s+/', ' ', $value);
    }

    private function jsonOptions(array $rows)
    {
        $results = array_map(static function ($row) {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'text' => (string) ($row['text'] ?? ($row['name'] ?? '')),
                'name' => (string) ($row['name'] ?? ''),
            ];
        }, $rows);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['results' => $results]));
    }
}
