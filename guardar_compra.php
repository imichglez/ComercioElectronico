<?php
// Inicia la sesión y conecta con la base de datos
session_start();
require 'config/config.php';
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id_envio = $data['id_envio'] ?? null; // ID del envío
    $id_transaccion = $data['id_transaccion'] ?? null; // ID de la transacción generada por PayPal
    $fecha_compra = date('Y-m-d');
    $hora_compra = date('H:i:s');
    $total = 0;

    // Validar los datos
    if (!$id_envio || !$id_transaccion) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        exit;
    }

    // Conexión a la base de datos
    $db = new Database();
    $con = $db->conectar();

    // Calcular el total de la compra y preparar los detalles
    $productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

    if ($productos == null) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío']);
        exit;
    }

    try {
        // Inicia una transacción
        $con->beginTransaction();

        foreach ($productos as $id_producto => $cantidad) {
            $sql = $con->prepare("SELECT precio, descuento FROM zapatillas WHERE id = ?");
            $sql->execute([$id_producto]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new Exception("Producto con ID $id_producto no encontrado.");
            }

            $precio_descuento = $row['precio'] - ($row['precio'] * ($row['descuento'] / 100));
            $subtotal = $precio_descuento * $cantidad;
            $total += $subtotal;
        }

        // Registrar la compra
        $sql = $con->prepare("INSERT INTO compra (id_envio, id_transaccion, total, fecha, hora) VALUES (?, ?, ?, ?, ?)");
        $sql->execute([$id_envio, $id_transaccion, $total, $fecha_compra, $hora_compra]);

        // Obtener el ID de la compra
        $id_compra = $con->lastInsertId();

        // Registrar los detalles de la compra
        foreach ($productos as $id_producto => $cantidad) {
            $sql = $con->prepare("SELECT precio, descuento FROM zapatillas WHERE id = ?");
            $sql->execute([$id_producto]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            $precio_descuento = $row['precio'] - ($row['precio'] * ($row['descuento'] / 100));
            $subtotal = $precio_descuento * $cantidad;

            $sql_detalle = $con->prepare("INSERT INTO detalle_compra (id_compra, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)");
            $sql_detalle->execute([$id_compra, $id_producto, $cantidad, $subtotal]);
        }

        // Confirmar la transacción
        $con->commit();

        // Limpiar el carrito después de la compra
        unset($_SESSION['carrito']);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Revertir la transacción si algo falla
        $con->rollBack();
        error_log("Error en guardar_compra.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
