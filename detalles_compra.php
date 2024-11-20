<?php
session_start();
require 'config/database.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php");
    exit;
}

// Validar que se reciba el ID de la compra como parámetro
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID de compra no proporcionado.";
    exit;
}

$id_compra = $_GET['id'];
$id_cliente = $_SESSION['id_cliente'];

$db = new Database();
$con = $db->conectar();

// Obtener la información de la compra
$sql = $con->prepare("SELECT * FROM compra WHERE id = ? AND id_cliente = ?");
$sql->execute([$id_compra, $id_cliente]);
$compra = $sql->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    echo "Compra no encontrada o no tienes permiso para verla.";
    exit;
}

// Obtener los detalles de la compra
$sql = $con->prepare("SELECT * FROM detalle_compra WHERE id_compra = ?");
$sql->execute([$id_compra]);
$detalles = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a href="index.php" class="navbar-brand">Tienda Online</a>
            <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <main class="container my-4">
        <h2>Detalles de la Compra #<?php echo $compra['id']; ?></h2>
        <p><strong>Fecha:</strong> <?php echo $compra['fecha']; ?></p>
        <p><strong>Total Pagado:</strong> $<?php echo number_format($compra['total'], 2); ?></p>
        <p><strong>ID de Transacción:</strong> <?php echo $compra['id_transaccion']; ?></p>

        <h4>Productos:</h4>
        <?php if (count($detalles) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $detalle): ?>
                        <tr>
                            <td><?php echo $detalle['nombre']; ?></td>
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td>$<?php echo number_format($detalle['precio'], 2); ?></td>
                            <td>$<?php echo number_format($detalle['cantidad'] * $detalle['precio'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron productos en esta compra.</p>
        <?php endif; ?>

        <a href="historial_compra.php" class="btn btn-primary">Regresar al Historial</a>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
