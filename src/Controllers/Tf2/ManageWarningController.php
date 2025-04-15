<?php

namespace App\Controllers\Tf2;

use PDO;

class ManageWarningController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'Add Privileges') {
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
			throw new \Exception('Database configuration for Manage Warnings panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageWarnings() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$warnings = $this->searchWarningsBySteamId($search, $page, $perPage);
				$totalWarnings = $this->countWarningsBySteamId($search);
			} else {
				$warnings = $this->fetchWarnings($page, $perPage);
				$totalWarnings = $this->countWarnings();
			}
			$totalPages = ceil($totalWarnings / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/manage-warnings', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/manage-warnings.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'edit') {
				$warnings = $this->fetchWarnings($page, $perPage);
				$warning = $this->fetchWarningById($_POST['warning_id']);

				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/manage-warnings.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'update') {
				$this->updateWarning(
					$_POST['warning_id'],
					$_POST['player_nick'],
					$_POST['player_steam_id'],
					$_POST['admin_nick'],
					$_POST['reason'],
					$_POST['expired']
				);
			} elseif ($_POST['action'] === 'delete') {
				$this->deleteWarning($_POST['warning_id']);
			} elseif ($_POST['action'] === 'add') {
				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/manage-warnings.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'create') {
				$this->addWarning(
					$_POST['player_nick'],
					$_POST['player_steam_id'],
					$_POST['admin_nick'],
					$_POST['admin_steam_id'],
					$_POST['reason']
				);
			}
		}
	}

	private function fetchWarnings($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM smwarn ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countWarnings() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM smwarn");
		return $stmt->fetchColumn();
	}

	private function searchWarningsBySteamId($steamId, $page, $perPage) {
		$likeTerm = '%' . $steamId . '%';
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM smwarn WHERE tsteamid LIKE :tsteamid ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':tsteamid', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countWarningsBySteamId($steamId) {
		$likeTerm = '%' . $steamId . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM smwarn WHERE tsteamid LIKE :tsteamid");
		$stmt->bindParam(':tsteamid', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	private function fetchWarningById($id) {
		$stmt = $this->dbConnection->prepare("SELECT * FROM smwarn WHERE id = :id");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function updateWarning($warningId, $playerNick, $playerSteamId, $adminNick, $reason, $expired) {
		$stmt = $this->dbConnection->prepare("
			UPDATE smwarn 
			SET target = :target, tsteamid = :tsteamid, admin = :admin, reason = :reason, expired = :expired 
			WHERE id = :id
		");
		$stmt->bindParam(':target', $playerNick, PDO::PARAM_STR);
		$stmt->bindParam(':tsteamid', $playerSteamId, PDO::PARAM_STR);
		$stmt->bindParam(':admin', $adminNick, PDO::PARAM_STR);
		$stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
		$stmt->bindParam(':expired', $expired, PDO::PARAM_INT);
		$stmt->bindParam(':id', $warningId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/manage-warnings.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/manage-warnings');
		exit;
	}

	private function addWarning($playerNick, $playerSteamId, $adminNick, $adminSteamId, $reason) {
		$time = time();
		$hostname = 'Website';
		$expired = 0;

		$stmt = $this->dbConnection->prepare("
			INSERT INTO smwarn (target, tsteamid, admin, asteamid, reason, time, hostname, expired)
			VALUES (:target, :tsteamid, :admin, :asteamid, :reason, :time, :hostname, :expired)
		");
		$stmt->bindParam(':target', $playerNick, PDO::PARAM_STR);
		$stmt->bindParam(':tsteamid', $playerSteamId, PDO::PARAM_STR);
		$stmt->bindParam(':admin', $adminNick, PDO::PARAM_STR);
		$stmt->bindParam(':asteamid', $adminSteamId, PDO::PARAM_STR);
		$stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
		$stmt->bindParam(':time', $time, PDO::PARAM_INT);
		$stmt->bindParam(':hostname', $hostname, PDO::PARAM_STR);
		$stmt->bindParam(':expired', $expired, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/manage-warnings.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/manage-warnings');
		exit;
	}

	private function deleteWarning($warningId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM smwarn WHERE id = :id");
		$stmt->bindParam(':id', $warningId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/manage-warnings.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/manage-warnings');
		exit;
	}
}
