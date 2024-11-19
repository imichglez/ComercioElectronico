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
        $sql = $con->prepare("SELECT count(id) FROM zapatillas WHERE id=? and activo=1");
        $sql->execute([$id]);
        if ($sql->fetchColumn() > 0) {
            $sql = $con->prepare("SELECT nombre, descripcion, precio, descuento, id_categoria FROM zapatillas WHERE id=? and activo=1 LIMIT 1");
            $sql->execute([$id]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            $nombre = $row['nombre'];
            $descripcion = $row['descripcion'];
            $precio = $row['precio'];
            $descuento = $row['descuento'];
            $id_categoria = $row['id_categoria'];
            $precio_desc = $precio - (($precio * $descuento) / 100);

            // Determinar la carpeta principal y subcarpeta
            $carpeta_principal = in_array($id_categoria, [1, 2]) ? 'hombre' : (in_array($id_categoria, [3, 4]) ? 'mujer' : 'niños');
            $subcarpeta = in_array($id_categoria, [1, 3, 5]) ? 'casual' : 'running';

            // Ruta de la imagen
            $imagen = "imagenes/$carpeta_principal/$subcarpeta/$id/prueba.png";

            // Verificar si la imagen existe
            if (!file_exists($imagen)) {
                $imagen = "imagenes/nofoto.avif";
            }
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
    <!-- Bootstrap CSS -->
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

        /* Navbar styles */
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

        .nav .search-box {
            display: flex;
            align-items: center;
        }

        .nav .search-box input {
            width: 200px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .nav .search-box button {
            margin-left: 5px;
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 3px;
        }

        .nav .cart-icon {
            font-size: 24px;
            cursor: pointer;
            margin-right: 20px;
        }

        .nav .login-icon {
            font-size: 24px;
            cursor: pointer;
        }

        footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            text-align: center;
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
            <form action="buscar.php" method="GET">
                <input type="text" name="query" class="form-control" placeholder="Buscar productos" required>
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>
        <div class="cart-icon" onclick="window.location.href='checkout.php'">
            <i class="uil uil-shopping-cart"></i>
        </div>
        <div class="login-icon" onclick="window.location.href='login.php'">
            <i class="uil uil-user-circle"></i>
        </div>
    </nav>

    <main>
        <div class="container product-container">
            <div class="row">
                <!-- Imagen del producto -->
                <div class="col-md-6">
                    <img src="<?php echo $imagen; ?>" class="product-image" alt="<?php echo $nombre; ?>">
                </div>

                <!-- Información del producto -->
                <div class="col-md-6">
                    <h2 class="product-title"><?php echo $nombre; ?></h2>

                    <!-- Precios -->
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

                    <!-- Descripción -->
                    <p class="lead"><?php echo $descripcion; ?></p>

                    <!-- Botones -->
                    <div class="d-grid gap-3 col-10 mx-auto">
                        <button class="btn btn-primary btn-lg" type="button" onclick="window.location.href='index.php'">Comprar ahora</button>
                        <button class="btn btn-outline-primary btn-lg" type="button" onclick="addProducto(<?php echo $id; ?>, '<?php echo $token_tmp; ?>')">Agregar al carrito</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Street Kicks. Todos los derechos reservados.</p>
    </footer>

    <script>
        function addProducto(id, token) {
            let url = 'clases/carrito.php';
            let formData = new FormData();
            formData.append('id', id);
            formData.append('token', token);

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
