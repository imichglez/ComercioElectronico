<?php

require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$sql = $con->prepare("SELECT id, nombre, precio, descuento, id_categoria FROM zapatillas WHERE activo=1");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Unicons CSS -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />

    <style>
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

        .nav .cart-icon {
            font-size: 24px;
            cursor: pointer;
            margin-right: 20px;
            position: relative;
        }

        .nav .cart-icon .badge {
            position: absolute;
            top: -5px;
            right: -10px;
            font-size: 0.8rem;
            padding: 5px;
        }

        /* Product card styles */
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

        /* Footer styles */
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
            <form action="buscar.php" method="GET" class="d-flex">
                <input type="text" name="query" class="form-control" placeholder="Buscar productos" required />
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>
        <div class="cart-icon" onclick="window.location.href='checkout.php'">
            <i class="uil uil-shopping-cart"></i>
            <span id="num_cart" class="badge bg-secondary">
                <?php echo isset($_SESSION['carrito']['productos']) ? array_sum($_SESSION['carrito']['productos']) : 0; ?>
            </span>
        </div>
        <div class="login-icon" onclick="window.location.href='login.php'">
            <i class="uil uil-user-circle"></i>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container my-4">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($resultado as $row) {
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

                    // Determine subfolder (casual or running)
                    if (in_array($row['id_categoria'], [1, 3, 5])) {
                        $subcarpeta = 'casual';
                    } elseif (in_array($row['id_categoria'], [2, 4, 6])) {
                        $subcarpeta = 'running';
                    } else {
                        $subcarpeta = 'general';
                    }

                    // Image path
                    $imagen = "imagenes/$carpeta_principal/$subcarpeta/" . $id . "/prueba.png";

                    // Use a default image if the file does not exist
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
                                    <?php if ($row['descuento'] > 0) { ?>
                                        <span class="precio-original">$<?php echo number_format($row['precio'], 2, '.', ','); ?></span>
                                        <span class="precio-descuento">$<?php echo number_format($row['precio'] - ($row['precio'] * $row['descuento'] / 100), 2, '.', ','); ?></span>
                                        <span class="badge-descuento"><?php echo $row['descuento']; ?>% de descuento</span>
                                    <?php } else { ?>
                                        <span class="precio-descuento">$<?php echo number_format($row['precio'], 2, '.', ','); ?></span>
                                    <?php } ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="detalles.php?id=<?php echo $row['id']; ?>&token=<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>" class="btn btn-primary btn-sm">Comprar</a>
                                    <button class="btn btn-outline-success btn-sm" type="button" onclick="addProducto(<?php echo $row['id']; ?>, '<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>')">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
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
