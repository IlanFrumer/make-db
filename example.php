<?php

require_once 'vendor/autoload.php';

use \MakeDB\Lib\MySQLDatabase as DB;

$db = new DB('localhost', 'test', 'root', '1234');


$mdb = new \MakeDB\MakeDB();

$mdb->using($db);

$users  = $mdb->table('users');

$users->delete()->set('AUTO_INCREMENT', 10000);

$mdb->run();
