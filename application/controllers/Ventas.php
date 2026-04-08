<?php
class Ventas extends CI_Controller {

    public function pos() {
        // Carga la interfaz del POS
        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('ventas/pos');
        $this->load->view('layouts/footer');
    }

    // Endpoint para el buscador en tiempo real
    public function buscar_productos_ajax() {
        $buscar = $this->input->get('term');
        $this->load->model('Producto_model');
        $id_sucursal = $this->session->userdata('id_sucursal');
        $productos = $this->Producto_model->get_productos_pos($buscar, $id_sucursal);
        echo json_encode($productos);
    }

    public function guardar()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['carrito'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Datos inválidos']));
        }

        $carrito     = $input['carrito'];
        $total       = $input['total'];
        $metodo_pago = $input['metodo_pago'] ?? 'efectivo';
        $monto_rec   = $input['monto_recibido'] ?? 0;
        $vuelto      = $input['vuelto'] ?? 0;
        
        $accion      = $input['accion'] ?? 'cobrar'; // puede ser 'cobrar' o 'pedido'
        $id_venta_existente = !empty($input['id_venta']) ? (int) $input['id_venta'] : 0;

        $id_sucursal = $this->session->userdata('id_sucursal');
        $id_usuario  = $this->session->userdata('id');

        // Verificar caja activa (siempre se necesita la caja asignada en la venta)
        $caja = $this->db->get_where('cajas', [
            'id_sucursal' => $id_sucursal,
            'estado'      => 'Abierta'
        ])->row();

