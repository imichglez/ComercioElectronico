<?php
require 'config/database.php'; // Asegúrate de que la ruta es correcta

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

    // Crear instancia de la clase Database y conectar
    $db = new Database();
    $conn = $db->conectar();

    try {
        // Preparar la consulta
        $sql = "INSERT INTO envios (nombre, apellidos, calle, numero, pais, ciudad, codigo_postal, correo, telefono) 
                VALUES (:nombre, :apellidos, :calle, :numero, :pais, :ciudad, :codigo_postal, :correo, :telefono)";
        $stmt = $conn->prepare($sql);

        // Vincular los parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->bindParam(':calle', $calle);
        $stmt->bindParam(':numero', $numero);
        $stmt->bindParam(':pais', $pais);
        $stmt->bindParam(':ciudad', $ciudad);
        $stmt->bindParam(':codigo_postal', $codigo_postal);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':telefono', $telefono);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "Datos de envío guardados correctamente.";
        } else {
            echo "Error al guardar los datos.";
        }
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
    }
}
?>
