<?php

declare(strict_types=1);

/*
 * Prepares the usuario table for hashed credentials.
 *
 *   php bin/migrate-passwords.php
 *
 * Three steps, all idempotent, so re-running it is harmless:
 *
 *  1. usuario is the only MyISAM table left. That means writes to it ignore
 *     transactions, so the repository tests could not roll back and a failed
 *     multi-step change could not be undone. Converted to InnoDB.
 *  2. passwd is varchar(50). A bcrypt hash is 60 characters, so storing one
 *     would silently truncate it and lock the account out. Widened to 255,
 *     which also leaves room for whatever PASSWORD_DEFAULT becomes.
 *  3. Any password still held in clear text is replaced with a hash of
 *     itself. Rows already hashed are left alone.
 */

use Buffalo\Config;
use Buffalo\Database\ConnectionFactory;
use Buffalo\Security\Authenticator;

require dirname(__DIR__) . '/vendor/autoload.php';

$pdo = (new ConnectionFactory(Config::fromEnvironment()))->create();

$engine = $pdo->query(
    "SELECT engine FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = 'usuario'",
)->fetchColumn();

if (strtolower((string) $engine) !== 'innodb') {
    $pdo->exec('ALTER TABLE usuario ENGINE=InnoDB');
    echo "usuario converted from {$engine} to InnoDB\n";
} else {
    echo "usuario already InnoDB\n";
}

$length = $pdo->query(
    "SELECT character_maximum_length FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'usuario' AND column_name = 'passwd'",
)->fetchColumn();

if ((int) $length < 255) {
    $pdo->exec('ALTER TABLE usuario MODIFY passwd VARCHAR(255) NOT NULL');
    echo "passwd widened from varchar({$length}) to varchar(255)\n";
} else {
    echo "passwd already varchar({$length})\n";
}

$rows = $pdo->query('SELECT id, usuario, passwd FROM usuario ORDER BY id')->fetchAll();
$update = $pdo->prepare('UPDATE usuario SET passwd = :passwd WHERE id = :id');
$hashed = 0;

foreach ($rows as $row) {
    if (Authenticator::isHashed((string) $row['passwd'])) {
        echo "  {$row['usuario']}: already hashed\n";
        continue;
    }

    $update->execute([
        'passwd' => Authenticator::hash((string) $row['passwd']),
        'id' => $row['id'],
    ]);
    ++$hashed;
    echo "  {$row['usuario']}: hashed\n";
}

printf("done: %d of %d row(s) hashed\n", $hashed, count($rows));
