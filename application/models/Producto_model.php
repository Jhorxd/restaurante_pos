<?php
class Producto_model extends CI_Model {

    /**
     * @param string|null $filtro_tipo 'todos' | 'produccion' | 'licores' | 'cocteles'
     */
    public function get_productos_by_sucursal($id_sucursal, $filtro_tipo = 'todos') {
        $this->db->select('p.*, c.nombre as categoria_nombre, c.icono as categoria_icono, c.color as categoria_color, c.comportamiento as behavior');
        $this->db->from('productos p');
        $this->db->join('categorias c', 'c.id = p.id_categoria', 'left');
        $this->db->where('p.id_sucursal', $id_sucursal);

        if ($filtro_tipo && $filtro_tipo !== 'todos') {
            if (is_numeric($filtro_tipo)) {
                $this->db->where('p.id_categoria', (int) $filtro_tipo);
            } else {
                // Compatibilidad por si se pasa el slug/tipo_linea
                $this->db->where('p.tipo_linea', $filtro_tipo);
            }
        }

        $this->db->order_by('p.nombre', 'ASC');
        return $this->db->get()->result();
    }

    /** Cantidad de productos por línea (para badges en pestañas). */
    public function conteos_por_tipo($id_sucursal) {
        $this->load->model('Categoria_model');
        $categorias = $this->Categoria_model->get_todas();
        
        $out = ['todos' => (int) $this->db->where('id_sucursal', $id_sucursal)->count_all_results('productos')];
        
        foreach ($categorias as $cat) {
            $out[$cat->id] = (int) $this->db->where('id_sucursal', $id_sucursal)
                                            ->where('id_categoria', $cat->id)
                                            ->count_all_results('productos');
        }
        return $out;
    }

