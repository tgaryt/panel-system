<?php

namespace App\Controllers\Tf2;

use PDO;

class ViewPrivilegeController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'Manage Privileges') {
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
			throw new \Exception('Database configuration for View Privileges panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function viewPrivileges() {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if ($search) {
				$privileges = $this->searchPrivilegesBySteamId($search, $page, $perPage);
				$totalPrivileges = $this->countPrivilegesBySteamId($search);
			} else {
				$privileges = $this->fetchPrivileges($page, $perPage);
				$totalPrivileges = $this->countPrivileges();
			}
			$totalPages = ceil($totalPrivileges / $perPage);

			require_once __DIR__ . '/../../Views/pagination.php';
			$paginationHTML = generatePagination($page, $totalPages, '/tf2/view-privileges', $search);

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/view-privileges.php';
			include __DIR__ . '/../../Views/footer.php';
		} elseif ($_POST['action'] === 'delete') {
				$this->deletePrivilege((int)$_POST['privilege_id']);
			}
	}

	private function fetchPrivileges($page, $perPage) {
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM sm_admins ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function countPrivileges() {
		$stmt = $this->dbConnection->query("SELECT COUNT(*) FROM sm_admins");
		return $stmt->fetchColumn();
	}

	private function searchPrivilegesBySteamId($steamId, $page, $perPage) {
		$likeTerm = '%' . $steamId . '%';
		$offset = ($page - 1) * $perPage;
		$stmt = $this->dbConnection->prepare("SELECT * FROM sm_admins WHERE identity LIKE :identity OR name LIKE :identity OR flags LIKE :identity ORDER BY id DESC LIMIT :limit OFFSET :offset");
		$stmt->bindParam(':identity', $likeTerm, PDO::PARAM_STR);
		$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function deletePrivilege($privilegeId) {
		$stmt = $this->dbConnection->prepare("DELETE FROM sm_admins WHERE id = :id");
		$stmt->bindParam(':id', $privilegeId, PDO::PARAM_INT);
		$stmt->execute();

		file_put_contents("tf2/view-privileges.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);

		header('Location: /tf2/view-privileges');
		exit;
	}

	private function countPrivilegesBySteamId($steamId) {
		$likeTerm = '%' . $steamId . '%';
		$stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM sm_admins WHERE identity LIKE :identity OR name LIKE :identity OR flags LIKE :identity");
		$stmt->bindParam(':identity', $likeTerm, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	private function fetchPrivilegeById($id) {
		$stmt = $this->dbConnection->prepare("SELECT * FROM sm_admins WHERE id = :id");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	private function renderPrivilegePage($error = '') {
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = 50;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		if ($search) {
			$privileges = $this->searchPrivilegesBySteamId($search, $page, $perPage);
			$totalPrivileges = $this->countPrivilegesBySteamId($search);
		} else {
			$privileges = $this->fetchPrivileges($page, $perPage);
			$totalPrivileges = $this->countPrivileges();
		}
		$totalPages = ceil($totalPrivileges / $perPage);

		require_once __DIR__ . '/../../Views/pagination.php';
		$paginationHTML = generatePagination($page, $totalPages, '/tf2/view-privileges', $search);

		$config = require __DIR__ . '/../../config.php';
		include __DIR__ . '/../../Views/header.php';
		include __DIR__ . '/../../Views/tf2/view-privileges.php';
		include __DIR__ . '/../../Views/footer.php';
	}
}
