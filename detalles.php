<?php

require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$id = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($id == '' || $token == '') {
    echo 'Error al procesar la petición';
    exit;
} else {
    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

    if ($token == $token_tmp) {
        $sql = $con->prepare("SELECT nombre, descripcion, precio, descuento, id_categoria FROM zapatillas WHERE id=? and activo=1 LIMIT 1");
        $sql->execute([$id]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo 'Producto no encontrado';
            exit;
        }

        $nombre = $row['nombre'];
        $descripcion = $row['descripcion'];
        $precio = $row['precio'];
        $descuento = $row['descuento'];
        $precio_desc = $precio - (($precio * $descuento) / 100);

        // Consultar tallas y stock
        $sql = $con->prepare("SELECT talla, stock FROM stock_tallas WHERE id_producto = ?");
        $sql->execute([$id]);
        $tallas = $sql->fetchAll(PDO::FETCH_ASSOC);

        // Comprobar si todas las tallas están agotadas
        $agotado = true;
        foreach ($tallas as $talla) {
            if ($talla['stock'] > 0) {
                $agotado = false;
                break;
            }
        }

        // Construcción de la ruta de la imagen
        $carpeta_principal = in_array($row['id_categoria'], [1, 2]) ? 'hombre' : (in_array($row['id_categoria'], [3, 4]) ? 'mujer' : 'niños');
        $subcarpeta = in_array($row['id_categoria'], [1, 3, 5]) ? 'casual' : 'running';
        $imagen = "imagenes/$carpeta_principal/$subcarpeta/$id/prueba.png";

        if (!file_exists($imagen)) {
            $imagen = "imagenes/nofoto.avif"; // Imagen por defecto si no se encuentra la imagen del producto
        }
    } else {
        echo 'Error al procesar la petición';
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombre; ?> - Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
        }

        .product-image {
            max-width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-title {
            font-size: 2rem;
            font-weight: bold;
        }

        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }

        .price-original {
            text-decoration: line-through;
            color: gray;
            font-size: 1rem;
        }

        .discount {
            color: #28a745;
            font-size: 1rem;
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-outline-primary {
            border-color: #007bff;
        }

        .lead {
            font-size: 1.2rem;
            color: #6c757d;
            text-align: justify;
        }

        .product-container {
            margin-top: 30px;
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
            font-size: 24px;
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

        footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            text-align: center;
            margin-top: auto;
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
            <form action="buscar.php" method="GET">
                <input type="text" name="query" class="form-control" placeholder="Buscar productos" required>
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

    <main>
        <div class="container product-container">
            <div class="row">
                <div class="col-md-6">
                    <img src="<?php echo $imagen; ?>" class="product-image" alt="<?php echo $nombre; ?>">
                </div>
                <div class="col-md-6">
                    <h2 class="product-title"><?php echo $nombre; ?></h2>
                    <?php if ($descuento > 0) { ?>
                        <p>
                            <span class="price-original"><?php echo MONEDA . number_format($precio, 2, '.', ','); ?></span>
                        </p>
                        <h2>
                            <span class="price"><?php echo MONEDA . number_format($precio_desc, 2, '.', ','); ?></span>
                            <small class="discount"><?php echo $descuento; ?>% de descuento</small>
                        </h2>
                    <?php } else { ?>
                        <h2>
                            <span class="price"><?php echo MONEDA . number_format($precio, 2, '.', ','); ?></span>
                        </h2>
                    <?php } ?>
                    <p class="lead"><?php echo $descripcion; ?></p>

                    <!-- Mostrar Producto Agotado si todas las tallas están agotadas -->
                    <?php if ($agotado): ?>
                        <p class="text-danger"><strong>Producto Agotado</strong></p>
                    <?php else: ?>
                        <!-- Selector de Talla -->
                        <div class="form-group mb-4">
                            <label for="talla">Selecciona una talla:</label>
                            <select id="talla" class="form-select">
                                <?php foreach ($tallas as $talla): ?>
                                    <option value="<?php echo $talla['talla']; ?>" <?php echo $talla['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $talla['talla']; ?> <?php echo $talla['stock'] <= 0 ? '(Agotado)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-3 col-10 mx-auto mt-4">
                            <button class="btn btn-primary btn-lg" type="button" onclick="window.location.href='index.php'">Comprar ahora</button>
                            <button class="btn btn-outline-primary btn-lg" type="button" onclick="addProducto(<?php echo $id; ?>, '<?php echo $token_tmp; ?>')">Agregar al carrito</button>
                        </div>
                    <?php endif; ?>
                </div>
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

        function addProducto(id, token) {
            const talla = document.getElementById("talla").value;
            let url = 'clases/carrito.php';
            let formData = new FormData();
            formData.append('id', id);
            formData.append('token', token);
            formData.append('talla', talla);

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            }).then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        let elemento = document.getElementById("num_cart");
                        elemento.innerHTML = data.numero;
                    }
                });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
