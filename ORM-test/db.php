<?php

// database.php
use Illuminate\Database\Capsule\Manager as Capsule;

require 'vendor/autoload.php';

$capsule = new Capsule;

// Cấu hình kết nối cơ sở dữ liệu
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'database_name',
    'username' => 'username',
    'password' => 'password',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

// Thiết lập Eloquent ORM
$capsule->setAsGlobal();
$capsule->bootEloquent();