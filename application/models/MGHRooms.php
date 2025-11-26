<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHRooms extends CI_Model
{

    private $table = 'rooms';

    public function get_all()
    {
        $this->db->select('rooms.*, room_types.name AS type_name');
        $this->db->from($this->table);
        $this->db->join('room_types', 'room_types.id = rooms.room_type_id', 'left');
        $this->db->order_by('rooms.code', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_types()
    {
        return $this->db->order_by('name','ASC')->get('room_types')->result_array();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row_array();
    }
}
