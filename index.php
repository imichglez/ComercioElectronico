<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://www.paypal.com/sdk/js?client-id=AR8xACZzL1pYEHoqxITx1YkVoDPQjE3GNOh6T0lV0CPNkT-YtNO6BgowOsDHKiaZqghMKk3I2ckApAdq"></script>
</head>
<body>

<!-- Formulario de Envío -->
<form action="guardar_envio.php" method="POST">
  <h2>Datos de Envío</h2>
  
  <label for="nombre">Nombre:</label>
  <input type="text" id="nombre" name="nombre" required><br>

  <label for="apellidos">Apellidos:</label>
  <input type="text" id="apellidos" name="apellidos" required><br>

  <label for="calle">Calle:</label>
  <input type="text" id="calle" name="calle" required><br>

  <label for="numero">Número de la Casa:</label>
  <input type="text" id="numero" name="numero" required><br>

  <label for="pais">País:</label>
  <input type="text" id="pais" name="pais" required><br>

  <label for="ciudad">Ciudad:</label>
  <input type="text" id="ciudad" name="ciudad" required><br>

  <label for="codigo_postal">Código Postal:</label>
  <input type="text" id="codigo_postal" name="codigo_postal" required><br>

  <label for="correo">Correo Electrónico:</label>
  <input type="email" id="correo" name="correo" required><br>

  <label for="telefono">Número de Teléfono:</label>
  <input type="tel" id="telefono" name="telefono" required><br>

  <button type="submit">Guardar Datos de Envío</button>
</form>


<hr>

<!-- Resumen de Pago -->
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

if ($precio === null) {
    die("Error: No se pudo recuperar el precio del producto.");
}

$precio_descuento = $precio;
if ($descuento > 0) {
    $precio_descuento = $precio - ($precio * ($descuento / 100));
}

$envio = 10;
$precio_final = $precio_descuento + $envio;

$precio_descuento = number_format($precio_descuento, 2, '.', '');
$precio_final = number_format($precio_final, 2, '.', '');
?>

<h3>Resumen de Pago</h3>
<p>Precio del Producto: $<?php echo number_format($precio, 2); ?></p>
<p>Descuento: <?php echo $descuento; ?>%</p>
<p>Precio con Descuento: $<?php echo $precio_descuento; ?></p>
<p>Costo de Envío: $<?php echo $envio; ?></p>
<p><strong>Total a Pagar: $<?php echo $precio_final; ?></strong></p>

<hr>

<!-- Botón de Pago -->
<div id="paypal-button-container"></div>

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
                        value: "<?php echo $precio_final; ?>"
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            actions.order.capture().then(function(detalles) {
                console.log(detalles);
                alert("Pago realizado correctamente");
            });
        },
        onCancel: function(data) {
            alert("Pago Cancelado");
        }
    }).render('#paypal-button-container');
</script>

</body>
</html>
