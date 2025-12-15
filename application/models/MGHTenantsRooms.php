<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHTenantsRooms extends CI_Model
{
    // Ambil semua kamar
    public function getAllTenantsRooms()
    {
        $data = $this->db->query('SELECT
	rooms.*,
	room_types.price_default,
	room_types.name AS type_name,
	tenants_stay.*,
	tenants.*
FROM
	rooms
join room_types ON
	room_types.id = rooms.room_type_id
	LEFT JOIN tenants_stay ON rooms.id = tenants_stay.room_id 
	LEFT JOIN tenants ON tenants_stay.tenant_id = tenants.id
ORDER BY
	rooms.id ASC')->result_array();

        log_message('error', 'query tenants rooms : ' . $this->db->last_query());

        return $data;
    }

    public function get_tenants()
    {
        return $this->db->get('tenants')->result_array();
    }

    public function get_rooms()
    {
        return $this->db->get('rooms')->result_array();
    }

    public function get_filtered($tenants = [], $rooms = [], $status = [])
    {
        $this->db->select('ts.*, t.fullname, r.name AS room_name, r.code AS room_code');
        $this->db->from('tenants_stay ts');
        $this->db->join('tenants t', 't.id = ts.tenant_id');
        $this->db->join('rooms r', 'r.id = ts.room_id');

        if (!empty($tenants)) {
            $this->db->where_in('ts.tenant_id', $tenants);
        }

        if (!empty($rooms)) {
            $this->db->where_in('ts.room_id', $rooms);
        }

        if (!empty($status)) {
            $this->db->where_in('ts.active', $status);
        }

        return $this->db->get()->result_array();
    }

    public function get_available_filters($tenants = [], $rooms = [], $status = [])
    {
        $this->db->select('ts.*, t.fullname, r.name AS room_name, r.code AS room_code');
        $this->db->from('tenants_stay ts');
        $this->db->join('tenants t', 't.id = ts.tenant_id');
        $this->db->join('rooms r', 'r.id = ts.room_id');

        if (!empty($tenants))
            $this->db->where_in('ts.tenant_id', $tenants);
        if (!empty($rooms))
            $this->db->where_in('ts.room_id', $rooms);
        if (!empty($status))
            $this->db->where_in('ts.active', $status);

        $data = $this->db->get()->result_array();

        return [
            'tenants' => array_values(array_unique(array_column($data, 'tenant_id'))),
            'rooms' => array_values(array_unique(array_column($data, 'room_id'))),
            'status' => array_values(array_unique(array_column($data, 'active')))
        ];
    }

    public function insert($data)
    {
        return $this->db->insert("tenants_stay", $data);
    }

    public function getById($id)
    {
        return $this->db->get_where("tenants_stay", ["id" => $id])->row_array();
    }

    public function updateData($id, $data)
    {
        return $this->db->update("tenants_stay", $data, ["id" => $id]);
    }

    public function deleteData($id)
    {
        return $this->db->delete("tenants_stay", ["id" => $id]);
    }
}
