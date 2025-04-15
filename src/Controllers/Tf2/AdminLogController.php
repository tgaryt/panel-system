<?php

namespace App\Controllers\Tf2;

use PDO;

class AdminLogController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'Admin Logs') {
					$dbConfig = $panel['db'];
					break 2;
				}
			}
		}

		if ($dbConfig) {
			$this->dbConnection = new PDO(
				"mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}",
				$dbConfig['user'],
				$dbConfig['password']
			);
		} else {
			throw new \Exception('Database configuration for Admin Logs panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageLogs() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$logs = $this->searchLogsBySteamID($search, $page, $perPage);
				$totalLogs = $this->countSearchLogsBySteamID($search);
			} else {
				$logs = $this->fetchLogs($page, $perPage);
				$totalLogs = $this->countLogs();
			}
			$totalPages = ceil($totalLogs / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/admin-logs', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/admin-logs.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'delete') {
				$this->deleteLog($_POST['log_id']);
			}
			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/admin-logs.php';
			include __DIR__ . '/../../Views/footer.php';
		}
	}

	private function fetchLogs($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM adminlogs ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countLogs() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM adminlogs");
		return $stmt->fetchColumn();
	}

	private function searchLogsBySteamID($steamID, $page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$likeTerm = '%' . $steamID . '%';
		$stmt = $this->dbConnection->prepare("SELECT * FROM adminlogs WHERE name LIKE :steamId OR steam2 LIKE :steamId OR log LIKE :steamId ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':steamId', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countSearchLogsBySteamID($steamID) {
		$likeTerm = '%' . $steamID . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM adminlogs WHERE name LIKE :steamId OR steam2 LIKE :steamId OR log LIKE :steamId");
		$stmt->bindParam(':steamId', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	private function deleteLog($logId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM adminlogs WHERE id = :id");
		$stmt->bindParam(':id', $logId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/admin-logs.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/admin-logs');
		exit;
	}
}