        if (!$caja) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'No hay caja activa en esta sucursal']));
        }

        $id_mesa = 0;
        $liberar_mesa = false;
        $tiene_col_mesa = $this->db->field_exists('id_mesa', 'ventas');

        $es_append_directo = false;

        if ($tiene_col_mesa) {
            $id_mesa = isset($input['id_mesa']) ? (int) $input['id_mesa'] : 0;
            $liberar_mesa = !empty($input['liberar_mesa']);
            if ($id_mesa > 0) {
                // Verificar mesa
                $this->load->model('Mesa_model');
                $mesa = $this->Mesa_model->get($id_mesa, $id_sucursal);
                if (!$mesa || !(int) $mesa->activo) {
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(['success' => false, 'message' => 'La mesa seleccionada no es válida o está inactiva.']));
                }

                // Lógica crucial para "aumentar" la cuenta en lugar de "actualizarla/sobrescribirla"
                // Si la mesa ya está ocupada (tiene venta), pero el frontend envió id_venta = 0 (carrito limpio),
                // significa que simplemente seleccionaron la mesa del combo-box y quieren sumarle productos.
                if ($mesa->id_venta_activa > 0 && $id_venta_existente == 0 && $accion === 'pedido') {
                    $id_venta_existente = (int) $mesa->id_venta_activa;
                    $es_append_directo = true;
                }
            }
        }

        $this->db->trans_start();
        $this->load->model('Producto_model');

        $estado_venta = ($accion === 'pedido') ? 'pendiente' : 'pagada';
        $id_venta_final = 0;

        if ($id_venta_existente > 0) {
            // Actualizar Venta o Pedido existente
            $venta_previa = $this->db->get_where('ventas', ['id' => $id_venta_existente, 'id_sucursal' => $id_sucursal])->row();
            if (!$venta_previa || $venta_previa->estado !== 'pendiente') {
                $this->db->trans_rollback();
                return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'El pedido ya fue cobrado o no existe.']));
            }
            
            if ($es_append_directo) {
                // Modo Aumentar cuenta ciega: Sumar el total nuevo al total actual
                $update_data = [
                    'total' => $venta_previa->total + $total,
                    'estado' => $estado_venta
                ];
                $this->db->where('id', $id_venta_existente)->update('ventas', $update_data);
                // NO borramos venta_detalles.
                $id_venta_final = $id_venta_existente;
            } else {
                // Modo Modificar/Cobrar Normal (el carrito enviado es el equivalente exacto a la base de datos)
                $update_data = [
                    'total' => $total,
                    'estado' => $estado_venta
                ];
                
                if ($accion === 'cobrar') {
                    $update_data['metodo_pago'] = $metodo_pago;
                    $update_data['monto_recibido'] = $monto_rec;
                    $update_data['vuelto'] = $vuelto;
                    $update_data['fecha_registro'] = date('Y-m-d H:i:s'); // renovamos fecha al momento de cobro
                }
                
                if ($tiene_col_mesa && $id_mesa > 0) {
                    $update_data['id_mesa'] = $id_mesa;
                }
                
                $this->db->where('id', $id_venta_existente)->update('ventas', $update_data);
                
                // Eliminar detalles antiguos para insertar los nuevos íntegramente
                $this->db->where('id_venta', $id_venta_existente)->delete('venta_detalles');
                $id_venta_final = $id_venta_existente;
            }
            
        } else {
            // Crear Nueva Venta o Pedido
            $venta_row = [
                'id_sucursal'    => $id_sucursal,
                'id_usuario'     => $id_usuario,
                'id_caja'        => $caja->id,
                'total'          => $total,
                'estado'         => $estado_venta,
                'metodo_pago'    => $metodo_pago,
                'monto_recibido' => $monto_rec,
                'vuelto'         => $vuelto,
                'fecha_registro' => date('Y-m-d H:i:s')
            ];
            if ($tiene_col_mesa && $id_mesa > 0) {
                $venta_row['id_mesa'] = $id_mesa;
            }
            $this->db->insert('ventas', $venta_row);
            $id_venta_final = $this->db->insert_id();
        }

        // Insertar Detalles y aplicar Stock SOLO si se está Cobrando
        foreach ($carrito as $item) {
            $id_producto = $item['id'];
            $cantidad    = (float) $item['cantidad'];
            $precio      = (float) $item['precio'];

            $prod = $this->Producto_model->get_producto($id_producto, $id_sucursal);
            if (!$prod) {
                $this->db->trans_rollback();
                return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'Producto no encontrado en esta sucursal.']));
            }

            if ($es_append_directo) {
                // Verificar si ya existe en la orden actual para solo sumarle cantidad
                $det_existente = $this->db->get_where('venta_detalles', [
                    'id_venta' => $id_venta_final,
                    'id_producto' => $id_producto
                ])->row();

                if ($det_existente) {
                    $this->db->where('id', $det_existente->id)->update('venta_detalles', [
                        'cantidad' => $det_existente->cantidad + $cantidad,
                        'subtotal' => $det_existente->subtotal + ($cantidad * $precio)
                    ]);
                } else {
                    $this->db->insert('venta_detalles', [
                        'id_venta'        => $id_venta_final,
                        'id_producto'     => $id_producto,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precio,
                        'subtotal'        => $cantidad * $precio
                    ]);
                }
            } else {
                $this->db->insert('venta_detalles', [
                    'id_venta'        => $id_venta_final,
                    'id_producto'     => $id_producto,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal'        => $cantidad * $precio
                ]);
            }

            // Descontar inventario (Kardex / Productos) SOLAMENTE al pagar
            if ($accion === 'cobrar') {
                $tipo = isset($prod->tipo_linea) ? $prod->tipo_linea : 'produccion';
                
                if ($tipo === 'cocteles') {
                    // ── Lógica especial para cócteles (sin cambios) ──────────────────
                    $licor = $this->Producto_model->get_producto($prod->id_licor_base, $id_sucursal);
                    if (!$licor) {
                        $this->db->trans_rollback();
                        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'El cóctel no tiene licor base válido.']));
                    }
                    $res = $this->Producto_model->aplicar_salida_coctel($prod, $licor, $cantidad, $id_sucursal);
                    if (!$res['ok']) {
                        $this->db->trans_rollback();
                        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => $res['message']]));
                    }
                    $repo_res = isset($res['nuevo_repo']) ? $res['nuevo_repo'] : $prod->repositorio_botellas;
                    $this->db->insert('kardex', [
                        'id_sucursal'      => $id_sucursal,
                        'id_producto'      => $id_producto,
                        'tipo_movimiento'  => 'Salida',
                        'motivo'           => 'Venta',
                        'doc_tipo'         => 'Venta',
                        'doc_id'           => $id_venta_final,
                        'cantidad'         => $cantidad,
                        'stock_resultante' => $repo_res,
                        'fecha'            => date('Y-m-d H:i:s')
                    ]);

                } elseif (!empty($prod->tiene_receta)) {
                    // ── Producto COMPUESTO: descuenta sus insumos de la receta ────────
                    $this->load->model('Receta_model');
                    $res_receta = $this->Receta_model->descontar_insumos(
                        $id_producto,
                        $cantidad,
                        $id_sucursal,
                        $id_venta_final,
                        $prod->nombre
                    );
                    if (!$res_receta['ok']) {
                        $this->db->trans_rollback();
                        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => $res_receta['message']]));
                    }

                    // --- NUEVO: También descontar stock del producto final ---
                    $this->db->query(
                        "UPDATE productos SET stock = stock - ? WHERE id = ? AND id_sucursal = ?",
                        [$cantidad, $id_producto, $id_sucursal]
                    );
                    $stock_actual = $this->db->query(
                        "SELECT stock FROM productos WHERE id = ? AND id_sucursal = ?",
                        [$id_producto, $id_sucursal]
                    )->row()->stock;

                    // Registrar en kardex la salida del producto compuesto
                    $this->db->insert('kardex', [
                        'id_sucursal'      => $id_sucursal,
                        'id_producto'      => $id_producto,
                        'tipo_movimiento'  => 'Salida',
                        'motivo'           => 'Venta',
                        'doc_tipo'         => 'Venta',
                        'doc_id'           => $id_venta_final,
                        'cantidad'         => $cantidad,
                        'stock_resultante' => $stock_actual,
                        'nota'             => 'Producto compuesto — Ins. descontados',
                        'fecha'            => date('Y-m-d H:i:s')
                    ]);

                } else {
                    // ── Producto SIMPLE: descuenta su propio stock (comportamiento original) ──
                    if ((float) $prod->stock < $cantidad) {
                        $this->db->trans_rollback();
                        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'Stock insuficiente para: ' . $prod->nombre]));
                    }
                    $this->db->query(
                        "UPDATE productos SET stock = stock - ? WHERE id = ? AND id_sucursal = ?",
                        [$cantidad, $id_producto, $id_sucursal]
                    );
                    $stock_actual = $this->db->query(
                        "SELECT stock FROM productos WHERE id = ? AND id_sucursal = ?",
                        [$id_producto, $id_sucursal]
                    )->row()->stock;

                    $this->db->insert('kardex', [
                        'id_sucursal'      => $id_sucursal,
                        'id_producto'      => $id_producto,
                        'tipo_movimiento'  => 'Salida',
                        'motivo'           => 'Venta',
                        'doc_tipo'         => 'Venta',
                        'doc_id'           => $id_venta_final,
                        'cantidad'         => $cantidad,
                        'stock_resultante' => $stock_actual,
                        'fecha'            => date('Y-m-d H:i:s')
                    ]);
                }
            } // Fin if cobrar
        } // Fin foreach carrito

        // Actualizar el estado de la Mesa
        if ($tiene_col_mesa && $id_mesa > 0) {
            if ($accion === 'pedido') {
                // Dejar la mesa ocupada con cuenta activa
                $this->db->where('id', $id_mesa)->update('mesas', [
                    'estado' => 'ocupada',
                    'id_venta_activa' => $id_venta_final
                ]);
            } else {
                // Cobrar: desvincular id_venta_activa y liberar u ocupar sin cuenta
                $nuevo_estado = $liberar_mesa ? 'libre' : 'ocupada';
                $this->db->where('id', $id_mesa)->update('mesas', [
                    'estado' => $nuevo_estado,
                    'id_venta_activa' => NULL
                ]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Error al guardar.']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'id_venta' => $id_venta_final]));
    }


    public function ticket($id_venta)
    {
        $venta = $this->db->query("
            SELECT v.*, u.nombre as cajero
            FROM ventas v
            JOIN usuarios u ON u.id = v.id_usuario
            WHERE v.id = ?
        ", [$id_venta])->row();

        if (!$venta) show_404();

        $detalles = $this->db->query("
            SELECT vd.*, p.nombre, p.codigo_barras
            FROM venta_detalles vd
            JOIN productos p ON p.id = vd.id_producto
            WHERE vd.id_venta = ?
        ", [$id_venta])->result();

        $sucursal = $this->db->query(
            "SELECT * FROM sucursales WHERE id = ?",
            [$venta->id_sucursal]
        )->row();

        $html = $this->_html_ticket($venta, $detalles, $sucursal, false);

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => [80, 200],
            'margin_top'    => 4,
            'margin_bottom' => 4,
            'margin_left'   => 4,
            'margin_right'  => 4,
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output('ticket_' . $id_venta . '.pdf', 'I');
    }

    public function pre_cuenta($id_venta)
    {
        $venta = $this->db->query("
            SELECT v.*, u.nombre as cajero
            FROM ventas v
            JOIN usuarios u ON u.id = v.id_usuario
            WHERE v.id = ?
        ", [$id_venta])->row();

        if (!$venta) show_404();

        $detalles = $this->db->query("
            SELECT vd.*, p.nombre, p.codigo_barras
            FROM venta_detalles vd
            JOIN productos p ON p.id = vd.id_producto
            WHERE vd.id_venta = ?
        ", [$id_venta])->result();

        $sucursal = $this->db->query(
            "SELECT * FROM sucursales WHERE id = ?",
            [$venta->id_sucursal]
        )->row();

        $html = $this->_html_ticket($venta, $detalles, $sucursal, true);

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => [80, 200],
            'margin_top'    => 4,
            'margin_bottom' => 4,
            'margin_left'   => 4,
            'margin_right'  => 4,
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output('pre_cuenta_' . $id_venta . '.pdf', 'I');
    }

    public function detalle_pendiente($id_venta)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $venta = $this->db->get_where('ventas', ['id' => $id_venta, 'id_sucursal' => $id_sucursal])->row();
        if (!$venta) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'Venta no encontrada']));
        }
        $detalles = $this->db->query("
            SELECT vd.*, p.nombre, p.id as id_producto, p.precio_venta as precio_base
            FROM venta_detalles vd
            JOIN productos p ON p.id = vd.id_producto
            WHERE vd.id_venta = ?
        ", [$id_venta])->result();
        
        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success' => true,
            'venta' => $venta,
            'detalles' => $detalles
        ]));
    }

    private function _html_ticket($venta, $detalles, $sucursal, $es_pre_cuenta = false)
    {
        $items_html = '';
        foreach ($detalles as $d) {
            $items_html .= '
            <tr>
                <td style="padding:2px 0; font-size:10px;">' . htmlspecialchars($d->nombre) . '</td>
                <td style="text-align:center; font-size:10px;">' . $d->cantidad . '</td>
                <td style="text-align:right; font-size:10px;">S/ ' . number_format($d->precio_unitario, 2) . '</td>
                <td style="text-align:right; font-size:10px;">S/ ' . number_format($d->subtotal, 2) . '</td>
            </tr>';
        }

        $vuelto_html = '';
        if (!$es_pre_cuenta) {
            if ($venta->metodo_pago === 'efectivo') {
                $vuelto_html = '
                <tr>
                    <td colspan="2" style="font-size:10px;">Recibido:</td>
                    <td colspan="2" style="text-align:right; font-size:10px;">S/ ' . number_format($venta->monto_recibido, 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size:10px;">Vuelto:</td>
                    <td colspan="2" style="text-align:right; font-size:10px;">S/ ' . number_format($venta->vuelto, 2) . '</td>
                </tr>';
            }
        }

        $nombre_sucursal = $sucursal ? htmlspecialchars($sucursal->nombre) : 'Sucursal';

        return '
        <style>
            body { font-family: monospace; font-size: 11px; color: #000; }
            .center { text-align: center; }
            .bold { font-weight: bold; }
            .line { border-top: 1px dashed #000; margin: 4px 0; }
            table { width: 100%; border-collapse: collapse; }
        </style>

        <div class="center bold" style="font-size:14px;">' . $nombre_sucursal . '</div>
        <div class="center" style="font-size:10px;">' . ($es_pre_cuenta ? 'PRE-CUENTA' : 'Boleta de Venta') . '</div>
        <div class="line"></div>

        <table>
            <tr>
                <td style="font-size:10px;">' . ($es_pre_cuenta ? 'Mesa / Orden N°:' : 'Ticket N°:') . '</td>
                <td style="text-align:right; font-size:10px; font-weight:bold;">#' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) . '</td>
            </tr>
            <tr>
                <td style="font-size:10px;">Fecha:</td>
                <td style="text-align:right; font-size:10px;">' . date('d/m/Y H:i', strtotime($venta->fecha_registro)) . '</td>
            </tr>
            <tr>
                <td style="font-size:10px;">' . ($es_pre_cuenta ? 'Mesero:' : 'Cajero:') . '</td>
                <td style="text-align:right; font-size:10px;">' . htmlspecialchars($venta->cajero) . '</td>
            </tr>
        </table>

        <div class="line"></div>

        <table>
            <thead>
                <tr style="border-bottom: 1px dashed #000;">
                    <th style="text-align:left; font-size:10px; padding-bottom:2px;">Producto</th>
                    <th style="text-align:center; font-size:10px;">Cant.</th>
                    <th style="text-align:right; font-size:10px;">P.Unit</th>
                    <th style="text-align:right; font-size:10px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>' . $items_html . '</tbody>
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <td colspan="2" style="font-size:13px; font-weight:bold;">TOTAL:</td>
                <td colspan="2" style="text-align:right; font-size:13px; font-weight:bold;">S/ ' . number_format($venta->total, 2) . '</td>
            </tr>
            ' . (!$es_pre_cuenta ? '
            <tr>
                <td colspan="2" style="font-size:10px;">Método pago:</td>
                <td colspan="2" style="text-align:right; font-size:10px; text-transform:uppercase;">' . $venta->metodo_pago . '</td>
            </tr>' : '') . '
            ' . $vuelto_html . '
        </table>

        ' . ($es_pre_cuenta ? '
        <div class="line"></div>
        <div class="center" style="font-size:10px; margin-top:6px; font-weight:bold;">ESTE NO ES UN COMPROBANTE DE PAGO</div>
        ' : '
        <div class="line"></div>
        <div class="center" style="font-size:10px; margin-top:6px;">¡Gracias por su compra!</div>
        <div class="center" style="font-size:9px;">Conserve su comprobante</div>
        ') . '
        ';
    }


    public function venta_index()
{
    $id_sucursal = $this->session->userdata('id_sucursal');

    // Traemos las ventas con info básica
    $sql = "
        SELECT v.id,
               v.fecha_registro,
               v.total,
               v.metodo_pago,
               u.nombre   AS cajero,
               m.codigo   AS mesa_codigo,
               m.nombre   AS mesa_nombre
        FROM ventas v
        JOIN usuarios u ON u.id = v.id_usuario
        LEFT JOIN mesas m ON m.id = v.id_mesa
        WHERE v.id_sucursal = ?
        ORDER BY v.fecha_registro DESC
    ";
    if (!$this->db->field_exists('id_mesa', 'ventas')) {
        $sql = "
            SELECT v.id,
                   v.fecha_registro,
                   v.total,
                   v.metodo_pago,
                   u.nombre   AS cajero,
                   NULL       AS mesa_codigo,
                   NULL       AS mesa_nombre
            FROM ventas v
            JOIN usuarios u ON u.id = v.id_usuario
            WHERE v.id_sucursal = ?
            ORDER BY v.fecha_registro DESC
        ";
    }
    $ventas = $this->db->query($sql, [$id_sucursal])->result();

    $data['ventas'] = $ventas;
    $data['titulo'] = 'Historial de Ventas';

    $this->load->view('layouts/header', $data);
    $this->load->view('layouts/sidebar');
    $this->load->view('ventas/venta_index', $data);
    $this->load->view('layouts/footer');
}


    /**
     * Devuelve (vía AJAX) los insumos descontados del kardex para una venta concreta.
     */
    public function detalle_insumos_venta($id_venta)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');

        // Productos compuestos de esa venta
        $compuestos = $this->db->query("
            SELECT vd.id_producto, vd.cantidad, p.nombre AS prod_compuesto
            FROM venta_detalles vd
            JOIN productos p ON p.id = vd.id_producto
            WHERE vd.id_venta = ? AND p.tiene_receta = 1
        ", [$id_venta])->result();

        // Insumos descontados en el kardex para esa venta
        $insumos = $this->db->query("
            SELECT k.id_producto, k.cantidad, k.stock_resultante, k.nota, p.nombre AS insumo_nombre
            FROM kardex k
            JOIN productos p ON p.id = k.id_producto
            WHERE k.doc_id = ? AND k.doc_tipo = 'Venta'
              AND k.id_sucursal = ?
              AND k.nota LIKE 'Insumo de:%'
            ORDER BY k.id ASC
        ", [$id_venta, $id_sucursal])->result();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => true,
                'compuestos' => $compuestos,
                'insumos'    => $insumos
            ]));
    }

}