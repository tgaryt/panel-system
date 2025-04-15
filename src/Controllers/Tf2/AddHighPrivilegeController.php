<?php

namespace App\Controllers\Tf2;

use PDO;

class AddHighPrivilegeController {
	private $dbConnection;

	public function __construct() {
		$config = require __DIR__ . '/../../config.php';
		$dbConfig = null;
		foreach ($config['games'] as $game) {
			foreach ($game['panels'] as $panel) {
				if ($panel['name'] === 'Add High Privileges') {
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
			throw new \Exception('Database configuration for Add High Privileges panel not found.');
		}
	}

	public function getAccessInfo() {
		$folderPath = 'tf2';
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}

		return $_SERVER['PHP_AUTH_USER']." - ".date('Y-m-d H:i:s')." - ".$_SERVER['REMOTE_ADDR'];
	}

	public function addHighPrivileges() {
		$config = require __DIR__ . '/../../config.php';
		include __DIR__ . '/../../Views/header.php';
		include __DIR__ . '/../../Views/tf2/add-high-privileges.php';
		include __DIR__ . '/../../Views/footer.php';
	}

	public function processForm() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$steamid = $_POST['steamid'];
			$name = $_POST['name'];
			$privileges = $_POST['privileges'];
			$immunity = $_POST['immunity'];
			$days = $_POST['days'];
			$discord = $_POST['discord'] ? $_POST['discord'] : null;
			$comment = $_POST['comment'] ? $_POST['comment'] : null;

			$error = '';
			$success = '';

			$allowedPrivileges = ['abcdefgjkz'];

			if (!preg_match('/^STEAM_[0-5]:[01]:\d+$/', $steamid)) {
				$error = "<p class='error' style='text-align: center;'>Invalid SteamID format. Please enter a valid SteamID.</p>";
			} elseif (empty($name)) {
				$error = "<p class='error' style='text-align: center;'>Name cannot be empty.</p>";
			} elseif (!in_array($privileges, $allowedPrivileges)) {
				$error = "<p class='error' style='text-align: center;'>Invalid privilege selected.</p>";
			} else {
				$stmt = $this->dbConnection->prepare("SELECT * FROM sm_admins WHERE identity = :identity");
				$stmt->bindParam(':identity', $steamid);
				$stmt->execute();
				$existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($existingAdmin) {
					if ($days === 'lifetime') {
						$newExpireTime = strtotime('2037-12-31');
					} else {
						$newExpireTime = $existingAdmin['expire_time'] + ($days * 24 * 60 * 60);
					}

					$stmt = $this->dbConnection->prepare("UPDATE sm_admins SET expire_time = :expire_time, flags = :flags, name = :name, comment = :comment, discord = :discord, immunity = :immunity WHERE identity = :identity");

					$stmt->bindParam(':expire_time', $newExpireTime);
					$stmt->bindParam(':flags', $privileges);
					$stmt->bindParam(':name', $name);
					$stmt->bindParam(':comment', $comment);
					$stmt->bindParam(':discord', $discord);
					$stmt->bindParam(':immunity', $immunity);
					$stmt->bindParam(':identity', $steamid);
					$stmt->execute();
				} else {
					if ($days === 'lifetime') {
						$newExpireTime = strtotime('2037-12-31');
					} else {
						$currentTimestamp = time();
						$newExpireTime = $currentTimestamp + ($days * 24 * 60 * 60);
					}

					$stmt = $this->dbConnection->prepare("INSERT INTO sm_admins (authtype, identity, flags, name, expire_time, discord, comment, immunity) VALUES (:authtype, :identity, :flags, :name, :expire_time, :discord, :comment, :immunity)");

					$stmt->bindValue(':authtype', 'steam');
					$stmt->bindParam(':identity', $steamid);
					$stmt->bindParam(':flags', $privileges);
					$stmt->bindParam(':immunity', $immunity);
					$stmt->bindParam(':name', $name);
					$stmt->bindParam(':expire_time', $newExpireTime);
					$stmt->bindParam(':discord', $discord);
					$stmt->bindParam(':comment', $comment);
					$stmt->execute();
				}

				$newExpireDate = $days === 'lifetime' ? '2037-12-31' : date('Y-m-d', $newExpireTime);

				$success = "<p class='success' style='text-align: center;'>Privileges added for SteamID: $steamid, Username: $name, Type: $privilegesLabel, Valid until: $newExpireDate</p>";

				file_put_contents("tf2/add-high-privileges.txt", $this->getAccessInfo() . PHP_EOL . print_r($_POST, true) . PHP_EOL, FILE_APPEND);
			}

			$config = require __DIR__ . '/../../config.php';
			include __DIR__ . '/../../Views/header.php';
			include __DIR__ . '/../../Views/tf2/add-high-privileges.php';
			include __DIR__ . '/../../Views/footer.php';
		}
	}
}
