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

		$ciUriString = '';
		$CI =& get_instance();
		if (isset($CI->uri) && method_exists($CI->uri, 'uri_string'))
		{
			$ciUriString = strtolower(trim((string) $CI->uri->uri_string(), '/'));
		}

		// Allow only explicit auth path access during maintenance.
		$isAuthRoute = false;
		if ($ciUriString !== '')
		{
			$isAuthRoute = (strpos($ciUriString, 'auth') === 0);
		}
		elseif (strpos($uriPath, '/auth') !== false)
		{
			$isAuthRoute = true;
		}

		if ($isAuthRoute)
		{
			return;
		}

		$allowedRole = trim((string) $this->readEnvValue('MAINTENANCE_ALLOWED_ROLE', 'Super Admin'));
		$allowedLevelIdRaw = trim((string) $this->readEnvValue('MAINTENANCE_ALLOWED_LEVEL_ID', '1'));
		$allowedLevelId = ctype_digit($allowedLevelIdRaw) ? (int) $allowedLevelIdRaw : 1;
		$currentLevel = '';
		$currentLevelId = 0;
		$currentUserId = 0;
		$currentUsername = '';

		if (isset($CI->session))
		{
			$currentLevel = trim((string) $CI->session->userdata('nama_level'));
			$currentLevelId = (int) $CI->session->userdata('id_level');
			$currentUserId = (int) $CI->session->userdata('id_user');
			$currentUsername = trim((string) $CI->session->userdata('username_user'));
		}

		// Fallback: fetch role/level from DB when session keys are incomplete.
		if (($currentLevelId <= 0 || $currentLevel === '') && isset($CI->db))
		{
			$query = $CI->db
				->select('a.id_level, tl.nama_level')
				->from('tb_master_user_new a')
				->join('tb_level tl', 'a.id_level = tl.id_level', 'left');

			if ($currentUserId > 0)
			{
				$query->where('a.id', $currentUserId);
			}
			elseif ($currentUsername !== '')
			{
				$query->where('a.username_user', $currentUsername);
			}

			$userRow = $query->limit(1)->get()->row_array();

			if (!empty($userRow))
			{
				if ($currentLevelId <= 0)
				{
					$currentLevelId = (int) ($userRow['id_level'] ?? 0);
				}
				if ($currentLevel === '')
				{
					$currentLevel = trim((string) ($userRow['nama_level'] ?? ''));
				}
			}
		}

		log_message(
			'debug',
			'Maintenance guard: uri='.$uriPath
			.' ci_uri='.$ciUriString
			.' id_user='.$currentUserId
			.' username='.$currentUsername
			.' id_level='.$currentLevelId
			.' nama_level='.$currentLevel
			.' allowed_role='.$allowedRole
			.' allowed_level='.$allowedLevelId
		);

		$isAllowedRole = ($allowedRole !== '' && strcasecmp($currentLevel, $allowedRole) === 0);
		$isAllowedLevelId = ($allowedLevelId > 0 && $currentLevelId === $allowedLevelId);
		if ($isAllowedRole || $isAllowedLevelId)
		{
			return;
		}

		if ($this->hasValidBypassCookie($CI, $allowedRole, $allowedLevelId))
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

	private function hasValidBypassCookie($CI, $allowedRole, $allowedLevelId)
	{
		$cookie = isset($_COOKIE['mtk_maintenance_bypass']) ? (string) $_COOKIE['mtk_maintenance_bypass'] : '';
		if ($cookie === '')
		{
			return false;
		}

		$parts = explode('|', $cookie);
		if (count($parts) !== 3)
		{
			return false;
		}

		$userId = ctype_digit($parts[0]) ? (int) $parts[0] : 0;
		$exp = ctype_digit($parts[1]) ? (int) $parts[1] : 0;
		$sig = $parts[2];
		if ($userId <= 0 || $exp <= time() || $sig === '')
		{
			return false;
		}

		$secret = (string) $this->readEnvValue('MAINTENANCE_BYPASS_SECRET', config_item('encryption_key'));
		if ($secret === '')
		{
			$secret = 'database_tkm_maintenance_secret';
		}

		$payload = $userId.'|'.$exp;
		$expectedSig = hash_hmac('sha256', $payload, $secret);
		if (!hash_equals($expectedSig, $sig))
		{
			return false;
		}

		if (!isset($CI->db))
		{
			return false;
		}

		$userRow = $CI->db
			->select('a.id_level, tl.nama_level')
			->from('tb_master_user_new a')
			->join('tb_level tl', 'a.id_level = tl.id_level', 'left')
			->where('a.id', $userId)
			->where('a.status_user', 'ACTIVE')
			->limit(1)
			->get()
			->row_array();
		if (empty($userRow))
		{
			return false;
		}

		$userLevelId = (int) ($userRow['id_level'] ?? 0);
		$userLevelName = trim((string) ($userRow['nama_level'] ?? ''));
		$isAllowedRole = ($allowedRole !== '' && strcasecmp($userLevelName, $allowedRole) === 0);
		$isAllowedLevel = ($allowedLevelId > 0 && $userLevelId === $allowedLevelId);

		return ($isAllowedRole || $isAllowedLevel);
	}
}
