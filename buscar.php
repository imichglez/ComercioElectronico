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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de búsqueda - Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="index.html" class="navbar-brand">
                    <strong>Tienda Online</strong>
                </a>
            </div>
        </div>
    </header>

    <main class="container my-4">
        <h2>Resultados para "<?php echo htmlspecialchars($query); ?>"</h2>
        <?php if (count($resultados) > 0): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($resultados as $producto): 
                    $id = $producto['id'];
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
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                                <p class="card-text">$<?php echo number_format($producto['precio'], 2, '.', ','); ?></p>
                                <a href="detalles.php?id=<?php echo $producto['id']; ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No se encontraron resultados para su búsqueda.</p>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
