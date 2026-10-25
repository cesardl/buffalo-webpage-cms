<?php

declare(strict_types=1);

/*
 * Adds the contacto table to a database that predates the contact form.
 *
 *   php bin/migrate-contacto.php
 *
 * database/buffalo_data.sql already carries the table, so a database loaded
 * from the seed has it and this script says so and stops. It exists for the
 * databases that are already running, which is all of them.
 *
 * Idempotent: re-running it is harmless.
 */

use Buffalo\Config;
use Buffalo\Database\ConnectionFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

$pdo = (new ConnectionFactory(Config::fromEnvironment()))->create();

$exists = (bool) $pdo->query(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = 'contacto'",
)->fetchColumn();

if ($exists) {
    echo "contacto already exists\n";

    return;
}

// utf8mb4, unlike the tables around it: this is the one table whose contents
// are typed by strangers, and utf8 would reject anything outside the basic
// plane -- an emoji in a comment would fail the insert.
$pdo->exec(
    "CREATE TABLE `contacto` (
      `id_contacto` int(11) NOT NULL auto_increment,
      `nombre` varchar(100) NOT NULL,
      `email` varchar(150) NOT NULL,
      `telefono` varchar(30) NOT NULL,
      `direccion` varchar(150) NOT NULL,
      `ciudad` varchar(80) NOT NULL,
      `comentarios` varchar(1000) NOT NULL,
      `fecha_registro` datetime NOT NULL,
      PRIMARY KEY  (`id_contacto`),
      KEY `contacto_fecha` (`fecha_registro`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
);

echo "contacto created\n";
