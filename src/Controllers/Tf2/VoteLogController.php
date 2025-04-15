<?php

namespace App\Controllers\Tf2;

use PDO;

class VoteLogController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'Vote Logs') {
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
			throw new \Exception('Database configuration for Vote Logs panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageVotes() {
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$perPage = 50;
			$search = isset($_GET['search']) ? $_GET['search'] : '';

			if ($search) {
				$logs = $this->searchLogsBySteamId($search, $page, $perPage);
				$totalLogs = $this->countLogsBySteamId($search);
			} else {
				$logs = $this->fetchLogs($page, $perPage);
				$totalLogs = $this->countLogs();
			}

			$totalPages = ceil($totalLogs / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/vote-logs', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/vote-logs.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'delete') {
				$this->deleteLog($_POST['log_id']);
			}
			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/vote-logs.php';
			include __DIR__ . '/../../Views/footer.php';
		}
	}

	private function fetchLogs($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM gamevote_log ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countLogs() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM gamevote_log");
		return $stmt->fetchColumn();
	}

	private function deleteLog($logId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM gamevote_log WHERE id = :id");
		$stmt->bindParam(':id', $logId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/vote-logs.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/vote-logs');
		exit;
	}

	private function searchLogsBySteamId($steamId, $page, $perPage) {
		$likeTerm = '%' . $steamId . '%';
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM gamevote_log WHERE steamid LIKE :steam_id ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':steam_id', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countLogsBySteamId($steamId) {
		$likeTerm = '%' . $steamId . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM gamevote_log WHERE steamid LIKE :steam_id");
		$stmt->bindParam(':steam_id', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}
}
