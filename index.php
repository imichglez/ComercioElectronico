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
    </form>
  </div>

  <!-- Resumen de Pago -->
  <div class="summary-container">
    <?php
    session_start();
    require 'config/config.php';
    require 'config/database.php';

    $db = new Database();
    $con = $db->conectar();

    $productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

    $total = 0;
    $detalles = [];

    if ($productos != null) {
        foreach ($productos as $id => $cantidad) {
            $sql = $con->prepare("SELECT nombre, precio, descuento FROM zapatillas WHERE id = ?");
            $sql->execute([$id]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            $precio_descuento = $row['precio'] - ($row['precio'] * ($row['descuento'] / 100));
            $subtotal = $cantidad * $precio_descuento;
            $total += $subtotal;

            $detalles[] = [
                'id_producto' => $id,
                'nombre' => $row['nombre'],
                'precio' => $precio_descuento,
                'cantidad' => $cantidad
            ];
        }
    }

    $costo_envio = 10; // Costo de envío fijo
    $total += $costo_envio;
    $total = number_format($total, 2, '.', '');
    ?>
    <div class="summary-title">Resumen de Pago</div>
    <div class="summary-item">
      <span>Total de Productos:</span>
      <span>$<?php echo number_format($total - $costo_envio, 2); ?></span>
    </div>
    <div class="summary-item">
      <span>Costo de Envío:</span>
      <span>$<?php echo $costo_envio; ?></span>
    </div>
    <div class="summary-total">
      Total a Pagar: <span>$<?php echo $total; ?></span>
    </div>
    <div id="paypal-button-container"></div>
  </div>
</div>

<script>
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
            value: "<?php echo $total; ?>"
          }
        }]
      });
    },
    onApprove: function(data, actions) {
      return actions.order.capture().then(function(details) {
        // Registro de datos en el servidor
        const envioId = 1; // Cambia según el contexto, este debería ser dinámico.
        const transactionId = details.id;

        fetch('guardar_compra.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            id_envio: envioId,
            id_transaccion: transactionId
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Compra registrada con éxito.');
            window.location.href = 'confirmacion.php';
          } else {
            alert('Error al registrar la compra.');
          }
        });
      });
    },
    onCancel: function(data) {
      alert('Pago cancelado');
    }
  }).render('#paypal-button-container');
</script>

</body>
</html>
