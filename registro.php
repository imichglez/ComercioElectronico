<?php

require 'config/config.php';
require 'config/database.php';
require 'clases/clienteFunciones.php';

$db = new Database();
$con = $db->conectar();

$errors = [];
$emailError = '';
$usuarioError = '';
$telefonoError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización de los campos
    $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : null;
    $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $repassword = isset($_POST['repassword']) ? trim($_POST['repassword']) : null;

    // Validar unicidad del correo, usuario y teléfono
    $sql = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
    $sql->execute([$usuario]);
    if ($sql->fetchColumn() > 0) {
        $usuarioError = "El nombre de usuario ya está en uso.";
    }

    $sql = $con->prepare("SELECT COUNT(*) FROM clientes WHERE email = ?");
    $sql->execute([$email]);
    if ($sql->fetchColumn() > 0) {
        $emailError = "El correo electrónico ya está registrado.";
    }

    $sql = $con->prepare("SELECT COUNT(*) FROM clientes WHERE telefono = ?");
    $sql->execute([$telefono]);
    if ($sql->fetchColumn() > 0) {
        $telefonoError = "El número de teléfono ya está registrado.";
    }

    // Si no hay errores de unicidad, registrar al cliente y usuario
    if (!$emailError && !$usuarioError && !$telefonoError) {
        // Registro del cliente
        $id = registraCliente([$nombres, $apellidos, $email, $telefono], $con);

        if ($id > 0) {
            // Hash de la contraseña y generación de token
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $token = generarToken();

            if (registraUsuario([$usuario, $pass_hash, $token, $id], $con)) {
                // Iniciar sesión automáticamente
                session_start();
                $_SESSION['id_usuario'] = $con->lastInsertId(); // ID del usuario insertado
                $_SESSION['id_cliente'] = $id;
                $_SESSION['nombre'] = $nombres;
                $_SESSION['apellido'] = $apellidos;

                // Redirigir a la página principal
                echo "<script>alert('Registro exitoso. Redirigiendo a la página principal.');</script>";
                echo "<script>window.location.href = 'index.html';</script>";
                exit;
            } else {
                $errors[] = "Error al registrar usuario.";
            }
        } else {
            $errors[] = "Error al registrar cliente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Street Kicks - Registro</title>
    <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-message {
            color: red;
            font-size: 0.875rem;
        }

        .is-invalid {
            border-color: red;
        }

        /* Aseguramos que el footer siempre esté al final */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
        }

        footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            text-align: center;
        }
    </style>
</head>

<body>
    <main>
        <div class="container mt-5">
            <h2 class="text-center">Registro</h2>

            <form id="formRegistro" class="mt-4" method="POST" action="registro.php">
                <div class="mb-3">
                    <label for="nombres">Nombres</label>
                    <input type="text" name="nombres" id="nombres" class="form-control" required>
                    <div id="nombres-error" class="error-message"></div>
                </div>
                <div class="mb-3">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                    <div id="apellidos-error" class="error-message"></div>
                </div>
                <div class="mb-3">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control <?php echo $emailError ? 'is-invalid' : ''; ?>" required>
                    <div id="email-error" class="error-message"><?php echo $emailError; ?></div>
                </div>
                <div class="mb-3">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control <?php echo $telefonoError ? 'is-invalid' : ''; ?>" required>
                    <div id="telefono-error" class="error-message"><?php echo $telefonoError; ?></div>
                </div>
                <div class="mb-3">
                    <label for="usuario">Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control <?php echo $usuarioError ? 'is-invalid' : ''; ?>" required>
                    <div id="usuario-error" class="error-message"><?php echo $usuarioError; ?></div>
                </div>
                <div class="mb-3">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <div id="password-error" class="error-message"></div>
                </div>
                <div class="mb-3">
                    <label for="repassword">Confirmar Contraseña</label>
                    <input type="password" name="repassword" id="repassword" class="form-control" required>
                    <div id="repassword-error" class="error-message"></div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Tienda Online. Todos los derechos reservados.</p>
    </footer>

    <script>
        const form = document.getElementById('formRegistro');

        const validateField = (field, errorId, validationFunction) => {
            const errorMessage = validationFunction(field.value);
            const errorElement = document.getElementById(errorId);

            if (errorMessage) {
                field.classList.add('is-invalid');
                errorElement.textContent = errorMessage;
            } else {
                field.classList.remove('is-invalid');
                errorElement.textContent = '';
            }
        };

        const validations = {
            nombres: (value) => (value.trim() === '' ? 'El nombre es obligatorio.' : ''),
            apellidos: (value) => (value.trim() === '' ? 'El apellido es obligatorio.' : ''),
            email: (value) =>
                !/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(value) ? 'Debe ser un correo válido.' : '',
            telefono: (value) =>
                !/^\+?[0-9]{10,15}$/.test(value) ? 'Debe ser un número de teléfono válido.' : '',
            usuario: (value) => (value.trim() === '' ? 'El usuario es obligatorio.' : ''),
            password: (value) =>
                !/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W]).{8,}$/.test(value)
                    ? 'Debe tener al menos 8 caracteres, incluyendo mayúscula, minúscula, número y carácter especial.'
                    : '',
            repassword: (value) =>
                value !== document.getElementById('password').value
                    ? 'Las contraseñas no coinciden.'
                    : '',
        };

        form.addEventListener('input', (event) => {
            const field = event.target;
            const fieldId = field.id;
            const errorId = `${fieldId}-error`;

            if (validations[fieldId]) {
                validateField(field, errorId, validations[fieldId]);
            }
        });

        form.addEventListener('submit', (event) => {
            let isValid = true;

            Object.keys(validations).forEach((fieldId) => {
                const field = document.getElementById(fieldId);
                const errorId = `${fieldId}-error`;

                validateField(field, errorId, validations[fieldId]);

                if (document.getElementById(errorId).textContent !== '') {
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault();
            }
        });
    </script>
</body>

</html>
