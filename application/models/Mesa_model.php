<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mesa_model extends CI_Model {

    public function get_by_sucursal($id_sucursal, $solo_activas = false) {
        $this->db->where('id_sucursal', (int) $id_sucursal);
        if ($solo_activas) {
            $this->db->where('activo', 1);
        }
        $this->db->order_by('pos_orden', 'ASC');
        $this->db->order_by('codigo', 'ASC');
        return $this->db->get('mesas')->result();
    }

    public function get($id, $id_sucursal) {
        return $this->db->get_where('mesas', [
            'id' => (int) $id,
            'id_sucursal' => (int) $id_sucursal,
        ])->row();
    }

    public function insertar(array $data) {
        return $this->db->insert('mesas', $data) ? (int) $this->db->insert_id() : false;
    }

    public function actualizar($id, $id_sucursal, array $data) {
        $this->db->where('id', (int) $id);
        $this->db->where('id_sucursal', (int) $id_sucursal);
        return $this->db->update('mesas', $data);
    }

    /**
     * Solo si está libre o en limpieza y sin ventas futuras que la referencien (FK SET NULL al borrar mesa).
     */
    public function eliminar($id, $id_sucursal) {
        $m = $this->get($id, $id_sucursal);
        if (!$m) {
            return false;
        }
        if (!in_array($m->estado, ['libre', 'limpieza'], true)) {
            return false;
        }
        $this->db->where('id', (int) $id);
        $this->db->where('id_sucursal', (int) $id_sucursal);
        return $this->db->delete('mesas');
    }

    public function codigo_existe($id_sucursal, $codigo, $excluir_id = null) {
        $this->db->where('id_sucursal', (int) $id_sucursal);
        $this->db->where('codigo', $codigo);
        if ($excluir_id) {
            $this->db->where('id !=', (int) $excluir_id);
        }
        return $this->db->count_all_results('mesas') > 0;
    }

    public function set_estado($id, $id_sucursal, $estado) {
        $permitidos = ['libre', 'ocupada', 'reservada', 'limpieza'];
        if (!in_array($estado, $permitidos, true)) {
            return false;
        }
        return $this->actualizar($id, $id_sucursal, ['estado' => $estado]);
    }

    /**
     * Traslado de comensales: origen queda libre, destino ocupada.
     */
    public function trasladar($id_origen, $id_destino, $id_sucursal) {
        $o = $this->get($id_origen, $id_sucursal);
        $d = $this->get($id_destino, $id_sucursal);
        if (!$o || !$d || !$o->activo || !$d->activo) {
            return ['ok' => false, 'message' => 'Mesas no válidas o inactivas.'];
        }
        if ($id_origen === $id_destino) {
            return ['ok' => false, 'message' => 'Elige otra mesa de destino.'];
        }
        if (!in_array($o->estado, ['ocupada', 'reservada'], true)) {
            return ['ok' => false, 'message' => 'La mesa origen no está ocupada ni reservada.'];
        }
        if (!in_array($d->estado, ['libre', 'limpieza'], true)) {
            return ['ok' => false, 'message' => 'La mesa destino no está disponible (libre o limpieza).'];
        }
        $this->db->trans_start();
        $this->actualizar($id_origen, $id_sucursal, ['estado' => 'libre']);
        $this->actualizar($id_destino, $id_sucursal, ['estado' => 'ocupada']);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return ['ok' => false, 'message' => 'Error al trasladar.'];
        }
        return ['ok' => true, 'message' => 'Mesa actualizada.'];
    }

    /** Para JSON del POS: mesas activas con estado. */
    public function listar_para_pos($id_sucursal) {
        $rows = $this->get_by_sucursal($id_sucursal, true);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int) $r->id,
                'codigo' => $r->codigo,
                'nombre' => $r->nombre,
                'zona' => $r->zona,
                'capacidad' => (int) $r->capacidad,
                'estado' => $r->estado,
                'label' => $r->codigo . ' — ' . $r->nombre . ($r->zona ? ' (' . $r->zona . ')' : ''),
            ];
        }
        return $out;
    }
}
