<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require 'config/config.php';
require 'config/database.php';

$db = new Database();
$con = $db->conectar();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;

    // Validar que los campos no estén vacíos
    if (!$email || !$password) {
        $errors[] = "Por favor, completa ambos campos.";
    } else {
        // Buscar el usuario en la base de datos usando su correo electrónico
        $sql = $con->prepare("SELECT u.id, u.password, u.activacion, c.nombres 
                              FROM usuarios u 
                              INNER JOIN clientes c ON u.id_cliente = c.id 
                              WHERE c.email = ?");
        $sql->execute([$email]);
        $user = $sql->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar si el usuario está activado
            if ($user['activacion'] == 0) {
                $errors[] = "Tu cuenta no está activada. Por favor, verifica tu correo.";
            } else {
                // Verificar la contraseña
                if (password_verify($password, $user['password'])) {
                    // Iniciar sesión
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nombre'] = $user['nombres'];

                    // Redirigir a la página principal
                    header('Location: index.php');
                    exit;
                } else {
                    $errors[] = "La contraseña es incorrecta.";
                }
            }
        } else {
            $errors[] = "El correo electrónico no está registrado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-message {
            color: red;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="index.php" class="navbar-brand"><strong>Tienda Online</strong></a>
            </div>
        </div>
    </header>

    <main>
        <div class="container mt-4">
            <h2>Inicia sesión</h2>

            <!-- Mostrar errores -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulario de inicio de sesión -->
            <form class="row g-3" action="login.php" method="post" autocomplete="off">
                <div class="col-md-6">
                    <label for="email"><span class="text-danger">*</span>Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="password"><span class="text-danger">*</span>Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>
