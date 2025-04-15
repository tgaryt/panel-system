<?php

namespace App\Controllers\Tf2;

use PDO;

class SuperSprayController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'Super Spray Handler') {
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
			throw new \Exception('Database configuration for Super Sprays panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageSprays() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$sprays = $this->searchSpraysBySteamId($search, $page, $perPage);
				$totalSprays = $this->countSpraysBySteamId($search);
			} else {
				$sprays = $this->fetchSprays($page, $perPage);
				$totalSprays = $this->countSprays();
			}
			$totalPages = ceil($totalSprays / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/super-spray-handler', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/super-spray-handler.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'edit') {
				$sprays = $this->fetchSprays($page, $perPage);
				$spray = $this->fetchSprayById($_POST['spray_id']);

				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/super-spray-handler.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'update') {
				$this->updateSpray(
					$_POST['spray_id'],
					$_POST['player_nick'],
					$_POST['player_steam_id']
				);
			} elseif ($_POST['action'] === 'delete') {
				$this->deleteSpray($_POST['spray_id']);
			} elseif ($_POST['action'] === 'add') {
				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/super-spray-handler.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'create') {
				$this->addSpray(
					$_POST['player_nick'],
					$_POST['player_steam_id']
				);
			}
		}
	}

	private function fetchSprays($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM ssh ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countSprays() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM ssh");
		return $stmt->fetchColumn();
	}

	private function searchSpraysBySteamId($steamId, $page, $perPage) {
		$likeTerm = '%' . $steamId . '%';
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM ssh WHERE auth LIKE :auth ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':auth', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countSpraysBySteamId($steamId) {
		$likeTerm = '%' . $steamId . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM ssh WHERE auth LIKE :auth");
		$stmt->bindParam(':auth', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	private function fetchSprayById($id) {
		$stmt = $this->dbConnection->prepare("SELECT * FROM ssh WHERE id = :id");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function updateSpray($sprayId, $playerNick, $playerSteamId) {
		$stmt = $this->dbConnection->prepare("
			UPDATE ssh 
			SET name = :name, auth = :auth
			WHERE id = :id
		");
		$stmt->bindParam(':name', $playerNick, PDO::PARAM_STR);
		$stmt->bindParam(':auth', $playerSteamId, PDO::PARAM_STR);
		$stmt->bindParam(':id', $sprayId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/super-spray-handler.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/super-spray-handler');
		exit;
	}

	private function addSpray($playerNick, $playerSteamId) {
		$stmt = $this->dbConnection->prepare("
			INSERT INTO ssh (name, auth)
			VALUES (:name, :auth)
		");
		$stmt->bindParam(':name', $playerNick, PDO::PARAM_STR);
		$stmt->bindParam(':auth', $playerSteamId, PDO::PARAM_STR);
		$stmt->execute();

		file_put_contents("tf2/super-spray-handler.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/super-spray-handler');
		exit;
	}

	private function deleteSpray($sprayId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM ssh WHERE id = :id");
		$stmt->bindParam(':id', $sprayId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/super-spray-handler.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/super-spray-handler');
		exit;
	}
}
