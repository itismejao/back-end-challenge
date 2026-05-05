<?php

// Force testing database connection — prevents tests from touching the main database.
// This is necessary because Docker's env_file sets OS-level env vars that override
// phpunit.xml and .env.testing values.
putenv('DB_CONNECTION=mysql_testing');
$_ENV['DB_CONNECTION'] = 'mysql_testing';
$_SERVER['DB_CONNECTION'] = 'mysql_testing';

require __DIR__.'/../vendor/autoload.php';
