<?php

namespace App\Controllers\Tf2;

use PDO;

class CidrWhitelistController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'Cidr Whitelist') {
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
			throw new \Exception('Database configuration for Cidr Whitelist panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageCidr() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$cidrs = $this->searchCidrsBySteamId($search, $page, $perPage);
				$totalCidrs = $this->countCidrsBySteamId($search);
			} else {
				$cidrs = $this->fetchCidrs($page, $perPage);
				$totalCidrs = $this->countCidrs();
			}
			$totalPages = ceil($totalCidrs / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/cidr-whitelist', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/cidr-whitelist.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'edit') {
				$cidrs = $this->fetchCidrs($page, $perPage);
				$cidr = $this->fetchCidrById($_POST['cidr_id']);

				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/cidr-whitelist.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'update') {
				$this->updateCidr(
					$_POST['cidr_id'],
					$_POST['cidr'],
					$_POST['kick_message'],
					$_POST['comment']
				);
			} elseif ($_POST['action'] === 'delete') {
				$this->deleteCidr($_POST['cidr_id']);
			} elseif ($_POST['action'] === 'add') {
				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/cidr-whitelist.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'create') {
				$this->addCidr(
					$_POST['cidr'],
					$_POST['kick_message'],
					$_POST['comment']
				);
			}
		}
	}

	private function fetchCidrs($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM cidr_whitelist ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countCidrs() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM cidr_whitelist");
		return $stmt->fetchColumn();
	}

	private function searchCidrsBySteamId($steamId, $page, $perPage) {
		$likeTerm = '%' . $steamId . '%';
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM cidr_whitelist WHERE identity LIKE :steamId ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':steamId', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countCidrsBySteamId($steamId) {
		$likeTerm = '%' . $steamId . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM cidr_whitelist WHERE identity LIKE :steamId");
		$stmt->bindParam(':steamId', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	private function fetchCidrById($id) {
		$stmt = $this->dbConnection->prepare("SELECT * FROM cidr_whitelist WHERE id = :id");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function updateCidr($cidrId, $cidr, $kickMessage, $comment) {
		$stmt = $this->dbConnection->prepare("
			UPDATE cidr_whitelist 
			SET type = :cidr, identity = :kick_message, comment = :comment
			WHERE id = :id
		");
		$stmt->bindParam(':cidr', $cidr, PDO::PARAM_STR);
		$stmt->bindParam(':kick_message', $kickMessage, PDO::PARAM_STR);
		$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
		$stmt->bindParam(':id', $cidrId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/cidr-whitelist.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/cidr-whitelist');
		exit;
	}

	private function addCidr($cidr, $kickMessage, $comment) {
		$stmt = $this->dbConnection->prepare("
			INSERT INTO cidr_whitelist (type, identity, comment)
			VALUES (:cidr, :kick_message, :comment)
		");
		$stmt->bindParam(':cidr', $cidr, PDO::PARAM_STR);
		$stmt->bindParam(':kick_message', $kickMessage, PDO::PARAM_STR);
		$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
		$stmt->execute();

		file_put_contents("tf2/cidr-whitelist.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/cidr-whitelist');
		exit;
	}

	private function deleteCidr($cidrId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM cidr_whitelist WHERE id = :id");
		$stmt->bindParam(':id', $cidrId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/cidr-whitelist.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/cidr-whitelist');
		exit;
	}
}
