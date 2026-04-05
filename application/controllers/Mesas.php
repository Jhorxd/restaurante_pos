<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mesas extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('id')) {
            redirect('login');
        }
        $this->load->model('Mesa_model');
    }

    private function _es_admin() {
        return $this->session->userdata('rol') === 'admin';
    }

    /** Mapa / listado del salón */
    public function index() {
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $data['mesas'] = $this->Mesa_model->get_by_sucursal($id_sucursal, false);
        $data['es_admin'] = $this->_es_admin();
        $data['titulo'] = 'Mesas y salón';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('mesas/index', $data);
        $this->load->view('layouts/footer');
    }

    public function nuevo() {
        if (!$this->_es_admin()) {
            show_error('No autorizado', 403);
        }
        $data['titulo'] = 'Nueva mesa';
        $data['m'] = null;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('mesas/form', $data);
        $this->load->view('layouts/footer');
    }

    public function editar($id) {
        if (!$this->_es_admin()) {
            show_error('No autorizado', 403);
        }
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $m = $this->Mesa_model->get($id, $id_sucursal);
        if (!$m) {
            show_404();
        }
        $data['titulo'] = 'Editar mesa';
        $data['m'] = $m;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('mesas/form', $data);
        $this->load->view('layouts/footer');
    }

    public function guardar() {
        if (!$this->_es_admin()) {
            show_error('No autorizado', 403);
        }
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $post = $this->input->post();
        $id = !empty($post['id']) ? (int) $post['id'] : 0;

        $codigo = isset($post['codigo']) ? trim($post['codigo']) : '';
        if ($codigo === '') {
            $this->session->set_flashdata('error', 'El código de mesa es obligatorio.');
            redirect($id ? 'mesas/editar/' . $id : 'mesas/nuevo');
            return;
        }
        if ($this->Mesa_model->codigo_existe($id_sucursal, $codigo, $id ?: null)) {
            $this->session->set_flashdata('error', 'Ya existe una mesa con ese código en esta sucursal.');
            redirect($id ? 'mesas/editar/' . $id : 'mesas/nuevo');
            return;
        }

        $row = [
            'id_sucursal' => $id_sucursal,
            'codigo' => $codigo,
            'nombre' => isset($post['nombre']) ? trim($post['nombre']) : $codigo,
            'capacidad' => max(1, (int) ($post['capacidad'] ?? 4)),
            'zona' => isset($post['zona']) ? trim($post['zona']) : null,
            'pos_orden' => (int) ($post['pos_orden'] ?? 0),
            'pos_x' => (int) ($post['pos_x'] ?? 0),
            'pos_y' => (int) ($post['pos_y'] ?? 0),
            'notas' => isset($post['notas']) ? trim($post['notas']) : null,
            'activo' => !empty($post['activo']) ? 1 : 0,
        ];

        if ($id) {
            $ex = $this->Mesa_model->get($id, $id_sucursal);
            if (!$ex) {
                show_404();
            }
            if (!empty($post['estado']) && in_array($post['estado'], ['libre', 'ocupada', 'reservada', 'limpieza'], true)) {
                $row['estado'] = $post['estado'];
            }
            $this->Mesa_model->actualizar($id, $id_sucursal, $row);
            $this->session->set_flashdata('msg', 'Mesa actualizada.');
        } else {
            $row['estado'] = 'libre';
            $this->Mesa_model->insertar($row);
            $this->session->set_flashdata('msg', 'Mesa creada.');
        }
        redirect('mesas');
    }

    public function eliminar($id) {
        if (!$this->_es_admin()) {
            show_error('No autorizado', 403);
        }
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        if ($this->Mesa_model->eliminar($id, $id_sucursal)) {
            $this->session->set_flashdata('msg', 'Mesa eliminada.');
        } else {
            $this->session->set_flashdata('error', 'No se puede eliminar: la mesa debe estar libre o en limpieza.');
        }
        redirect('mesas');
    }

    public function cambiar_estado() {
        $id = (int) $this->input->post('id');
        $estado = $this->input->post('estado');
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        
        $mesa = $this->Mesa_model->get($id, $id_sucursal);
        if ($mesa && $mesa->id_venta_activa > 0 && $estado === 'libre') {
            $this->session->set_flashdata('error', 'No se puede liberar esta mesa, tiene un pedido pendiente en caja.');
            redirect('mesas');
            return;
        }

        if (!in_array($estado, ['libre', 'ocupada', 'reservada', 'limpieza'], true)) {
            $this->session->set_flashdata('error', 'Estado no válido.');
            redirect('mesas');
            return;
        }
        if ($this->Mesa_model->set_estado($id, $id_sucursal, $estado)) {
            $this->session->set_flashdata('msg', 'Estado actualizado.');
        } else {
            $this->session->set_flashdata('error', 'No se pudo actualizar la mesa.');
        }
        redirect('mesas');
    }

    public function trasladar() {
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $origen = (int) $this->input->post('id_origen');
        $destino = (int) $this->input->post('id_destino');
        $res = $this->Mesa_model->trasladar($origen, $destino, $id_sucursal);
        if ($res['ok']) {
            $this->session->set_flashdata('msg', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('mesas');
    }

    /** Mover posición en el plano (orden / coordenadas) */
    public function mover() {
        if (!$this->_es_admin()) {
            show_error('No autorizado', 403);
        }
        $id = (int) $this->input->post('id');
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $m = $this->Mesa_model->get($id, $id_sucursal);
        if (!$m) {
            $this->session->set_flashdata('error', 'Mesa no encontrada.');
            redirect('mesas');
            return;
        }
        $this->Mesa_model->actualizar($id, $id_sucursal, [
            'pos_x' => (int) $this->input->post('pos_x'),
            'pos_y' => (int) $this->input->post('pos_y'),
            'pos_orden' => (int) $this->input->post('pos_orden'),
        ]);
        $this->session->set_flashdata('msg', 'Posición actualizada.');
        redirect('mesas');
    }

    /** JSON para el POS */
    public function lista_pos() {
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($this->Mesa_model->listar_para_pos($id_sucursal)));
    }
}
