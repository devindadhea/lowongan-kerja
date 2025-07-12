<?php

use Medoo\Medoo;

// Kembalikan instance Medoo
return new Medoo([
    'type'     => 'mysql',
    'host'     => 'localhost',
    'database' => 'lowongan_kerja',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
]);
