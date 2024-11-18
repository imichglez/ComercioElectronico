<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Página Privada</title>
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h1>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
