<?php
require '../config/config.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $token = $_POST['token'];

    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

    if ($token == $token_tmp) {  
        // Si el producto ya existe en el carrito, incrementa su cantidad
        if (isset($_SESSION['carrito']['productos'][$id])) {
            $_SESSION['carrito']['productos'][$id] += 1; // Incrementa la cantidad
        } else {    
            // Si no existe, agrega el producto al carrito con cantidad 1
            $_SESSION['carrito']['productos'][$id] = 1;
        }

        // Calcular el total de productos en el carrito
        $totalProductos = 0;
        foreach ($_SESSION['carrito']['productos'] as $cantidad) {
            $totalProductos += $cantidad;
        }

        // Devolver el número total de productos en el carrito
        $datos['numero'] = $totalProductos;
        $datos['ok'] = true;

    } else {
        $datos['ok'] = false; // Token no válido
    }

} else {
    $datos['ok'] = false; // No se recibió un ID
}

echo json_encode($datos); // Respuesta JSON para el frontend
