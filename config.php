<?php
declare(strict_types=1);

session_start();

define('APP_NAME', 'The Deyli-Pdoor');
// update name
define('BASE_URL', getenv('BASE_URL') ?: '');

define('DB_HOST', getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'retro_press');
define('DB_USER', getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('POSTS_PER_PAGE', 8);

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Europe/Amsterdam');
