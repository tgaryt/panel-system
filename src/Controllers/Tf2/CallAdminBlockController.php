<?php

namespace App\Controllers\Tf2;

use PDO;

class CallAdminBlockController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'CallAdmin Block') {
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
			throw new \Exception('Database configuration for CallAdmin Block panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageCalls() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$calls = $this->searchCallsBySteamId($search, $page, $perPage);
				$totalCalls = $this->countCallsBySteamId($search);
			} else {
				$calls = $this->fetchCalls($page, $perPage);
				$totalCalls = $this->countCalls();
			}

			$totalPages = ceil($totalCalls / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/calladmin-block', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/calladmin-block.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'edit') {
				$calls = $this->fetchCalls($page, $perPage);
				$call = $this->fetchCallById($_POST['call_id']);

				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/calladmin-block.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'update') {
				$this->updateCall(
					$_POST['call_id'],
					$_POST['player_steam_id'],
					$_POST['time_end'],
					$_POST['alias']
				);
			} elseif ($_POST['action'] === 'delete') {
				$this->deleteCall($_POST['call_id']);
			} elseif ($_POST['action'] === 'add') {
				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/calladmin-block.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'create') {
				$this->addCall(
					$_POST['player_steam_id'],
					$_POST['time_end'],
					$_POST['alias']
				);
			}
		}
	}

	private function fetchCalls($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM calladmin_block ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countCalls() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM calladmin_block");
		return $stmt->fetchColumn();
	}

	private function fetchCallById($id) {
		$stmt = $this->dbConnection->prepare("SELECT * FROM calladmin_block WHERE id = :id");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$call = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($call) {
			$currentTime = time();
			$timeEndUnix = $call['time_end'];
			$timeRemaining = max(0, ($timeEndUnix - $currentTime) / 60);
			$call['time_end'] = ceil($timeRemaining);
		}

		return $call;
	}

	private function updateCall($callId, $steamId, $timeEnd, $alias) {
		$stmt = $this->dbConnection->prepare("SELECT time_start FROM calladmin_block WHERE id = :id");
		$stmt->bindParam(':id', $callId, PDO::PARAM_INT);
		$stmt->execute();
		$existingCall = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($existingCall) {
			$timeStart = $existingCall['time_start'];
			$currentTime = time();
			$timeEndUnix = $currentTime + ($timeEnd * 60);

			$stmt = $this->dbConnection->prepare("
				UPDATE calladmin_block 
				SET steam_id = :steamid, time_end = :time_end, alias = :alias
				WHERE id = :id
			");
			$stmt->bindParam(':steamid', $steamId, PDO::PARAM_STR);
			$stmt->bindParam(':time_end', $timeEndUnix, PDO::PARAM_STR);
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->bindParam(':id', $callId, PDO::PARAM_INT);
			$stmt->execute();

			file_put_contents("tf2/calladmin-block.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

			header('Location: /tf2/calladmin-block');
			exit;
		} else {
			throw new \Exception('Call not found.');
		}
	}

	private function addCall($steamId, $timeEnd, $alias) {
		$timeStart = time();
		$timeEndUnix = $timeStart + ($timeEnd * 60);

		$existingStmt = $this->dbConnection->prepare("
			SELECT * FROM calladmin_block WHERE steam_id = :steamid
		");
		$existingStmt->bindParam(':steamid', $steamId, PDO::PARAM_STR);
		$existingStmt->execute();
		$existingEntry = $existingStmt->fetch(PDO::FETCH_ASSOC);

		if ($existingEntry) {
			$deleteStmt = $this->dbConnection->prepare("
				DELETE FROM calladmin_block WHERE steam_id = :steamid
			");
			$deleteStmt->bindParam(':steamid', $steamId, PDO::PARAM_STR);
			$deleteStmt->execute();
		}

		$insertStmt = $this->dbConnection->prepare("
			INSERT INTO calladmin_block (steam_id, time_start, time_end, alias)
			VALUES (:steamid, :time_start, :time_end, :alias)
		");
		$insertStmt->bindParam(':steamid', $steamId, PDO::PARAM_STR);
		$insertStmt->bindParam(':time_start', $timeStart, PDO::PARAM_STR);
		$insertStmt->bindParam(':time_end', $timeEndUnix, PDO::PARAM_STR);
		$insertStmt->bindParam(':alias', $alias, PDO::PARAM_STR);
		$insertStmt->execute();

		file_put_contents("tf2/calladmin-block.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/calladmin-block');
		exit;
	}

	private function deleteCall($callId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM calladmin_block WHERE id = :id");
		$stmt->bindParam(':id', $callId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/calladmin-block.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/calladmin-block');
		exit;
	}

	private function searchCallsBySteamId($steamId, $page, $perPage) {
		$likeTerm = '%' . $steamId . '%';
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM calladmin_block WHERE steam_id LIKE :steam_id ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':steam_id', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countCallsBySteamId($steamId) {
		$likeTerm = '%' . $steamId . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM calladmin_block WHERE steam_id LIKE :steam_id");
		$stmt->bindParam(':steam_id', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}
}
