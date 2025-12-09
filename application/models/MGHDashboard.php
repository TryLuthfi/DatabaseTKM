<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHDashboard extends CI_Model
{
    // Total kamar
    public function totalRooms()
    {
        return $this->db->count_all('rooms');
    }

    // Kamar terisi (hanya tenant aktif)
    public function roomsOccupied()
    {
        $this->db->select('DISTINCT(room_id)');
        $this->db->where('active', 1);
        return $this->db->get('tenants_stay')->num_rows();
    }

    // Total penghuni aktif
    public function totalActiveTenants()
    {
        $this->db->where('active', 1);
        return $this->db->count_all_results('tenants_stay');
    }

    // Statistik penghuni masuk per bulan (grafik)
    public function tenantInPerMonth()
    {
        $this->db->select("MONTH(contract_start) AS bulan, COUNT(*) AS total");
        $this->db->from("tenants_stay");
        $this->db->group_by("MONTH(contract_start)");
        $this->db->order_by("bulan", "ASC");
        return $this->db->get()->result_array();
    }
}
