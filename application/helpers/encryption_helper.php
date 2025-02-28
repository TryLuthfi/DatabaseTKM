<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('encrypt_id')) {
    function encrypt_id($id) {
        return base64_encode($id);
    }
}

if (!function_exists('decrypt_id')) {
    function decrypt_id($hash) {
        return base64_decode($hash);
    }
}
?>