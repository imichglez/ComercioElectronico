<?php

define("KEY_TOKEN", "ABCdef123@--");
define("MONEDA", "$");

// Iniciar la sesión solo si no está ya activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$num_cart = 0;
if (isset($_SESSION['carrito']['productos'])) {
    $num_cart = count($_SESSION['carrito']['productos']);
}

?>
