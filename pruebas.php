<div class="modal fade" id="modalBoleta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #bfe3bf;">
                <h5 class="modal-title text-center" style="flex: 1;">ORDEN EMITIDA CORRECTAMENTE</h5>
            </div>
            <div class="modal-body">
                <p>ORDEN DE COMPRA NUMERO : <?php echo $id_orden; ?></p>

                <div class="card-body">
                    <?php
                    $promocionesArray = explode(', ', $promociones);
                    $total = 0;
                    foreach ($promocionesArray as $id_promocion) {
                        $sentenciaSQL_promocion = $conexion->prepare("SELECT * FROM promocion WHERE id_promocion = :id_promocion");
                        $sentenciaSQL_promocion->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
                        $sentenciaSQL_promocion->execute();
                        $promocion = $sentenciaSQL_promocion->fetch(PDO::FETCH_ASSOC);

                        if ($promocion) {
                            $total += $promocion['precio'];
                            echo '<div class="d-flex justify-content-between">';
                            echo '<li><span>' . strtoupper($promocion['nombre_promocion']) . '</span></li>';
                            echo '<span>$' . $promocion['precio'] . '</span>';
                            if (!empty($comentarios)) {
                                echo '<span>' . htmlspecialchars($comentarios) . '</span>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                    <hr>

                    <li><span><strong>TOTAL</strong></span></li>
                    <span><strong>$<?php echo $total; ?></strong></span>
                    <br>
                    <hr>
                    <!-- Sección Cliente -->
                    <h5 class="text-center" style="flex: 1;">Información del Cliente</h5>
                    <br>
                    <div class="d-flex justify-content-between">
                        <li><span>Teléfono:</span></li>
                        <span><?php echo !empty($telefono) ? htmlspecialchars($telefono) : 'Sin datos'; ?></span>
                    
                
                        <li><span>Nombre:</span></li>
                        <span><?php echo !empty($nombre_cliente) ? htmlspecialchars($nombre_cliente) : 'Sin datos'; ?></span>
                 
                        <li><span>Dirección:</span></li>
                        <?php echo !empty($direccion_cliente) ? htmlspecialchars($direccion_cliente) : 'Sin datos'; ?></span>
                  
                        <li><span>Referencia:</span></li>
                        <span><?php echo !empty($referencia_cliente) ? htmlspecialchars($referencia_cliente) : 'Sin datos'; ?></span>
                    
                        <li><span>Medio de Pago:</span></li>
                        <span><?php echo $metodo_pago_texto; ?></span>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>