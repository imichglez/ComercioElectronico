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
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <style>
        html {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }

        main {
            flex: 1;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav .logo {
            font-size: 30px;
            font-weight: bold;
            text-decoration: none;
            color: #2c2c2c;
        }

        .nav .nav-links {
            display: flex;
            list-style: none;
            gap: 15px;
            margin: 0;
            padding: 0;
        }

        .nav .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .cart-icon,
        .login-icon {
            position: relative;
            font-size: 24px;
            cursor: pointer;
        }

        .login-icon:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 50px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
        }

        .dropdown-menu a:hover {
            background: #f8f9fa;
        }

        footer {
            background-color: #e9ecef;
            padding: 20px 0;
            text-align: center;
            margin-top: auto;
        }

        .table {
            margin-top: 20px;
        }

        .table th,
        .table td {
            text-align: center;
        }
    </style>
</head>

<body>
    <nav class="nav">
        <a href="index.html" class="logo">Street Kicks</a>
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="principal.php">Catálogo</a></li>
        </ul>
        <div class="search-box">
            <form action="buscar.php" method="GET" class="d-flex">
                <input type="text" name="query" class="form-control" placeholder="Buscar productos" required />
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>
        <div class="cart-icon" onclick="handleCartClick()">
            <i class="uil uil-shopping-cart"></i>
        </div>
        <div class="login-icon">
            <i class="uil uil-user-circle"></i>
            <?php if (isset($_SESSION['id_cliente'])): ?>
                <div class="dropdown-menu">
                    <a href="historial_compra.php">Historial de Compra</a>
                    <a href="mi_perfil.php">Mi Perfil</a>
                    <a href="logout.php">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                <script>
                    document.querySelector('.login-icon').onclick = () => {
                        window.location.href = 'login.php';
                    };
                </script>
            <?php endif; ?>
        </div>
    </nav>

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

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Street Kicks. Todos los derechos reservados.</p>
    </footer>

    <script>
        function handleCartClick() {
            <?php if (isset($_SESSION['id_cliente'])): ?>
                window.location.href = 'checkout.php';
            <?php else: ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        }
    </script>
</body>

</html>
