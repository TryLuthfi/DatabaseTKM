<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHDashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MGHDashboard');
    }

    public function index()
    {
        $data['title'] = "Dashboard";

        // Card data
        $data['total_rooms'] = $this->MGHDashboard->totalRooms();
        $data['rooms_occupied'] = $this->MGHDashboard->roomsOccupied();
        $data['rooms_empty'] = $data['total_rooms'] - $data['rooms_occupied'];
        $data['total_active_tenants'] = $this->MGHDashboard->totalActiveTenants();

        // Grafik data
        $data['grafik_tenant'] = $this->MGHDashboard->tenantInPerMonth();

        $data['total_rooms'] = $this->MGHDashboard->totalRooms();
        $data['rooms_occupied'] = $this->MGHDashboard->roomsOccupied();
        $data['rooms_empty'] = $data['total_rooms'] - $data['rooms_occupied'];
        $data['total_active_tenants'] = $this->MGHDashboard->totalActiveTenants();

        $this->load->view("Templates/01_Header", $data);
        $this->load->view("Templates/02_Menu");
        $this->load->view("GHDashboard/index", $data);
        $this->load->view("Templates/03_Footer");
        $this->load->view("Templates/99_JS");
    }
}
