<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MaintenancePreview extends CI_Controller
{
    public function index()
    {
        $this->output->set_status_header(503);
        $this->load->view('errors/html/error_403');
    }
}

