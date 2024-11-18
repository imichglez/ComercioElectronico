<?php

function generarToken()
{
    return md5(uniqid(mt_rand(), false)); // Genera un token único
}

function registraCliente(array $datos, $con)
{
    try {
        // Preparamos la consulta
        $sql = $con->prepare(
            "INSERT INTO clientes (nombres, apellidos, email, telefono, estatus, fecha_alta) 
            VALUES (?, ?, ?, ?, 1, NOW())"
        );

        // Ejecutamos con los datos proporcionados
        if ($sql->execute($datos)) {
            return $con->lastInsertId(); // Retorna el ID del cliente recién insertado
        }

        return 0; // Si falla, retorna 0
    } catch (PDOException $e) {
        // Manejo de errores
        error_log("Error en registraCliente: " . $e->getMessage());
        return 0;
    }
}

function registraUsuario(array $datos, $con)
{
    try {
        // Preparamos la consulta
        $sql = $con->prepare(
            "INSERT INTO usuarios (usuario, password, token, id_cliente) 
            VALUES (?, ?, ?, ?)"
        );

        // Ejecutamos con los datos proporcionados
        if ($sql->execute($datos)) {
            return true; // Retorna true si se inserta correctamente
        }

        return false; // Retorna false si falla
    } catch (PDOException $e) {
        // Manejo de errores
        error_log("Error en registraUsuario: " . $e->getMessage());
        return false;
    }
}
