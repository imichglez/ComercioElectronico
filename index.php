<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Opciones de Envío y Pago</title>
  <script src="https://www.paypal.com/sdk/js?client-id=AR8xACZzL1pYEHoqxITx1YkVoDPQjE3GNOh6T0lV0CPNkT-YtNO6BgowOsDHKiaZqghMKk3I2ckApAdq"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f7f7f7;
    }
    .container {
      display: flex;
      justify-content: space-between;
      padding: 20px;
      max-width: 1200px;
      margin: auto;
    }
    .form-container {
      width: 60%;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    label {
      font-weight: bold;
    }
    input {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      background-color: #000;
      color: #fff;
      padding: 10px;
      font-size: 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background-color: #444;
    }
    .summary-container {
      width: 35%;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .summary-title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 15px;
    }
    .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .summary-total {
      font-size: 18px;
      font-weight: bold;
      margin-top: 20px;
    }
    #paypal-button-container {
      margin-top: 20px;
    }
    /* Estilo del modal */
    .modal {
      display: none; /* Ocultar por defecto */
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background-color: white;
      padding: 30px;
      border-radius: 8px;
      text-align: center;
      width: 300px;
    }
    .modal button {
      background-color: #000;
      color: #fff;
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 4px;
      margin: 10px;
      cursor: pointer;
    }
    .modal button:hover {
      background-color: #444;
    }
  </style>
</head>
<body>

<div class="container">
  <!-- Formulario de Envío -->
  <div class="form-container">
    <h2>Datos de Envío</h2>
    <form id="shipping-form" action="guardar_envio.php" method="POST">
      <label for="nombre">Nombre:</label>
      <input type="text" id="nombre" name="nombre" required>

      <label for="apellidos">Apellidos:</label>
      <input type="text" id="apellidos" name="apellidos" required>

      <label for="calle">Calle:</label>
      <input type="text" id="calle" name="calle" required>

      <label for="numero">Número:</label>
      <input type="text" id="numero" name="numero" required>

      <label for="pais">País:</label>
      <input type="text" id="pais" name="pais" required>

      <label for="ciudad">Ciudad:</label>
      <input type="text" id="ciudad" name="ciudad" required>

      <label for="codigo_postal">Código Postal:</label>
      <input type="text" id="codigo_postal" name="codigo_postal" required>

      <label for="correo">Correo Electrónico:</label>
      <input type="email" id="correo" name="correo" required>

      <label for="telefono">Número de Teléfono:</label>
      <input type="tel" id="telefono" name="telefono" required>

      <!-- Campos opcionales -->
      <label for="piso_departamento">Piso/Departamento (Opcional):</label>
      <input type="text" id="piso_departamento" name="piso_departamento">

      <label for="provincia">Provincia/Municipio (Opcional):</label>
      <input type="text" id="provincia" name="provincia">

      <button type="submit" id="submit-button" style="display:none;">Guardar Datos de Envío</button>
    </form>
  </div>

  <!-- Resumen de Pago -->
  <div class="summary-container">
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "e_commerce";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error){
      die("Error de conexión: " . $conn->connect_error);
    }

    $id = 5;
    $sql = "SELECT precio, descuento FROM zapatillas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($precio, $descuento);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    $precio_descuento = $precio - ($precio * ($descuento / 100));
    $envio = 10;
    $precio_final = $precio_descuento + $envio;

    $precio_descuento = number_format($precio_descuento, 2, '.', '');
    $precio_final = number_format($precio_final, 2, '.', '');
    ?>
    <div class="summary-title">Resumen de Pago</div>
    <div class="summary-item">
      <span>Precio del Producto:</span>
      <span>$<?php echo number_format($precio, 2); ?></span>
    </div>
    <div class="summary-item">
      <span>Descuento:</span>
      <span><?php echo $descuento; ?>%</span>
    </div>
    <div class="summary-item">
      <span>Precio con Descuento:</span>
      <span>$<?php echo $precio_descuento; ?></span>
    </div>
    <div class="summary-item">
      <span>Costo de Envío:</span>
      <span>$<?php echo $envio; ?></span>
    </div>
    <div class="summary-total">
      Total a Pagar: <span>$<?php echo $precio_final; ?></span>
    </div>
    <div id="paypal-button-container"></div>
  </div>
</div>

<!-- Detalles de Envío -->
<div class="shipping-details">
  <h3>Detalles de Envío</h3>
  <p>La fecha estimada de llegada de tu paquete es entre el <strong>24 y 25 de noviembre</strong>.</p>
</div>

<!-- Modal de Confirmación -->
<div id="confirmation-modal" class="modal">
  <div class="modal-content">
    <h2>¡Compra Confirmada!</h2>
    <p>Tu pago ha sido procesado correctamente.</p>
    <button onclick="window.location.href='index.html'">Ir a la tienda</button>
    <button onclick="window.location.href='detalles_envio.html'">Ver detalles de envío</button>
  </div>
</div>

<script>
  document.getElementById('shipping-form').addEventListener('submit', function(event) {
    let fields = ['nombre', 'apellidos', 'calle', 'numero', 'pais', 'ciudad', 'codigo_postal', 'correo', 'telefono'];
    let isValid = true;

    fields.forEach(function(field) {
      let input = document.getElementById(field);
      if (!input.value) {
        isValid = false;
        input.style.borderColor = 'red';
      } else {
        input.style.borderColor = '';
      }
    });

    if (!isValid) {
      alert('Por favor, completa todos los campos obligatorios.');
      event.preventDefault();
    }
  });

  paypal.Buttons({
    style: {
      color: 'blue',
      shape: 'pill',
      label: 'pay'
    },
    createOrder: function(data, actions) {
      return actions.order.create({
        purchase_units: [{
          amount: {
            value: "<?php echo $precio_final; ?>"
          }
        }]
      });
    },
    onApprove: function(data, actions) {
      actions.order.capture().then(function(details) {
        console.log(details);
        document.getElementById('confirmation-modal').style.display = 'flex';
      });
    },
    onCancel: function(data) {
      alert("Pago Cancelado");
    }
  }).render('#paypal-button-container');
</script>

</body>
</html>
