<?php
session_start();
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

// Manejo de carrito persistente
if (isset($_SESSION['id_cliente'])) {
    $id_cliente = $_SESSION['id_cliente'];

    // Recuperar carrito de la base de datos
    $sql = $con->prepare("SELECT contenido FROM carrito WHERE id_cliente = ?");
    $sql->execute([$id_cliente]);
    $carritoData = $sql->fetch(PDO::FETCH_ASSOC);

    if ($carritoData) {
        $_SESSION['carrito'] = json_decode($carritoData['contenido'], true);
    } else {
        $_SESSION['carrito'] = [];
    }
} else {
    $_SESSION['carrito'] = $_SESSION['carrito'] ?? [];
}

// Aplicar filtros si existen
$where = "activo=1";
$filtros = [];

if (isset($_GET['genero']) && $_GET['genero'] !== '') {
    $genero = $_GET['genero'];
    if ($genero === 'hombre') {
        $where .= " AND id_categoria IN (1, 2)";
    } elseif ($genero === 'mujer') {
        $where .= " AND id_categoria IN (3, 4)";
    } elseif ($genero === 'niños') {
        $where .= " AND id_categoria IN (5, 6)";
    }
    $filtros['genero'] = $genero;
}

if (isset($_GET['tipo']) && $_GET['tipo'] !== '') {
    $tipo = $_GET['tipo'];
    if ($tipo === 'casual') {
        $where .= " AND id_categoria IN (1, 3, 5)";
    } elseif ($tipo === 'running') {
        $where .= " AND id_categoria IN (2, 4, 6)";
    }
    $filtros['tipo'] = $tipo;
}

if (isset($_GET['precio_min']) && is_numeric($_GET['precio_min'])) {
    $precioMin = floatval($_GET['precio_min']);
    $where .= " AND precio >= $precioMin";
    $filtros['precio_min'] = $precioMin;
}

if (isset($_GET['precio_max']) && is_numeric($_GET['precio_max'])) {
    $precioMax = floatval($_GET['precio_max']);
    $where .= " AND precio <= $precioMax";
    $filtros['precio_max'] = $precioMax;
}

if (isset($_GET['descuento']) && $_GET['descuento'] === '1') {
    $where .= " AND descuento > 0";
    $filtros['descuento'] = true;
}

