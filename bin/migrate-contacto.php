<?php

declare(strict_types=1);

/*
 * Brings the contacto table up to the shape the application expects.
 *
 *   php bin/migrate-contacto.php
 *
 * database/buffalo_data.sql already carries that shape, so a database loaded
 * from the seed has nothing to do here and the script says so. It exists for
 * the databases that are already running, which is all of them.
 *
 * Two steps, both idempotent:
 *
 *  1. Create the table, for a database that predates the contact form.
 *  2. Add the three columns the administrator's inbox needs, for a database
 *     that has the table but not the state the inbox moves messages through.
 */

use Buffalo\Config;
use Buffalo\Database\ConnectionFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

$pdo = (new ConnectionFactory(Config::fromEnvironment()))->create();

$tableExists = (bool) $pdo->query(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = 'contacto'",
)->fetchColumn();

if (!$tableExists) {
    // utf8mb4, unlike the tables around it: this is the one table whose
    // contents are typed by strangers, and utf8 would reject anything outside
    // the basic plane -- an emoji in a comment would fail the insert.
    //
    // atendido_por holds a usuario id but declares no foreign key: usuario is
    // MyISAM until bin/migrate-passwords.php converts it, and InnoDB cannot
    // point a constraint at a MyISAM table.
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
          `estado` enum('nueva','atendida') NOT NULL default 'nueva',
          `fecha_atencion` datetime default NULL,
          `atendido_por` int(11) default NULL,
          PRIMARY KEY  (`id_contacto`),
          KEY `contacto_fecha` (`fecha_registro`),
          KEY `contacto_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    );

    echo "contacto created\n";

    return;
}

echo "contacto already exists\n";

$columnas = [
    'estado' => "ADD COLUMN `estado` enum('nueva','atendida') NOT NULL default 'nueva',
                 ADD KEY `contacto_estado` (`estado`)",
    'fecha_atencion' => 'ADD COLUMN `fecha_atencion` datetime default NULL',
    'atendido_por' => 'ADD COLUMN `atendido_por` int(11) default NULL',
];

$statement = $pdo->prepare(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'contacto' AND column_name = :columna",
);

foreach ($columnas as $columna => $sql) {
    $statement->execute(['columna' => $columna]);

    if ((bool) $statement->fetchColumn()) {
        echo "  {$columna}: already there\n";
        continue;
    }

    $pdo->exec("ALTER TABLE contacto {$sql}");
    echo "  {$columna}: added\n";
}
