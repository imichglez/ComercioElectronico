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
            $sql = $con->prepare("SELECT nombre, descripcion, precio, descuento FROM zapatillas WHERE id=? and activo=1 LIMIT 1");
            $sql->execute([$id]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            $nombre = $row['nombre'];
            $descripcion = $row['descripcion'];
            $precio = $row['precio'];
            $descuento = $row['descuento'];
            $precio_desc = $precio - (($precio * $descuento) / 100);
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
    <style>
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
        }

        .product-container {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="#" class="navbar-brand">
                    <strong>Tienda Online</strong>
                </a>
                <a href="carrito.php" class="btn btn-primary">
                    Carrito <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart; ?></span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container product-container">
            <div class="row">
                <!-- Imagen del producto -->
                <div class="col-md-6">
                    <img src="imagenes/hombre/casual/<?php echo $id; ?>/prueba.png" class="product-image" alt="<?php echo $nombre; ?>">
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
                        <button class="btn btn-primary btn-lg" type="button">Comprar ahora</button>
                        <button class="btn btn-outline-primary btn-lg" type="button" onclick="addProducto(<?php echo $id; ?>, '<?php echo $token_tmp; ?>')">Agregar al carrito</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
