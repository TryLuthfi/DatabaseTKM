<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHRoomsAssets extends CI_Model
{
    public function getRooms()
    {
        return $this->db->get("rooms")->result_array();
    }

    public function getAssetTypes()
    {
        return $this->db->get("asset_types")->result_array();
    }

    public function getRoomAssets($room_id)
    {
        $this->db->select("ra.*, at.label AS asset_name");
        $this->db->from("room_assets ra");
        $this->db->join("asset_types at", "ra.asset_type_id = at.id", "left");
        $this->db->where("ra.room_id", $room_id);
        return $this->db->get()->result_array();
    }

    public function insert($data)
    {
        return $this->db->insert("room_assets", $data);
    }

    public function getById($id)
    {
        return $this->db->get_where("room_assets", ["id" => $id])->row_array();
    }

    public function updateData($id, $data)
    {
        return $this->db->update("room_assets", $data, ["id" => $id]);
    }

    public function deleteData($id)
    {
        return $this->db->delete("room_assets", ["id" => $id]);
    }
}
