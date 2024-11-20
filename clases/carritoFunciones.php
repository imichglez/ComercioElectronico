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

    /**
     * Guarda el carrito en la base de datos para un cliente.
     * 
     * @param int $idCliente ID del cliente.
     * @param array $carrito Contenido del carrito (array asociativo).
     * @return bool
     */
    public function guardarCarritoEnDB($idCliente, $carrito)
    {
        $contenidoJSON = json_encode($carrito); // Convertir carrito a JSON
        $fechaActual = date('Y-m-d H:i:s');

        // Verificar si el carrito ya existe
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

    /**
     * Carga el carrito desde la base de datos para un cliente.
     * 
     * @param int $idCliente ID del cliente.
     * @return array Contenido del carrito o un array vacío si no hay carrito.
     */
    public function cargarCarritoDesdeDB($idCliente)
    {
        $sql = $this->db->prepare("SELECT contenido FROM carrito WHERE id_cliente = ?");
        $sql->execute([$idCliente]);

        if ($sql->rowCount() > 0) {
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            return json_decode($row['contenido'], true); // Convertir JSON a array asociativo
        }

        return []; // Si no hay carrito, retornar un array vacío
    }

    /**
     * Limpia el carrito de un cliente después de una compra.
     * 
     * @param int $idCliente ID del cliente.
     * @return bool
     */
    public function limpiarCarrito($idCliente)
    {
        $sql = $this->db->prepare("DELETE FROM carrito WHERE id_cliente = ?");
        return $sql->execute([$idCliente]);
    }
}

?>
