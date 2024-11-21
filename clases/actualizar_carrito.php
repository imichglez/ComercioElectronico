<?php

require '../config/config.php';
require '../config/database.php';

if (isset($_POST['action'])) {

    $action = $_POST['action'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    if ($action == 'agregar') {
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
        $respuesta = agregar($id, $cantidad);
        if ($respuesta > 0) {
            $datos['ok'] = true;
        } else {
            $datos['ok'] = false;
        }
        $datos['sub'] = MONEDA . number_format($respuesta, 2, '.', ',');
        $datos['total'] = MONEDA . number_format(calcularTotal(), 2, '.', ',');
    } else if ($action == 'eliminar') {
        $datos['ok'] = eliminar($id); // Elimina el producto
        $datos['total'] = MONEDA . number_format(calcularTotal(), 2, '.', ','); // Total actualizado
    } else {
        $datos['ok'] = false;
    }
} else {
    $datos['ok'] = false;
}

echo json_encode($datos);

function agregar($id, $cantidad)
{
    $res = 0;
    if ($id > 0 && $cantidad > 0) {
        if (isset($_SESSION['carrito']['productos'][$id])) {
            $_SESSION['carrito']['productos'][$id] += $cantidad; // Incrementar la cantidad
        } else {
            $_SESSION['carrito']['productos'][$id] = $cantidad;
        }
    }
    return $res;
}

function eliminar($id)
{
    $res = false;
    if ($id > 0) {
        unset($_SESSION['carrito']['productos'][$id]);
        $res = true;
    }
    return $res;
}

function calcularTotal()
{
    $total = 0;
    if (isset($_SESSION['carrito']['productos'])) {
        foreach ($_SESSION['carrito']['productos'] as $id => $cantidad) {
            // Se puede implementar una consulta a base de datos para obtener el precio
            // en lugar de usar un precio estático. Este es un ejemplo simplificado.
            $precio = 100; // Aquí iría el precio del producto obtenido desde la DB.
            $total += $cantidad * $precio;
        }
    }
    return $total;
}
?>
