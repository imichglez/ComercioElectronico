<?php
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
    $piso_departamento = $_POST['piso_departamento'];
    $provincia = $_POST['provincia'];

    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "e_commerce");

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $sql = "INSERT INTO envio (nombre, apellidos, calle, numero, pais, ciudad, codigo_postal, correo, telefono, piso_departamento, provincia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", $nombre, $apellidos, $calle, $numero, $pais, $ciudad, $codigo_postal, $correo, $telefono, $piso_departamento, $provincia);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
    $conn->close();
}
?>
