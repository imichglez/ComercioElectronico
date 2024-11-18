<?php

require 'config/config.php';
require 'config/database.php';
require 'clases/clienteFunciones.php';

$db = new Database();
$con = $db->conectar();

$errors = [];

if (!empty($_POST)) {

    // Sanitización de los campos
    $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : null;
    $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $repassword = isset($_POST['repassword']) ? trim($_POST['repassword']) : null;

    // Validación de campos requeridos
    if (!$nombres || !$apellidos || !$email || !$telefono || !$usuario || !$password || !$repassword) {
        $errors[] = "Todos los campos son obligatorios.";
    }

    // Validación de contraseñas
    if ($password !== $repassword) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (count($errors) === 0) {
        // Registro del cliente
        $id = registraCliente([$nombres, $apellidos, $email, $telefono], $con);

        if ($id > 0) {
            // Hash de la contraseña y generación de token
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $token = generarToken();

            if (!registraUsuario([$usuario, $pass_hash, $token, $id], $con)) {
                $errors[] = "Error al registrar usuario.";
            }
        } else {
            $errors[] = "Error al registrar cliente.";
        }
    }

    // Manejo de errores
    if (count($errors) === 0) {
        echo "Registro exitoso.";
    } else {
        print_r($errors); // Esto puede reemplazarse por una mejor visualización de errores
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="#" class="navbar-brand">
                    <strong>Tienda Online</strong>
                </a>
                <a href="checkout.php" class="btn btn-primary">
                    Carrito <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart ?? 0; ?></span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container mt-4">
            <h2>Datos del cliente</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="row g-3" action="registro.php" method="post" autocomplete="off">
                <div class="col-md-6">
                    <label for="nombres"><span class="text-danger">*</span>Nombres</label>
                    <input type="text" name="nombres" id="nombres" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="apellidos"><span class="text-danger">*</span>Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="email"><span class="text-danger">*</span>Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="telefono"><span class="text-danger">*</span>Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="usuario"><span class="text-danger">*</span>Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="password"><span class="text-danger">*</span>Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="repassword"><span class="text-danger">*</span>Repetir Contraseña</label>
                    <input type="password" name="repassword" id="repassword" class="form-control" required>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