$sql = $con->prepare("SELECT id, nombre, precio, descuento, id_categoria FROM zapatillas WHERE $where");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Street Kicks - Catálogo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Unicons CSS -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />

    <style>
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
            margin-left: 40px;
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

        .cart-icon .badge {
            position: absolute;
            top: -5px;
            right: -10px;
            font-size: 0.8rem;
            padding: 5px;
        }

        .filters {
            border-right: 1px solid #ddd;
            padding-right: 20px;
        }

        .filters h5 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .filters form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .card img {
            height: 250px;
            object-fit: cover;
        }

        .card-title {
            font-size: 1rem;
            font-weight: bold;
        }

        .precio-original {
            text-decoration: line-through;
            color: gray;
            font-size: 0.9rem;
        }

        .precio-descuento {
            color: green;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .badge-descuento {
            background-color: #28a745;
            color: white;
            font-size: 0.9rem;
            border-radius: 5px;
            padding: 2px 5px;
        }

        footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            text-align: center;
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

        .login-icon:hover .dropdown-menu {
            display: block;
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
            <span id="num_cart" class="badge bg-secondary">
                <?php echo array_sum(array_column($_SESSION['carrito'], 'cantidad')); ?>
            </span>
        </div>
        <div class="login-icon">
            <i class="uil uil-user-circle"></i>
            <?php if (isset($_SESSION['id_cliente'])): ?>
                <div class="dropdown-menu">
                    <a href="historial_compra.php">Historial de Compra</a>
                    <a href="mi_perfil.php">Mi Perfil</a>
                    <a href="logout.php">Cerrar Sesión</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <div class="container my-4">
            <div class="row">
                <aside class="col-md-3 filters">
                    <h5>Filtrar por</h5>
                    <form method="GET" action="principal.php">
                        <div class="mb-3">
                            <label for="genero" class="form-label">Género:</label>
                            <select name="genero" id="genero" class="form-select">
                                <option value="">Todos</option>
                                <option value="hombre" <?php echo (isset($filtros['genero']) && $filtros['genero'] === 'hombre') ? 'selected' : ''; ?>>Hombre</option>
                                <option value="mujer" <?php echo (isset($filtros['genero']) && $filtros['genero'] === 'mujer') ? 'selected' : ''; ?>>Mujer</option>
                                <option value="niños" <?php echo (isset($filtros['genero']) && $filtros['genero'] === 'niños') ? 'selected' : ''; ?>>Niños</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo:</label>
                            <select name="tipo" id="tipo" class="form-select">
                                <option value="">Todos</option>
                                <option value="casual" <?php echo (isset($filtros['tipo']) && $filtros['tipo'] === 'casual') ? 'selected' : ''; ?>>Casual</option>
                                <option value="running" <?php echo (isset($filtros['tipo']) && $filtros['tipo'] === 'running') ? 'selected' : ''; ?>>Running</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="precio_min" class="form-label">Precio mínimo:</label>
                            <input type="number" name="precio_min" id="precio_min" class="form-control" value="<?php echo $filtros['precio_min'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="precio_max" class="form-label">Precio máximo:</label>
                            <input type="number" name="precio_max" id="precio_max" class="form-control" value="<?php echo $filtros['precio_max'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="descuento" id="descuento" class="form-check-input" value="1" <?php echo (isset($filtros['descuento']) && $filtros['descuento']) ? 'checked' : ''; ?>>
                                <label for="descuento" class="form-check-label">Con descuento</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
                        <a href="principal.php" class="btn btn-secondary w-100 mt-2">Eliminar Filtros</a>
                    </form>
                </aside>
                <section class="col-md-9">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                        <?php foreach ($resultado as $row): 
                            $id = $row['id'];

                            // Determine main folder based on category
                            if (in_array($row['id_categoria'], [1, 2])) {
                                $carpeta_principal = 'hombre';
                            } elseif (in_array($row['id_categoria'], [3, 4])) {
                                $carpeta_principal = 'mujer';
                            } elseif (in_array($row['id_categoria'], [5, 6])) {
                                $carpeta_principal = 'niños';
                            } else {
                                $carpeta_principal = 'desconocido';
                            }

                            if (in_array($row['id_categoria'], [1, 3, 5])) {
                                $subcarpeta = 'casual';
                            } elseif (in_array($row['id_categoria'], [2, 4, 6])) {
                                $subcarpeta = 'running';
                            } else {
                                $subcarpeta = 'general';
                            }

                            $imagen = "imagenes/$carpeta_principal/$subcarpeta/" . $id . "/prueba.png";

                            if (!file_exists($imagen)) {
                                $imagen = "imagenes/nofoto.avif";
                            }
                        ?>
                            <div class="col">
                                <div class="card shadow-sm">
                                    <img src="<?php echo $imagen; ?>" class="card-img-top" alt="<?php echo $row['nombre']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $row['nombre']; ?></h5>
                                        <p class="card-text">
                                            <?php if ($row['descuento'] > 0): ?>
                                                <span class="precio-original">$<?php echo number_format($row['precio'], 2, '.', ','); ?></span>
                                                <span class="precio-descuento">$<?php echo number_format($row['precio'] - ($row['precio'] * $row['descuento'] / 100), 2, '.', ','); ?></span>
                                                <span class="badge-descuento"><?php echo $row['descuento']; ?>% de descuento</span>
                                            <?php else: ?>
                                                <span class="precio-descuento">$<?php echo number_format($row['precio'], 2, '.', ','); ?></span>
                                            <?php endif; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="detalles.php?id=<?php echo $row['id']; ?>&token=<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>" class="btn btn-primary btn-sm">Comprar ahora</a>
                                            <button class="btn btn-outline-success btn-sm" type="button" onclick="addProducto(<?php echo $row['id']; ?>)">Agregar al carrito</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>
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

        function addProducto(id) {
            fetch('carrito_agregar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    document.getElementById('num_cart').textContent = data.numero;
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
