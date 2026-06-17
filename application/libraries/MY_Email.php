<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Email extends CI_Email
{
    protected function _smtp_connect()
    {
        if (is_resource($this->_smtp_connect)) {
            return true;
        }

        $ssl = ($this->smtp_crypto === 'ssl') ? 'ssl://' : '';
        $contextOptions = [];
        if ($this->allowInvalidTlsCertificate()) {
            $contextOptions['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ];
        }

        if (!empty($contextOptions)) {
            $context = stream_context_create($contextOptions);
            $this->_smtp_connect = @stream_socket_client(
                $ssl . $this->smtp_host . ':' . $this->smtp_port,
                $errno,
                $errstr,
                $this->smtp_timeout,
                STREAM_CLIENT_CONNECT,
                $context
            );
        } else {
            $this->_smtp_connect = @fsockopen(
                $ssl . $this->smtp_host,
                $this->smtp_port,
                $errno,
                $errstr,
                $this->smtp_timeout
            );
        }

        if (!is_resource($this->_smtp_connect)) {
            $this->_set_error_message('lang:email_smtp_error', $errno . ' ' . $errstr);
            return false;
        }

        stream_set_timeout($this->_smtp_connect, $this->smtp_timeout);
        $this->_set_error_message($this->_get_smtp_data());

        if ($this->smtp_crypto === 'tls') {
            $this->_send_command('hello');
            $this->_send_command('starttls');

            $method = $this->resolveCryptoMethod();
            $crypto = @stream_socket_enable_crypto($this->_smtp_connect, true, $method);

            if ($crypto !== true) {
                $this->_set_error_message('lang:email_smtp_error', $this->_get_smtp_data());
                return false;
            }
        }

        return $this->_send_command('hello');
    }

    private function allowInvalidTlsCertificate()
    {
        $value = $this->readEnvValue('MYREP_REJECT_EMAIL_ALLOW_INVALID_TLS', 'false');
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    private function resolveCryptoMethod()
    {
        $method = 0;

        foreach ([
            'STREAM_CRYPTO_METHOD_TLS_CLIENT',
            'STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT',
            'STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT',
            'STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT',
            'STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT',
        ] as $constantName) {
            if (defined($constantName)) {
                $method |= constant($constantName);
            }
        }

        return $method > 0 ? $method : STREAM_CRYPTO_METHOD_TLS_CLIENT;
    }

    private function readEnvValue($key, $default = '')
    {
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        $envPath = APPPATH . '../.env';
        if (!is_file($envPath)) {
            return $default;
        }

        $env = @parse_ini_file($envPath);
        if (!is_array($env) || !array_key_exists($key, $env)) {
            return $default;
        }

        return trim((string) $env[$key]);
    }
}
