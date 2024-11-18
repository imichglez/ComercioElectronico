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
    <style>
        .card img {
            height: 200px;
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
    </style>
</head>

<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="#" class="navbar-brand">
                    <strong>Tienda Online</strong>
                </a>
                <a href="checkout.php" class="btn btn-primary">
                    Carrito <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart; ?></span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container my-4">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($resultado as $row) { 
                    $id = $row['id'];
                    
                    // Determinar la carpeta principal según la categoría
                    if (in_array($row['id_categoria'], [1, 2])) {
                        $carpeta_principal = 'hombre';
                    } elseif (in_array($row['id_categoria'], [3, 4])) {
                        $carpeta_principal = 'mujer';
                    } elseif (in_array($row['id_categoria'], [5, 6])) {
                        $carpeta_principal = 'niños';
                    } else {
                        $carpeta_principal = 'desconocido';
                    }

                    // Determinar la subcarpeta (casual o running)
                    if (in_array($row['id_categoria'], [1, 3, 5])) {
                        $subcarpeta = 'casual';
                    } elseif (in_array($row['id_categoria'], [2, 4, 6])) {
                        $subcarpeta = 'running';
                    } else {
                        $subcarpeta = 'general';
                    }

                    // Ruta de la imagen
                    $imagen = "imagenes/$carpeta_principal/$subcarpeta/" . $id . "/prueba.png";

                    // Si no existe la imagen, usar una predeterminada
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
