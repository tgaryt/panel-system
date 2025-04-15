<?php

namespace App\Controllers\Tf2;

use PDO;

class AsnCidrLogController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'ASN/Cidr Logs') {
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
			throw new \Exception('Database configuration for ASN/Cidr Logs panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function manageAsnCidr() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$asnscidrs = $this->searchAsnsCidrsBySteamId($search, $page, $perPage);
				$totalAsnCidrs = $this->countAsnsCidrsBySteamId($search);
			} else {
				$asnscidrs = $this->fetchAsnsCidrs($page, $perPage);
				$totalAsnCidrs = $this->countAsnsCidrs();
			}
			$totalPages = ceil($totalAsnCidrs / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/asn-cidr-logs', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/asn-cidr-logs.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($_POST['action'] === 'delete') {
				$this->deleteAsnCidr($_POST['asncidr_id']);
			}
			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/asn-cidr-logs.php';
			include __DIR__ . '/../../Views/footer.php';
		}
	}

	private function fetchAsnsCidrs($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM asn_cidr_log ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countAsnsCidrs() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM asn_cidr_log");
		return $stmt->fetchColumn();
	}

	private function searchAsnsCidrsBySteamId($steamId, $page, $perPage) {
		$likeTerm = '%' . $steamId . '%';
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM asn_cidr_log WHERE steamid LIKE :steamId OR cidrasn LIKE :steamId ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':steamId', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countAsnsCidrsBySteamId($steamId) {
		$likeTerm = '%' . $steamId . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM asn_cidr_log WHERE steamid LIKE :steamId OR cidrasn LIKE :steamId");
		$stmt->bindParam(':steamId', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	private function deleteAsnCidr($asncidrId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM asn_cidr_log WHERE id = :id");
		$stmt->bindParam(':id', $asncidrId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/asn-cidr-logs.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/asn-cidr-logs');
		exit;
	}
}
