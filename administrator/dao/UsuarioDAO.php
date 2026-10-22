<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'domain/Usuario.php';
include_once 'util/Connection.php';

/**
 * Description of UsuarioDAO
 *
 * @author Cesardl
 */
class UsuarioDAO {

    public function getUsuario($user, $passwd) {
        $conexion = new Connection();
        // Look the user up by name, then check the password in PHP. Matching
        // on both columns at once meant the database compared the password in
        // clear text, and left nowhere for this app to understand a hash --
        // which it now has to, because the PHP 8 app shares this table.
        $user_escaped = mysql_real_escape_string($user, $conexion->getConnection());
        $query = "SELECT id, usuario, passwd FROM usuario WHERE usuario='$user_escaped' LIMIT 1";
        $result = mysql_query($query, $conexion->getConnection());
        $total_registros = mysql_num_rows($result);

        $usuario = new Usuario();
        if ($total_registros > 0) {
            $row = mysql_fetch_assoc($result);
            $stored = $row['passwd'];

            $info = password_get_info($stored);
            $valid = $info['algo']
                ? password_verify($passwd, $stored)
                : ($stored === $passwd);

            if ($valid) {
                $usuario->setId($row['id']);
                $usuario->setUser($row['usuario']);
            }
        }
        return $usuario;
    }

}

?>
