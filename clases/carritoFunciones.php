<?php
// Conexión a la base de datos
require 'config/config.php';
require 'config/database.php';

class CarritoFunciones
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
    }

    public function guardarCarritoEnDB($idCliente, $carrito)
    {
        $contenidoJSON = json_encode($carrito); 
        $fechaActual = date('Y-m-d H:i:s');

        $sql = $this->db->prepare("SELECT id FROM carrito WHERE id_cliente = ?");
        $sql->execute([$idCliente]);

        if ($sql->rowCount() > 0) {
            // Actualizar carrito existente
            $sql = $this->db->prepare("UPDATE carrito SET contenido = ?, fecha_actualizacion = ? WHERE id_cliente = ?");
            return $sql->execute([$contenidoJSON, $fechaActual, $idCliente]);
        } else {
            // Insertar nuevo carrito
            $sql = $this->db->prepare("INSERT INTO carrito (id_cliente, contenido, fecha_actualizacion) VALUES (?, ?, ?)");
            return $sql->execute([$idCliente, $contenidoJSON, $fechaActual]);
        }
    }

    public function cargarCarritoDesdeDB($idCliente)
    {
        $sql = $this->db->prepare("SELECT contenido FROM carrito WHERE id_cliente = ?");
        $sql->execute([$idCliente]);

        if ($sql->rowCount() > 0) {
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            return json_decode($row['contenido'], true);
        }

        return [];
    }

    public function limpiarCarrito($idCliente)
    {
        $sql = $this->db->prepare("DELETE FROM carrito WHERE id_cliente = ?");
        return $sql->execute([$idCliente]);
    }
}

?>
