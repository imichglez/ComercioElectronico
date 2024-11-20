<?php
session_start();
require 'config/config.php';
require 'config/database.php';

if (!isset($_SESSION['id_cliente'])) {
    // Si no hay cliente en la sesión, redirigir al inicio de sesión
    header("Location: login.php");
    exit;
}

$db = new Database();
$con = $db->conectar();

$id_cliente = $_SESSION['id_cliente'];

// Obtener las compras del cliente
$sql = $con->prepare("SELECT id, fecha, total, id_transaccion FROM compra WHERE id_cliente = ? ORDER BY fecha DESC");
$sql->execute([$id_cliente]);
$compras = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table {
            margin-top: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="index.php" class="navbar-brand">Tienda Online</a>
                <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <main class="container my-4">
        <h2>Historial de Compras</h2>

        <?php if (count($compras) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>ID Transacción</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $key => $compra): ?>
                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo $compra['fecha']; ?></td>
                            <td>$<?php echo number_format($compra['total'], 2); ?></td>
                            <td><?php echo $compra['id_transaccion']; ?></td>
                            <td>
                                <a href="detalle_compra.php?id=<?php echo $compra['id']; ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No has realizado ninguna compra.</p>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