    public function insertar($data) {
        if ($this->db->insert('productos', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function get_producto($id, $id_sucursal) {
        return $this->db->get_where('productos', [
            'id' => $id,
            'id_sucursal' => $id_sucursal
        ])->row();
    }

    public function eliminar($id, $id_sucursal) {
        return $this->db->delete('productos', [
            'id' => $id,
            'id_sucursal' => $id_sucursal
        ]);
    }

    public function actualizar($id, $id_sucursal, $data) {
        $this->db->where('id', $id);
        $this->db->where('id_sucursal', $id_sucursal);
        return $this->db->update('productos', $data);
    }

    /** Botellas enteras (línea licores) para enlazar cócteles */
    public function get_licores_sucursal($id_sucursal) {
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->where('tipo_linea', 'licores');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get('productos')->result();
    }

    /**
     * Máximo de cócteles vendibles: R botellas en bar × V tragos por botella, menos tragos ya contados (C).
     * No usar solo floor((C+N)/V)-floor(C/V) <= R: eso permite ~V-1 ventas de más (ej. R=5,V=5 daba 29 en vez de 25).
     */
    public function max_cocteles_vendibles($coctel, $licor) {
        if (!$coctel || $coctel->tipo_linea !== 'cocteles' || !$licor || $licor->tipo_linea !== 'licores') {
            return 0;
        }
        $V = max(1, (int) $coctel->ventas_por_botella);
        $R = max(0, (int) floor((float) $coctel->repositorio_botellas));
        $C = (int) $coctel->contador_ventas_coctel;
        $C = (($C % $V) + $V) % $V;

        return max(0, $R * $V - $C);
    }

    /**
     * Enriquece filas POS: stock mostrable y meta para cócteles.
     */
    private function _enriquecer_fila_pos(array &$row, $id_sucursal) {
        $tipo = isset($row['tipo_linea']) ? $row['tipo_linea'] : 'produccion';
        $row['tipo_linea'] = $tipo;
        $row['stock_num'] = isset($row['stock']) ? (float) $row['stock'] : 0;

        if ($tipo === 'cocteles') {
            $licor_id = isset($row['id_licor_base']) ? (int) $row['id_licor_base'] : 0;
            $licor_stock = isset($row['licor_stock']) ? (float) $row['licor_stock'] : 0;
            if ($licor_id <= 0) {
                $row['stock'] = 0;
                $row['pos_stock_label'] = 'Sin licor base';
                $row['max_vender'] = 0;
                return;
            }
            $coctel = (object) $row;
            $licor = (object) [
                'tipo_linea' => 'licores',
                'stock' => $licor_stock,
                'id_sucursal' => $id_sucursal,
            ];
            $max = $this->max_cocteles_vendibles($coctel, $licor);
            $row['stock'] = $max;
            $row['max_vender'] = $max;
            $rep = isset($row['repositorio_botellas']) ? (float) $row['repositorio_botellas'] : 0;
            $maxr = isset($row['max_repositorio_botellas']) ? (int) $row['max_repositorio_botellas'] : 5;
            $row['pos_stock_label'] = 'Bar ' . $rep . '/' . $maxr . ' · Licor ' . (int) $licor_stock;
        } else {
            $row['max_vender'] = $row['stock_num'];
            $row['pos_stock_label'] = null;
        }
    }

    public function get_productos_pos($busqueda = '', $id_sucursal = null) {
        $this->db->select('p.id, p.codigo_barras, p.nombre, p.precio_venta, p.stock, p.imagen, p.version, p.tipo_linea, p.id_licor_base, p.repositorio_botellas, p.max_repositorio_botellas, p.ventas_por_botella, p.contador_ventas_coctel, pl.stock AS licor_stock', false);
        $this->db->from('productos p');
        $this->db->join('productos pl', 'pl.id = p.id_licor_base AND pl.id_sucursal = p.id_sucursal', 'left');

        if ($id_sucursal !== null && $id_sucursal !== '') {
            $this->db->where('p.id_sucursal', $id_sucursal);
        }

        if (!empty($busqueda)) {
            $this->db->group_start();
            $this->db->like('p.nombre', $busqueda);
            $this->db->or_like('p.codigo_barras', $busqueda);
            $this->db->group_end();
        }

        $this->db->order_by('p.nombre', 'ASC');
        $query = $this->db->get();
        $rows = $query->result_array();
        foreach ($rows as &$row) {
            $this->_enriquecer_fila_pos($row, $id_sucursal);
        }
        return $rows;
    }

    /**
     * Cada venta de cóctel: avanza contador y descuenta solo del repositorio de bar al completar cada V ventas.
     * El stock del licor en almacén no se toca aquí (baja solo al reponer bar desde edición del cóctel).
     */
    public function aplicar_salida_coctel($coctel, $licor, $cantidad, $id_sucursal) {
        $V = max(1, (int) $coctel->ventas_por_botella);
        $N = (int) $cantidad;
        if ($N < 1) {
            return ['ok' => false, 'message' => 'Cantidad inválida', 'botellas_consumidas' => 0];
        }
        $C = (int) $coctel->contador_ventas_coctel;
        $C = (($C % $V) + $V) % $V;
        $comp = (int) (floor(($C + $N) / $V) - floor($C / $V));
        $newC = ($C + $N) % $V;

        $maxV = $this->max_cocteles_vendibles($coctel, $licor);
        if ($N > $maxV) {
            return ['ok' => false, 'message' => 'No hay suficientes botellas en el repositorio de bar para esta cantidad de cócteles. Repone el bar desde la edición del producto.', 'botellas_consumidas' => 0];
        }

        $R = max(0, (int) floor((float) $coctel->repositorio_botellas));
        $newRepo = (float) ($R - $comp);

        $this->db->where('id', $coctel->id)->where('id_sucursal', $id_sucursal);
        $this->db->update('productos', [
            'contador_ventas_coctel' => $newC,
            'repositorio_botellas' => $newRepo,
        ]);

        return ['ok' => true, 'message' => '', 'botellas_consumidas' => $comp, 'nuevo_contador' => $newC, 'nuevo_repo' => $newRepo];
    }

    /**
     * Pasa botellas del almacén (licor) al bar (repositorio del cóctel), hasta el máximo configurado.
     */
    public function reponer_repositorio_coctel($id_coctel, $id_sucursal, $botellas_solicitadas) {
        $coctel = $this->get_producto($id_coctel, $id_sucursal);
        if (!$coctel || $coctel->tipo_linea !== 'cocteles' || empty($coctel->id_licor_base)) {
            return ['ok' => false, 'message' => 'Cóctel o licor base no válido.'];
        }
        $licor = $this->get_producto($coctel->id_licor_base, $id_sucursal);
        if (!$licor || $licor->tipo_linea !== 'licores') {
            return ['ok' => false, 'message' => 'El producto licor base no existe o no es línea LICORES.'];
        }
        $maxR = max(1, (int) $coctel->max_repositorio_botellas);
        $repo = (float) $coctel->repositorio_botellas;
        $espacio = $maxR - $repo;
        if ($espacio <= 0) {
            return ['ok' => false, 'message' => 'El repositorio de bar ya está lleno (' . $maxR . ' botellas).'];
        }
        $pedido = (float) $botellas_solicitadas;
        if ($pedido < 1) {
            return ['ok' => false, 'message' => 'Indica cuántas botellas reponer.'];
        }
        $mover = min($espacio, $pedido, (float) $licor->stock);
        if ($mover < 1) {
            return ['ok' => false, 'message' => 'No hay botellas de licor disponibles en almacén.'];
        }
        $this->db->where('id', $coctel->id)->where('id_sucursal', $id_sucursal);
        $this->db->update('productos', ['repositorio_botellas' => $repo + $mover]);
        $this->db->where('id', $licor->id)->where('id_sucursal', $id_sucursal);
        $this->db->update('productos', ['stock' => (float) $licor->stock - $mover]);
        return ['ok' => true, 'message' => 'Se repusieron ' . (int) $mover . ' botella(s) al bar.', 'movidas' => $mover];
    }
}
