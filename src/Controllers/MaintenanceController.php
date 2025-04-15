<?php

namespace App\Controllers;

class MaintenanceController {
	public function index() {
		$config = require __DIR__ . '/../config.php';
		include __DIR__ . '/../Views/header.php';
		include __DIR__ . '/../Views/maintenance.php';
		include __DIR__ . '/../Views/footer.php';
	}
}
