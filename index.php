//Formas de Pago y Envío

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://www.paypal.com/sdk/js?client-id=AR8xACZzL1pYEHoqxITx1YkVoDPQjE3GNOh6T0lV0CPNkT-YtNO6BgowOsDHKiaZqghMKk3I2ckApAdq"></script>
</head>
<body>

<div id="paypal-button-container"></div>

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

$precio_final = $precio;
if ($descuento > 0) {
    $precio_final = $precio - ($precio * ($descuento / 100));
}

$precio_final = number_format($precio_final, 2, '.', '');
?>

<script>
    paypal.Buttons({
        style:{
            color: 'blue',
            shape: 'pill',
            label: 'pay'
        },
        createOrder: function(data, actions){
            return actions.order.create({
                purchase_units: [{
                  amount: {
                    value: "<?php echo $precio_final; ?>"
                  }
                }]
            });
        },

        onApprove: function(data, actions){
          actions.order.capture().then(function (detalles){
              console.log(detalles);
              alert("Pago realizado correctamente");
          });
        },

        onCancel: function(data){
          alert("Pago Cancelado");
        }
    }).render('#paypal-button-container');
</script>

</body>
</html>