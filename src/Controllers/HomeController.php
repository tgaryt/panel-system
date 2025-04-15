<?php

namespace App\Controllers;

class HomeController {
	public function index() {
		$config = require __DIR__ . '/../config.php';
		include __DIR__ . '/../Views/header.php';
		include __DIR__ . '/../Views/home.php';
		include __DIR__ . '/../Views/footer.php';
	}
}
