<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Purchase_Request extends CI_Model
{
    public function get_all_purchase_request($tipe)
    {
        $where = ($tipe == 'ho') ? 'WHERE tmu.lokasi_user = "HO"' : 'WHERE tmu.lokasi_user != "HO"';
        $data = $this->db->query("
            SELECT
                tlp.*,
                tmb.nama_bowheer as nama_project,
                tmu.nama_user as nama_pembuat,
                tmlg.kota_lokasi_gudang,
                tlp.nama_project as nama_projects
            FROM
                tb_logistik_purchase_request tlp
            LEFT JOIN tb_master_bowheer tmb ON
                tmb.nama_bowheer = tlp.id_project
            LEFT JOIN tb_master_user tmu ON
                tmu.id_user = tlp.pembuat
            LEFT JOIN tb_master_logistik_lokasi_gudang tmlg ON
                tmlg.id_lokasi_gudang = tlp.lokasi_project
            $where
        ")->result_array();
        return $data;
    }

    public function get_detail_purchase_request($id)
    {
        $data = $this->db->query("
            SELECT
                tlpr.*,
                tlprd.*,
                tmlki.nama_item,
                tmlki.satuan_item,
                tmlg.kota_lokasi_gudang
            FROM
                tb_logistik_purchase_request_detail tlprd
            LEFT JOIN tb_logistik_purchase_request tlpr on
                tlprd.id_purchase_request = tlpr.id_purchase_request
            LEFT JOIN tb_master_logistik_kode_item tmlki ON
                tlprd.id_kode_item = tmlki.id_kode_item
            LEFT JOIN tb_master_logistik_lokasi_gudang tmlg ON
                tmlg.id_lokasi_gudang = tlpr.lokasi_project
            WHERE
                tlpr.id_purchase_request = '" . $id . "'
        ")->result_array();
        return $data;
    }

    public function get_all_gudang()
    {
        $data = $this->db->query("
            SELECT
                *
            FROM
                tb_master_logistik_lokasi_gudang
        ")->result_array();
        return $data;
    }

    public function update_purchase_request_detail($id, $data)
    {
        $this->db->where('id_purchase_request_detail', $id);
        return $this->db->update('tb_logistik_purchase_request_detail', $data);
    }
}
