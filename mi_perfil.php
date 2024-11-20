<?php
session_start();
require 'config/database.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php");
    exit;
}

$id_cliente = $_SESSION['id_cliente'];

$db = new Database();
$con = $db->conectar();

// Consultar la información del cliente
$sql = $con->prepare("SELECT nombres, apellidos, email, telefono FROM clientes WHERE id = ?");
$sql->execute([$id_cliente]);
$cliente = $sql->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró al cliente
if (!$cliente) {
    echo "Error: Cliente no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .profile-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .profile-info {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="profile-title">Mi Perfil</h1>
        <div class="profile-info">
            <strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombres']); ?>
        </div>
        <div class="profile-info">
            <strong>Apellido:</strong> <?php echo htmlspecialchars($cliente['apellidos']); ?>
        </div>
        <div class="profile-info">
            <strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($cliente['email']); ?>
        </div>
        <div class="profile-info">
            <strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono']); ?>
        </div>
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Volver a la tienda</a>
        </div>
    </div>
</body>
</html>
