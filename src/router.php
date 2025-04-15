<?php

use FastRoute\RouteCollector;

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {
	$config = require __DIR__ . '/../src/config.php';

	$r->addRoute('GET', '/', ['App\Controllers\HomeController', 'index']);

	foreach ($config['games'] as $game) {
		foreach ($game['panels'] as $panel) {
			if ($panel['active'] ?? true) {
				$controllerPath = str_replace('/', '\\', $panel['controller']);
				$r->addRoute('GET', $panel['route'], ['App\Controllers\\' . $controllerPath, $panel['method']]);
				$formMethod = isset($panel['form_method']) ? $panel['form_method'] : $panel['method'];
				$r->addRoute('POST', $panel['route'], ['App\Controllers\\' . $controllerPath, $formMethod]);
			} else {
				$r->addRoute('GET', $panel['route'], ['App\Controllers\MaintenanceController', 'index']);
			}
		}
	}
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
	$uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
	case FastRoute\Dispatcher::NOT_FOUND:
		http_response_code(404);
		echo '404 Not Found';
		break;
	case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		http_response_code(405);
		echo '405 Method Not Allowed';
		break;
	case FastRoute\Dispatcher::FOUND:
		$handler = $routeInfo[1];
		$vars = $routeInfo[2];
		[$class, $method] = $handler;

		try {
			$controller = new $class;
			call_user_func_array([$controller, $method], $vars);
		} catch (Exception $e) {
			error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
			
			http_response_code(500);
			echo '500 Internal Server Error';
		}
		break;
}
