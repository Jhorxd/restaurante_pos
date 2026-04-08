<?php
class Categoria_model extends CI_Model {

    public function get_todas($solo_activas = true) {
        if ($solo_activas) {
            $this->db->where('estado', 1);
        }
        $this->db->order_by('orden', 'ASC');
        return $this->db->get('categorias')->result();
    }

    public function get_por_id($id) {
        return $this->db->get_where('categorias', ['id' => $id])->row();
    }

    public function insertar($data) {
        $this->load->helper('url');
        // Generar slug si no existe
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = url_title($data['nombre'], 'dash', TRUE);
        }
        return $this->db->insert('categorias', $data);
    }

    public function actualizar($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('categorias', $data);
    }

    public function eliminar($id) {
        // Antes de eliminar, movemos productos a NULL o a otra categoría
        // Para este POS, simplemente desvinculamos
        $this->db->where('id_categoria', $id)->update('productos', ['id_categoria' => null]);
        return $this->db->delete('categorias', ['id' => $id]);
    }
}
