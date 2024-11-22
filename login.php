<?php
session_start();
require 'config/config.php';
require 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!empty($correo) && !empty($password)) {
        // Consulta para buscar el usuario por correo
        $sql = $con->prepare("
            SELECT u.id, u.password, u.activacion, u.id_cliente, c.nombres, c.apellidos 
            FROM usuarios u 
            INNER JOIN clientes c ON u.id_cliente = c.id
            WHERE c.email = ? LIMIT 1
        ");
        $sql->execute([$correo]);
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Verifica si la cuenta está activada
            if ($usuario['activacion'] == 1) {
                // Verifica la contraseña
                if (password_verify($password, $usuario['password'])) {
                    // Configura las variables de sesión
                    $_SESSION['id_usuario'] = $usuario['id'];
                    $_SESSION['id_cliente'] = $usuario['id_cliente'];
                    $_SESSION['nombre'] = $usuario['nombres'];
                    $_SESSION['apellido'] = $usuario['apellidos'];

                    // Redirige al usuario al carrito o página principal
                    header('Location: index.html');
                    exit;
                } else {
                    $error = "La contraseña es incorrecta.";
                }
            } else {
                $error = "Tu cuenta no está activada.";
            }
        } else {
            $error = "El correo no está registrado.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Street Kicks - Iniciar Sesión</title>
    <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilo para mantener el footer en la parte inferior */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            flex: 1; /* Hace que el contenido principal ocupe todo el espacio disponible */
        }

        #footer-container {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Iniciar Sesión</h2>

        <!-- Mostrar alertas de error si existen -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="mt-4">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico:</label>
                <input type="email" name="correo" id="correo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
    </div>

    <!-- Footer -->
    <div id="footer-container">
        <p>&copy; <?php echo date("Y"); ?> Street Kicks. Todos los derechos reservados.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
