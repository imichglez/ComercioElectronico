<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $calle = $_POST['calle'];
    $numero = $_POST['numero'];
    $pais = $_POST['pais'];
    $ciudad = $_POST['ciudad'];
    $codigo_postal = $_POST['codigo_postal'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];

    $sql = "INSERT INTO envios (nombre, apellidos, calle, numero, pais, ciudad, codigo_postal, correo, telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $nombre, $apellidos, $calle, $numero, $pais, $ciudad, $codigo_postal, $correo, $telefono);

    if ($stmt->execute()) {
        echo "Datos de envío guardados correctamente.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
