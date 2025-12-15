<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHRooms extends CI_Model
{

    private $table = 'rooms';

    public function getAllRooms()
    {
        $data = $this->db->query('SELECT
	rooms.*,
	room_types.price_default,
	room_types.name AS type_name,
	tenants_stay.tenant_id,
	tenants.fullname
FROM
	rooms
join room_types ON
	room_types.id = rooms.room_type_id
	LEFT JOIN tenants_stay ON rooms.id = tenants_stay.room_id 
	LEFT JOIN tenants ON tenants_stay.tenant_id = tenants.id
ORDER BY
	rooms.id ASC')->result_array();
        return $data;
    }

    public function getTypesSummary()
    {
        $this->db->select("rt.name AS type_name, COUNT(r.id) as total");
        $this->db->from("rooms r");
        $this->db->join("room_types rt", "rt.id = r.room_type_id", "left");
        $this->db->group_by("rt.id");
        $this->db->order_by("rt.name", "ASC");

        $data = $this->db->get()->result_array();

        log_message('error', 'query ghrooms : ' . $this->db->last_query());

        return $data;
    }

    public function get_types()
    {
        return $this->db->order_by('name', 'ASC')->get('room_types')->result_array();
    }


    public function tambahKamar($data_array)
    {
        $res = $this->db->insert("rooms", $data_array);
        return $res;
    }

    public function editKamar($data_array, $id)
    {
        $res = $this->db->update("rooms", $data_array, $id);
        return $res;
    }

    public function hapusKamar($id)
    {
        $res = $this->db->delete("rooms", $id);
        return $res;
    }

}
