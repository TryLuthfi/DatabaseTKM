<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenance_hook
{
	public function enforce()
	{
		if (PHP_SAPI === 'cli')
		{
			return;
		}

		$maintenanceRaw = strtolower(trim((string) $this->readEnvValue('MAINTENANCE_MODE', 'false')));
		$isMaintenanceOn = in_array($maintenanceRaw, array('1', 'true', 'on', 'yes'), true);
		if (!$isMaintenanceOn)
		{
			return;
		}

		$uriPath = '/';
		if (!empty($_SERVER['REQUEST_URI']))
		{
			$parsedPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			if (is_string($parsedPath) && $parsedPath !== '')
			{
				$uriPath = strtolower($parsedPath);
			}
		}

		$isAuthRoute = (strpos($uriPath, '/auth') !== false);
		if ($isAuthRoute)
		{
			return;
		}

		$allowedRole = trim((string) $this->readEnvValue('MAINTENANCE_ALLOWED_ROLE', 'Super Admin'));
		$currentLevel = '';

		$CI =& get_instance();
		if (isset($CI->session))
		{
			$currentLevel = trim((string) $CI->session->userdata('nama_level'));
		}

		$isAllowedRole = ($allowedRole !== '' && strcasecmp($currentLevel, $allowedRole) === 0);
		if ($isAllowedRole)
		{
			return;
		}

		header('HTTP/1.1 503 Service Unavailable', true, 503);
		header('Retry-After: 3600');
		header('Content-Type: text/html; charset=UTF-8');
		$maintenanceView = APPPATH.'views/errors/html/error_403.php';
		if (is_file($maintenanceView))
		{
			require $maintenanceView;
		}
		else
		{
			echo 'Maintenance';
		}
		exit;
	}

	private function readEnvValue($key, $default = '')
	{
		if ($key === '')
		{
			return $default;
		}

		if (isset($_ENV[$key]))
		{
			$envVal = $_ENV[$key];
			if (is_scalar($envVal))
			{
				return (string) $envVal;
			}
		}

		if (isset($_SERVER[$key]))
		{
			$serverVal = $_SERVER[$key];
			if (is_scalar($serverVal))
			{
				return (string) $serverVal;
			}
		}

		$envGet = getenv($key);
		if ($envGet !== false && is_scalar($envGet))
		{
			return (string) $envGet;
		}

		$envFile = FCPATH.'.env';
		if (!is_file($envFile))
		{
			return $default;
		}

		$lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === false)
		{
			return $default;
		}

		foreach ($lines as $line)
		{
			$line = trim($line);
			if ($line === '' || $line[0] === '#')
			{
				continue;
			}

			$parts = explode('=', $line, 2);
			if (count($parts) !== 2)
			{
				continue;
			}

			$envKey = trim($parts[0]);
			if ($envKey !== $key)
			{
				continue;
			}

			$envVal = trim($parts[1]);
			$first = substr($envVal, 0, 1);
			$last = substr($envVal, -1);
			if (($first === '"' && $last === '"') || ($first === "'" && $last === "'"))
			{
				$envVal = substr($envVal, 1, -1);
			}

			return $envVal;
		}

		return $default;
	}
}
