<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'config/database.php';
    $db = new Database();
    $con = $db->conectar();

    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $calle = $_POST['calle'];
    $numero = $_POST['numero'];
    $pais = $_POST['pais'];
    $ciudad = $_POST['ciudad'];
    $codigo_postal = $_POST['codigo_postal'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $piso_departamento = $_POST['piso_departamento'] ?? null;
    $provincia = $_POST['provincia'] ?? null;

    $sql = $con->prepare("INSERT INTO envios (nombre, apellidos, calle, numero, pais, ciudad, codigo_postal, correo, telefono, piso_departamento, provincia)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($sql->execute([$nombre, $apellidos, $calle, $numero, $pais, $ciudad, $codigo_postal, $correo, $telefono, $piso_departamento, $provincia])) {
        $id_envio = $con->lastInsertId();
        echo json_encode(['success' => true, 'id_envio' => $id_envio]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
