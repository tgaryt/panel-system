<?php

namespace App\Controllers\Tf2;

use PDO;

class RemovePrivilegeController {
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
			throw new \Exception('Database configuration for Remove Privileges panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function removePrivileges() {
		$config = require __DIR__ . '/../../config.php';
		include __DIR__ . '/../../Views/header.php';
		include __DIR__ . '/../../Views/tf2/remove-privileges.php';
		include __DIR__ . '/../../Views/footer.php';
	}

	public function processForm() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$steamid = $_POST['steamid'];

			$error = '';
			$success = '';

			if (!preg_match('/^STEAM_[0-5]:[01]:\d+$/', $steamid)) {
				$error = "<p class='error' style='text-align: center;'>Invalid SteamID format. Please enter a valid SteamID.</p>";
			} else {
				$stmt = $this->dbConnection->prepare("DELETE FROM sm_admins WHERE identity = :identity");
				$stmt->bindParam(':identity', $steamid);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$success = "<p class='success' style='text-align: center;'>Privileges removed for SteamID: $steamid</p>";
					file_put_contents("tf2/remove-privileges.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);
				} else {
					$error = "<p class='error' style='text-align: center;'>No privileges found with SteamID: $steamid</p>";
				}
			}

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/remove-privileges.php';
			include __DIR__ . '/../../Views/footer.php';
		}
	}
}
