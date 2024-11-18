<?php

require 'config/config.php';
require 'config/database.php';
require 'clases/clienteFunciones.php';

$db = new Database();
$con = $db->conectar();

$errors = [];
$emailError = '';
$usuarioError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Validación del correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "El formato del correo electrónico no es válido.";
    }

    // Validación del número de teléfono
    if (!preg_match('/^\+?[0-9]{10,15}$/', $telefono)) {
        $errors[] = "El número de teléfono no es válido.";
    }

    // Validación de contraseñas
    if ($password !== $repassword) {
        $errors[] = "Las contraseñas no coinciden.";
    }
    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "La contraseña debe incluir al menos una letra mayúscula.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "La contraseña debe incluir al menos una letra minúscula.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "La contraseña debe incluir al menos un número.";
    }
    if (!preg_match('/[\W]/', $password)) {
        $errors[] = "La contraseña debe incluir al menos un carácter especial.";
    }

    // Validar unicidad del correo y usuario
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

    // Si no hay errores, registrar al cliente y usuario
    if (count($errors) === 0 && !$emailError && !$usuarioError) {
        // Registro del cliente
        $id = registraCliente([$nombres, $apellidos, $email, $telefono], $con);

        if ($id > 0) {
            // Hash de la contraseña y generación de token
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $token = generarToken();

            if (registraUsuario([$usuario, $pass_hash, $token, $id], $con)) {
                echo "<div class='alert alert-success'>Registro exitoso.</div>";
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
    <title>Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-message {
            color: red;
            font-size: 0.875rem;
        }

        .is-invalid {
            border-color: red;
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="#" class="navbar-brand"><strong>Tienda Online</strong></a>
                <a href="checkout.php" class="btn btn-primary">
                    Carrito <span id="num_cart" class="badge bg-secondary">0</span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container mt-4">
            <h2>Datos del cliente</h2>

            <form class="row g-3" action="registro.php" method="post" autocomplete="off" id="formRegistro">
                <div class="col-md-6">
                    <label for="nombres"><span class="text-danger">*</span>Nombres</label>
                    <input type="text" name="nombres" id="nombres" class="form-control" value="<?php echo htmlspecialchars($nombres ?? ''); ?>" required>
                    <div id="nombres-error" class="error-message"></div>
                </div>
                <div class="col-md-6">
                    <label for="apellidos"><span class="text-danger">*</span>Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control" value="<?php echo htmlspecialchars($apellidos ?? ''); ?>" required>
                    <div id="apellidos-error" class="error-message"></div>
                </div>
                <div class="col-md-6">
                    <label for="email"><span class="text-danger">*</span>Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control <?php echo $emailError ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    <div id="email-error" class="error-message"><?php echo $emailError; ?></div>
                </div>
                <div class="col-md-6">
                    <label for="telefono"><span class="text-danger">*</span>Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($telefono ?? ''); ?>" required>
                    <div id="telefono-error" class="error-message"></div>
                </div>
                <div class="col-md-6">
                    <label for="usuario"><span class="text-danger">*</span>Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control <?php echo $usuarioError ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($usuario ?? ''); ?>" required>
                    <div id="usuario-error" class="error-message"><?php echo $usuarioError; ?></div>
                </div>
                <div class="col-md-6">
                    <label for="password"><span class="text-danger">*</span>Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <div id="password-error" class="error-message"></div>
                </div>
                <div class="col-md-6">
                    <label for="repassword"><span class="text-danger">*</span>Repetir Contraseña</label>
                    <input type="password" name="repassword" id="repassword" class="form-control" required>
                    <div id="repassword-error" class="error-message"></div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </main>

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
