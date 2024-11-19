<?php

require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

$lista_carrito = array();

if ($productos != null) {
    foreach ($productos as $clave => $cantidad) {
        $sql = $con->prepare("SELECT id, nombre, precio, descuento, $cantidad AS cantidad FROM zapatillas WHERE id=? AND activo=1");
        $sql->execute([$clave]);
        $lista_carrito[] = $sql->fetch(PDO::FETCH_ASSOC);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table {
            margin-top: 20px;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
        }

        .btn-warning {
            color: #000;
        }

        .btn-primary {
            width: 100%;
            font-size: 1.2rem;
        }

        .total {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>

<body>

    <main>
        <div class="container">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($lista_carrito == null) { ?>
                            <tr>
                                <td colspan="5" class="text-center"><b>Lista vacía</b></td>
                            </tr>
                        <?php } else {
                            $total = 0;
                            foreach ($lista_carrito as $producto) {
                                $_id = $producto['id'];
                                $nombre = $producto['nombre'];
                                $precio = $producto['precio'];
                                $descuento = $producto['descuento'];
                                $cantidad = $producto['cantidad'];
                                $precio_desc = $precio - (($precio * $descuento) / 100);
                                $subtotal = $cantidad * $precio_desc;
                                $total += $subtotal;
                        ?>
                                <tr>
                                    <td><?php echo $nombre; ?></td>
                                    <td><?php echo MONEDA . number_format($precio_desc, 2, '.', ','); ?></td>
                                    <td>
                                        <input type="number" min="1" max="10" step="1" value="<?php echo $cantidad ?>" size="5" id="cantidad_<?php echo $_id; ?>" onchange="actualizaCantidad(this.value, <?php echo $_id; ?>)">
                                    </td>
                                    <td>
                                        <div id="subtotal_<?php echo $_id; ?>" name="subtotal[]"><?php echo MONEDA . number_format($subtotal, 2, '.', ','); ?></div>
                                    </td>
                                    <td>
                                        <a href="#" id="eliminar" class="btn btn-warning btn-sm" data-bs-id="<?php echo $_id; ?>" data-bs-toggle="modal" data-bs-target="#eliminaModal">Eliminar</a>
                                    </td>
                                </tr>
                        <?php } ?>
                            <tr>
                                <td colspan="3"></td>
                                <td class="total" colspan="2">
                                    Total: <span id="total"><?php echo MONEDA . number_format($total, 2, '.', ','); ?></span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-md-5 offset-md-7 d-grid gap-2">
                <button class="btn btn-primary btn-lg" onclick="window.location.href='index.php'">Realizar pago</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminaModalLabel">Alerta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">¿Desea eliminar el producto de la lista?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button id="btn-elimina" type="button" class="btn btn-danger" onclick="eliminar()">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let eliminaModal = document.getElementById('eliminaModal');
        eliminaModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget; // Botón que abrió el modal
            let id = button.getAttribute('data-bs-id'); // Obtiene el ID del producto
            let buttonElimina = document.getElementById('btn-elimina'); // Botón dentro del modal
            buttonElimina.value = id; // Asigna el ID al botón dentro del modal
        });

        function actualizaCantidad(cantidad, id) {
            let url = 'clases/actualizar_carrito.php';
            let formData = new FormData();
            formData.append('action', 'agregar');
            formData.append('id', id);
            formData.append('cantidad', cantidad);

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        let divsubtotal = document.getElementById('subtotal_' + id);
                        divsubtotal.innerHTML = data.sub;

                        let totalElement = document.getElementById('total');
                        totalElement.innerHTML = data.total;
                    }
                })
                .catch(error => console.error('Error al actualizar la cantidad:', error));
        }

        function eliminar() {
            let botonElimina = document.getElementById('btn-elimina'); // Botón dentro del modal
            let id = botonElimina.value; // Obtén el ID del producto

            let url = 'clases/actualizar_carrito.php';
            let formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id', id);

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        let fila = document.querySelector(`#subtotal_${id}`).closest('tr');
                        fila.remove();

                        let totalElement = document.getElementById('total');
                        totalElement.innerHTML = data.total;

                        let modal = bootstrap.Modal.getInstance(eliminaModal); // Instancia del modal
                        modal.hide(); // Cierra el modal
                    }
                })
                .catch(error => console.error('Error al eliminar el producto:', error));
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
