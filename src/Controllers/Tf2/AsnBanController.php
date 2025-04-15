<?php

namespace App\Controllers\Tf2;

use PDO;

class AsnBanController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'ASN Bans') {
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
			throw new \Exception('Database configuration for ASN Bans panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageAsn() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$asns = $this->searchAsnsByAsn($search, $page, $perPage);
				$totalAsns = $this->countAsnsByAsn($search);
			} else {
				$asns = $this->fetchAsns($page, $perPage);
				$totalAsns = $this->countAsns();
			}

			$totalPages = ceil($totalAsns / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/asn-bans', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/asn-bans.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'edit') {
				$asns = $this->fetchAsns($page, $perPage);
				$asn = $this->fetchAsnById($_POST['asn_id']);

				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/asn-bans.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'update') {
				$this->updateAsn(
					$_POST['asn_id'],
					$_POST['asn'],
					$_POST['kick_message'],
					$_POST['comment']
				);
			} elseif ($_POST['action'] === 'delete') {
				$this->deleteAsn($_POST['asn_id']);
			} elseif ($_POST['action'] === 'add') {
				$config = require __DIR__ . '/../../config.php';
				include __DIR__ . '/../../Views/header.php';
				include __DIR__ . '/../../Views/tf2/asn-bans.php';
				include __DIR__ . '/../../Views/footer.php';
			} elseif ($_POST['action'] === 'create') {
				$this->addAsn(
					$_POST['asn'],
					$_POST['kick_message'],
					$_POST['comment']
				);
			}
		}
	}

	private function fetchAsns($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM asn_list ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countAsns() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM asn_list");
		return $stmt->fetchColumn();
	}

	private function fetchAsnById($id) {
		$stmt = $this->dbConnection->prepare("SELECT * FROM asn_list WHERE id = :id");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function updateAsn($asnId, $asn, $kickMessage, $comment) {
		$stmt = $this->dbConnection->prepare("
			UPDATE asn_list 
			SET asn = :asn, kick_message = :kick_message, comment = :comment
			WHERE id = :id
		");
		$stmt->bindParam(':asn', $asn, PDO::PARAM_STR);
		$stmt->bindParam(':kick_message', $kickMessage, PDO::PARAM_STR);
		$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
		$stmt->bindParam(':id', $asnId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/asn-bans.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/asn-bans');
		exit;
	}

	private function addAsn($asn, $kickMessage, $comment) {
		$error = '';

		$checkStmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM asn_list WHERE asn = :asn");
		$checkStmt->bindParam(':asn', $asn, PDO::PARAM_STR);
		$checkStmt->execute();
		$asnExists = $checkStmt->fetchColumn();

		if ($asnExists) {
			$error = "<p class='error' style='text-align: center;'>ASN already exists in the list.</p>";
			$this->renderAsnBanPage($error);
		} else {
			$stmt = $this->dbConnection->prepare("
				INSERT INTO asn_list (asn, kick_message, comment)
				VALUES (:asn, :kick_message, :comment)
			");
			$stmt->bindParam(':asn', $asn, PDO::PARAM_STR);
			$stmt->bindParam(':kick_message', $kickMessage, PDO::PARAM_STR);
			$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
			$stmt->execute();

			file_put_contents("tf2/asn-bans.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

			header('Location: /tf2/asn-bans');
			exit;
		}
	}

	private function deleteAsn($asnId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM asn_list WHERE id = :id");
		$stmt->bindParam(':id', $asnId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/asn-bans.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/asn-bans');
		exit;
	}

	private function renderAsnBanPage($error = '') {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$asns = $this->fetchAsns($page, $perPage);
		$totalAsns = $this->countAsns();
		$totalPages = ceil($totalAsns / $perPage);

		require_once __DIR__ . '/../../Views/pagination.php';
		$paginationHTML = generatePagination($page, $totalPages, '/tf2/asn-bans');

		$config = require __DIR__ . '/../../config.php';
		include __DIR__ . '/../../Views/header.php';
		include __DIR__ . '/../../Views/tf2/asn-bans.php';
		include __DIR__ . '/../../Views/footer.php';
	}

	private function searchAsnsByAsn($asn, $page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$likeTerm = '%' . $asn . '%';
		$stmt = $this->dbConnection->prepare("SELECT * FROM asn_list WHERE asn LIKE :asn ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':asn', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countAsnsByAsn($asn) {
		$likeTerm = '%' . $asn . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM asn_list WHERE asn LIKE :asn");
		$stmt->bindParam(':asn', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}
}
