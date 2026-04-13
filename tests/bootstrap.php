<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$_SERVER['APP_ENV'] = 'test';
$_ENV['APP_ENV'] = 'test';

putenv('APP_ENV=test');
