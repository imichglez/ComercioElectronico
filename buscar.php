<?php

require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query == '') {
    echo "No se proporcionó una consulta de búsqueda.";
    exit;
}

// Buscar productos en la base de datos
$sql = $con->prepare("SELECT id, nombre, descripcion, precio, descuento, id_categoria FROM zapatillas WHERE (nombre LIKE ? OR descripcion LIKE ?) AND activo=1");
$sql->execute(["%$query%", "%$query%"]);
$resultados = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Street Kicks - Resultados de búsqueda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
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

        .login-icon:hover .dropdown-menu {
            display: block;
        }

        .card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card img {
            height: 250px;
            object-fit: cover;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: bold;
        }

        footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
        }

        footer p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                    <a href="historial_compra.php">Mi Historial de Compras</a>
                    <a href="mi_perfil.php">Mi Perfil</a>
                    <a href="logout.php">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                <script>document.querySelector('.login-icon').onclick = () => window.location.href = 'login.php';</script>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-4">
        <h2>Resultados para "<?php echo htmlspecialchars($query); ?>"</h2>
        <?php if (count($resultados) > 0): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($resultados as $producto): 
                    $id = $producto['id'];
                    $token = hash_hmac('sha1', $id, KEY_TOKEN);
                    $categoria = in_array($producto['id_categoria'], [1, 2]) ? 'hombre' : (in_array($producto['id_categoria'], [3, 4]) ? 'mujer' : 'niños');
                    $subcarpeta = in_array($producto['id_categoria'], [1, 3, 5]) ? 'casual' : 'running';
                    $imagen = "imagenes/$categoria/$subcarpeta/$id/prueba.png";
                    if (!file_exists($imagen)) {
                        $imagen = "imagenes/nofoto.avif";
                    }
                ?>
                    <div class="col">
                        <div class="card shadow-sm">
                            <img src="<?php echo $imagen; ?>" class="card-img-top" alt="<?php echo $producto['nombre']; ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                                <p class="card-text text-muted">€<?php echo number_format($producto['precio'], 2, '.', ','); ?></p>
                                <div class="d-flex justify-content-between">
                                    <a href="detalles.php?id=<?php echo $id; ?>&token=<?php echo $token; ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                                    <button class="btn btn-outline-success btn-sm" onclick="addProducto(<?php echo $id; ?>)">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No se encontraron resultados para su búsqueda.</p>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Street Kicks. Todos los derechos reservados.</p>
    </footer>

    <script>
        function handleCartClick() {
            window.location.href = 'checkout.php';
        }

        function addProducto(id) {
            fetch('carrito_agregar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    alert('Producto agregado al carrito');
                } else {
                    alert('Error al agregar el producto');
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
