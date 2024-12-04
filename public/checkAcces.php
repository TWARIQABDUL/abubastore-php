<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/middleware/AuthMiddleware.php';

$midleWare = new AuthMiddleware();

$status = $midleWare->handle();

echo $status;