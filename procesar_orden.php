<?php
include("administrador/config/bd.php");

header('Content-Type: application/json');
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario

    $cliente_id = $_POST['id_cliente'] ?? null;
    $promociones = $_POST['promociones'] ?? '';
    $telefono = $_POST['telefono_cliente'] ?? '';
    $nombre_cliente = $_POST['nombre_cliente'] ?? '';
    $direccion_cliente = $_POST['direccion_cliente'] ?? '';
    $referencia_cliente = $_POST['referencia_cliente'] ?? '';
    $metodo_pago = $_POST['metodo_pago'] ?? null;
    $metodo_entrega = $_POST['metodo_entrega'] ?? null;
    $comentarios = $_POST['comentarios'] ?? '';
    $total = $_POST['total'] ?? 0;

    
    try {
        // SECCION CLIENTE
        $sentenciaSQL_cliente = $conexion->prepare("SELECT id_cliente FROM cliente WHERE telefono = :telefono");
        $sentenciaSQL_cliente->bindParam(':telefono', $telefono);
        $sentenciaSQL_cliente->execute();
        $cliente = $sentenciaSQL_cliente->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $cliente_id_cliente = $cliente['id_cliente'];
        } else {
            $sentenciaSQL_nuevo_cliente = $conexion->prepare("INSERT INTO cliente (id_cliente, nombre_cliente, telefono, direccion, referencia) 
                                                              VALUES (NULL, :nombre_cliente, :telefono, :direccion, :referencia)");
            $sentenciaSQL_nuevo_cliente->bindParam(':nombre_cliente', $nombre_cliente);
            $sentenciaSQL_nuevo_cliente->bindParam(':telefono', $telefono);
            $sentenciaSQL_nuevo_cliente->bindParam(':direccion', $direccion_cliente);
            $sentenciaSQL_nuevo_cliente->bindParam(':referencia', $referencia_cliente);
            $sentenciaSQL_nuevo_cliente->execute();

            $cliente_id_cliente = $conexion->lastInsertId();
        }

        // INSERCIÓN FINAL DE LA ORDEN
        $sentenciaSQL_orden = $conexion->prepare("INSERT INTO orden (id_orden, id_promocion, total, comentario, forma_pago, metodo_entrega, fecha, cliente_id_cliente) 
        VALUES (NULL, :id_promocion, :total, :comentario, :forma_pago, :metodo_entrega, current_timestamp(), :cliente_id_cliente)");
        $sentenciaSQL_orden->bindParam(':id_promocion', $promociones);
        $sentenciaSQL_orden->bindParam(':total', $total);
        $sentenciaSQL_orden->bindParam(':comentario', $comentarios);
        $sentenciaSQL_orden->bindParam(':forma_pago', $metodo_pago);
        $sentenciaSQL_orden->bindParam(':metodo_entrega', $metodo_entrega);
        $sentenciaSQL_orden->bindParam(':cliente_id_cliente', $cliente_id_cliente);
        $sentenciaSQL_orden->execute();


        // Obtener el ID de la orden insertada
        $id_orden = $conexion->lastInsertId();

        // Definir el método de pago
        $metodo_pago_descripcion = [
            0 => 'Efectivo',
            1 => 'Transferencia',
            2 => 'Débito / Prepago',
            3 => 'Crédito'
        ];

        $metodo_entrega_descripcion = [
            0 => 'Retiro',
            1 => 'Despacho'
        ];

        $metodo_pago_texto = $metodo_pago_descripcion[$metodo_pago] ?? 'Sin datos';
        $metodo_entrega_texto = $metodo_entrega_descripcion[$metodo_entrega] ?? 'Sin datos';

        // Construir el contenido del modal
        $output = '<div class="modal fade" id="modalBoleta" tabindex="-1" role="dialog">';
        $output .= '<div class="modal-dialog modal-dialog-centered">';
        $output .= '<div class="modal-content">';
        $output .= '<div class="modal-header" style="background-color: #ff8000; ">';  // Fondo naranja oscuro
        $output .= '<h5 class="modal-title text-center" style="flex: 1; color: white;">ORDEN DE COMPRA NUMERO  ' . htmlspecialchars($id_orden) . '</h5>';  // Letra blanca y margen 0
        $output .= '</div>';
        $output .= '<div class="modal-body">';
        $output .= '<div class="card-body">';
        $promocionesArray = explode(', ', $promociones);
        foreach ($promocionesArray as $id_promocion) {
            $sentenciaSQL_promocion = $conexion->prepare("SELECT * FROM promocion WHERE id_promocion = :id_promocion");
            $sentenciaSQL_promocion->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
            $sentenciaSQL_promocion->execute();
            $promocion = $sentenciaSQL_promocion->fetch(PDO::FETCH_ASSOC);

            if ($promocion) {
                $output .= '<div class="d-flex justify-content-between">';
                $output .= '<li><span>' . strtoupper(htmlspecialchars($promocion['nombre_promocion'])) . '</span></li>';
                $output .= '<span>$' . htmlspecialchars($promocion['precio']) . '</span>';
                $output .= '</div>';
                if (!empty($comentarios)) {
                    $output .= '<div class="d-flex justify-content-between">';
                    $output .= '<span style="color: blue;"><em>' . htmlspecialchars($comentarios) . '</em></span>';
                    $output .= '</div>';
                } else {
                    $output .= '<div class="d-flex justify-content-between">';
                    $output .= '<span><em>Sin comentarios adicionales.</em></span>';
                    $output .= '</div>';
                }

            }
        }
        $output .= '<div style="text-align: right;">';  // Alinea todo a la derecha
        $output .= '<span><strong>TOTAL</strong> <strong>$' . htmlspecialchars($total) . '</strong></span>';  // Juntos en una línea
        $output .= '</div>';

        // Sección Cliente
        $output .= '<hr>';
        $output .= '<h5 class="text-center" style="flex: 1;">Información del Cliente</h5>';
        $output .= '<br>';
        $output .= '<div class="d-flex justify-content-between">';
        $output .= '<li><span>Teléfono</span></li>';
        $output .= '<span>' . (!empty($telefono) ? htmlspecialchars($telefono) : 'Sin datos') . '</span>';
        $output .= '</div>';

        $output .= '<div class="d-flex justify-content-between">';
        $output .= '<li><span>Nombre</span></li>';
        $output .= '<span>' . (!empty($nombre_cliente) ? htmlspecialchars($nombre_cliente) : 'Sin datos') . '</span>';
        $output .= '</div>';

        $output .= '<div class="d-flex justify-content-between">';
        $output .= '<li><span>Medio de Pago</span></li>';
        $output .= '<span>' . htmlspecialchars($metodo_pago_texto) . '</span>';
        $output .= '</div>';

        // Sección Entrega
        $output .= '<hr>';
        $output .= '<h5 class="text-center" style="flex: 1;">Información Entrega</h5>';
        $output .= '<br>';

        $output .= '<div class="d-flex justify-content-between">';
        $output .= '<li><span>Método de entrega</span></li>';
        $output .= '<span style="font-weight: bold; text-transform: uppercase;">' . htmlspecialchars($metodo_entrega_texto) . '</span>';
        $output .= '</div>';

        if ($metodo_entrega == 1) {
            $output .= '<div class="d-flex justify-content-between">';
            $output .= '<li><span>Dirección</span></li>';
            $output .= '<span>' . (!empty($direccion_cliente) ? htmlspecialchars($direccion_cliente) : 'Sin datos') . '</span>';
            $output .= '</div>';

            $output .= '<div class="d-flex justify-content-between">';
            $output .= '<li><span>Referencia</span></li>';
            $output .= '<span>' . (!empty($referencia_cliente) ? htmlspecialchars($referencia_cliente) : 'Sin datos') . '</span>';
            $output .= '</div>';
        }

        $output .= '<br>';
        // Nueva sección antes del botón
        $output .= '<div class="text-center" style="margin-top: 10px;">';  // Centra el texto
        $output .= '<span><em>* Esta orden se emitirá en la impresora por defecto. *</em></span>';
        $output .= '</div>';

        $output .= '</div>';  // Cierra .card-body
        $output .= '</div>';  // Cierra .modal-body

        $output .= '<div class="modal-footer">';
        $output .= '<button type="submit" class="btn btn-danger" data-dismiss="modal">Cerrar</button>';
        $output .= '</div>';

        $output .= '</div>';  // Cierra .modal-content
        $output .= '</div>';  // Cierra .modal-dialog
        $output .= '</div>';  // Cierra .modal

        // Devolver el modal como respuesta JSON
        echo json_encode(['modal' => $output]);

    } catch (Exception $e) {
        // Devolver un error en caso de excepción
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}
?>