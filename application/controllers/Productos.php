<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('id')) {
            redirect('login');
        }
        $this->load->model('Producto_model');
        $this->load->model('Receta_model');
        $this->load->model('Categoria_model');
    }

    // Listado de productos de LA SUCURSAL actual (pestañas por línea)
    public function index() {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $tab = $this->input->get('tab') ?: 'todos';
        
        $data['tab_activa'] = $tab;
        $data['categorias'] = $this->Categoria_model->get_todas();
        $data['conteos']    = $this->Producto_model->conteos_por_tipo($id_sucursal);
        $data['productos']  = $this->Producto_model->get_productos_by_sucursal($id_sucursal, $tab);

        // Variable global para estadísticas del dashboard
        $data['productos_dashboard'] = $this->Producto_model->get_productos_by_sucursal($id_sucursal, 'todos');

        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('productos/index', $data);
        $this->load->view('layouts/footer');
    }

    // Vista del formulario de nuevo producto
    public function nuevo() {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $data['licores']             = $this->Producto_model->get_licores_sucursal($id_sucursal);
        $data['insumos_disponibles'] = $this->Receta_model->get_insumos_disponibles($id_sucursal);
        $data['categorias']          = $this->Categoria_model->get_todas();
        
        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('productos/nuevo', $data);
        $this->load->view('layouts/footer');
    }

    // Guardar el producto en la base de datos
public function guardar() {
    $id_sucursal = $this->session->userdata('id_sucursal');
    $tipo = $this->input->post('tipo_linea') ?: 'produccion';
    if (!in_array($tipo, ['produccion', 'licores', 'cocteles'], true)) {
        $tipo = 'produccion';
    }

    // tiene_receta: viene del checkbox (solo tipo produccion puede ser compuesto)
    $tiene_receta = ($this->input->post('tiene_receta') == '1') ? 1 : 0;
    $id_categoria = $this->input->post('id_categoria');
    
    // Obtener comportamiento de la categoría
    $cat = $this->Categoria_model->get_por_id($id_categoria);
    $tipo = $cat ? $cat->comportamiento : 'produccion';

    $data = [
        'codigo_barras' => $this->input->post('codigo_barras'),
        'nombre'        => $this->input->post('nombre'),
        'descripcion'   => $this->input->post('descripcion'),
        'categoria'     => $this->input->post('categoria'), // mantiene campo texto para subcat
        'id_categoria'  => $id_categoria,
        'tipo_linea'    => $tipo,
        'tiene_receta'  => $tiene_receta,
        'precio_compra' => $this->input->post('precio_compra'),
        'precio_venta'  => $this->input->post('precio_venta'),
        'stock_minimo'  => $this->input->post('stock_minimo'),
        'id_sucursal'   => $id_sucursal,
        'version'       => time()
    ];

    $data['id_licor_base'] = null;
    $data['repositorio_botellas'] = 0;
    $data['max_repositorio_botellas'] = 5;
    $data['ventas_por_botella'] = 10;
    $data['contador_ventas_coctel'] = 0;

    if ($tipo === 'cocteles') {
        $id_licor = (int) $this->input->post('id_licor_base');
        if ($id_licor < 1) {
            $this->session->set_flashdata('error', 'Un cóctel debe tener una botella de licor base.');
            redirect('productos/nuevo');
            return;
        }
        $licor = $this->Producto_model->get_producto($id_licor, $id_sucursal);
        if (!$licor || $licor->tipo_linea !== 'licores') {
            $this->session->set_flashdata('error', 'Selecciona un producto válido de la línea LICORES.');
            redirect('productos/nuevo');
            return;
        }
        $maxr = (int) $this->input->post('max_repositorio_botellas');
        $data['max_repositorio_botellas'] = ($maxr >= 1 && $maxr <= 50) ? $maxr : 5;
        $vp = (int) $this->input->post('ventas_por_botella');
        $data['ventas_por_botella'] = ($vp >= 1 && $vp <= 1000) ? $vp : 10;
        $data['id_licor_base'] = $id_licor;
        $rep_ini = (float) $this->input->post('repositorio_inicial');
        if ($rep_ini < 0) {
            $rep_ini = 0;
        }
        if ($rep_ini > $data['max_repositorio_botellas']) {
            $rep_ini = (float) $data['max_repositorio_botellas'];
        }
        $data['stock'] = 0;
        $data['repositorio_botellas'] = 0;
        $rep_a_cargar = $rep_ini;
    } else {
        $data['stock'] = $this->input->post('stock');
    }

    // 1. Insertar primero para obtener el ID
    $id_producto = $this->Producto_model->insertar($data);

    if ($id_producto) {
        if ($tipo === 'cocteles' && isset($rep_a_cargar) && $rep_a_cargar > 0) {
            $licor = $this->Producto_model->get_producto((int) $data['id_licor_base'], $id_sucursal);
            if (!$licor || (float) $licor->stock < $rep_a_cargar) {
                $this->Producto_model->eliminar($id_producto, $id_sucursal);
                $this->session->set_flashdata('error', 'No hay suficientes botellas de licor en almacén para el repositorio inicial.');
                redirect('productos/nuevo');
                return;
            }
            $this->Producto_model->actualizar($id_producto, $id_sucursal, [
                'repositorio_botellas' => $rep_a_cargar
            ]);
            $this->Producto_model->actualizar($licor->id, $id_sucursal, [
                'stock' => (float) $licor->stock - $rep_a_cargar
            ]);
        }
        if (!empty($_FILES['imagen']['name'])) {
            
            $path = './uploads/productos/';
            if (!is_dir($path)) { mkdir($path, 0777, true); }

            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = $id_producto . "." . $extension; 

            $config['upload_path']   = $path;
            $config['file_name']     = $nombre_archivo;
            $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
            $config['overwrite']     = TRUE;
            $config['max_size']      = '10240'; 

            $this->load->library('upload');
            $this->upload->initialize($config);

            if ($this->upload->do_upload('imagen')) {
                $uploadData = $this->upload->data();
                $full_path = $uploadData['full_path'];

                $this->load->library('image_lib');

                // --- 2. CORREGIR ROTACIÓN SEGÚN EXIF ---
                $exif = @exif_read_data($full_path);
                if($exif && isset($exif['Orientation'])) {
                    $ort = $exif['Orientation'];
                    $degrees = 0;

                    if($ort == 6) $degrees = 270; // 90 grados a la derecha
                    if($ort == 8) $degrees = 90;  // 90 grados a la izquierda
                    if($ort == 3) $degrees = 180; // Invertido

                    if($degrees != 0) {
                        $config_r['image_library'] = 'gd2';
                        $config_r['source_image']  = $full_path;
                        $config_r['rotation_angle'] = $degrees;
                        $this->image_lib->initialize($config_r);
                        $this->image_lib->rotate();
                        $this->image_lib->clear();
                    }
                }

                // --- 3. COMPRESIÓN Y REDIMENSIÓN ---
                $config_img['image_library']  = 'gd2';
                $config_img['source_image']   = $full_path;
                $config_img['maintain_ratio'] = TRUE;
                $config_img['width']          = 800;
                $config_img['height']         = 800;
                $config_img['quality']        = '60%'; 

                $this->image_lib->initialize($config_img);
                $this->image_lib->resize();
                $this->image_lib->clear();

                // Actualizamos el registro con el nombre final de la imagen y versión real
                $this->Producto_model->actualizar($id_producto, $id_sucursal, [
                    'imagen' => $uploadData['file_name'],
                    'version' => time()
                ]);
            } else {
                $error_upload = $this->upload->display_errors('', '');
                $this->session->set_flashdata('warning', 'Producto guardado, pero la imagen falló: ' . $error_upload);
            }
        }

        $this->session->set_flashdata('success', 'Producto registrado correctamente');

        // Si es producto compuesto, guardar los ingredientes enviados desde el form
        if ($tiene_receta && $id_producto) {
            $insumos_ids      = $this->input->post('insumos_id')      ?? [];
            $insumos_cant     = $this->input->post('insumos_cantidad') ?? [];
            $insumos_unidades = $this->input->post('insumos_unidad')   ?? [];

            if (!empty($insumos_ids) && is_array($insumos_ids)) {
                foreach ($insumos_ids as $i => $id_insumo) {
                    $id_insumo = (int) $id_insumo;
                    $cantidad  = isset($insumos_cant[$i]) ? (float) $insumos_cant[$i] : 0;
                    $unidad    = isset($insumos_unidades[$i]) ? trim($insumos_unidades[$i]) : null;
                    if ($id_insumo > 0 && $cantidad > 0) {
                        $this->Receta_model->agregar_insumo($id_producto, $id_insumo, $cantidad, $unidad ?: null);
                    }
                }
            }
        }
    } else {
        $this->session->set_flashdata('error', 'Error al registrar el producto');
    }

    redirect('productos');
}

    // Editar producto (solo si pertenece a su sucursal)
    public function editar($id) {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $data['p'] = $this->Producto_model->get_producto($id, $id_sucursal);

        if (!$data['p']) {
            show_404();
        }

        $data['licores']             = $this->Producto_model->get_licores_sucursal($id_sucursal);
        $data['receta']              = $this->Receta_model->get_receta($id);
        $data['insumos_disponibles'] = $this->Receta_model->get_insumos_disponibles($id_sucursal, $id);
        $data['categorias']          = $this->Categoria_model->get_todas();

        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('productos/editar', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * Pasa botellas del licor al repositorio de bar del cóctel (hasta el máximo).
     */
    public function reponer_coctel($id) {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $botellas = (float) $this->input->post('botellas');
        $this->load->model('Producto_model');
        $res = $this->Producto_model->reponer_repositorio_coctel((int) $id, $id_sucursal, $botellas);
        if ($res['ok']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('productos/editar/' . $id);
    }

    public function eliminar($id) {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $this->Producto_model->eliminar($id, $id_sucursal);
        redirect('productos');
    }
    
    public function actualizar($id = null) {
        if (!$id) {
            $id = $this->input->post('id');
        }
        
        if (!$id) {
            redirect('productos');
        }

        $id_sucursal = $this->session->userdata('id_sucursal');
        $actual = $this->Producto_model->get_producto($id, $id_sucursal);
        if (!$actual) {
            show_404();
        }

    $id_categoria = $this->input->post('id_categoria');
    $cat = $this->Categoria_model->get_por_id($id_categoria);
    $tipo = $cat ? $cat->comportamiento : ($actual->tipo_linea ?? 'produccion');

    $tiene_receta = ($this->input->post('tiene_receta') == '1') ? 1 : 0;

    $data = [
        'codigo_barras' => $this->input->post('codigo_barras'),
        'nombre'        => $this->input->post('nombre'),
        'descripcion'   => $this->input->post('descripcion'),
        'categoria'     => $this->input->post('categoria'),
        'id_categoria'  => $id_categoria,
        'tipo_linea'    => $tipo,
        'tiene_receta'  => $tiene_receta,
        'precio_compra' => $this->input->post('precio_compra'),
        'precio_venta'  => $this->input->post('precio_venta'),
        'stock_minimo'  => $this->input->post('stock_minimo')
    ];

    if ($tipo === 'cocteles') {
        $id_licor = (int) $this->input->post('id_licor_base');
        if ($id_licor < 1) {
            $this->session->set_flashdata('error', 'Debes elegir la botella de licor base.');
            redirect('productos/editar/' . $id);
            return;
        }
        $licor = $this->Producto_model->get_producto($id_licor, $id_sucursal);
        if (!$licor || $licor->tipo_linea !== 'licores') {
            $this->session->set_flashdata('error', 'Licor base no válido.');
            redirect('productos/editar/' . $id);
            return;
        }
        $maxr = (int) $this->input->post('max_repositorio_botellas');
        $data['max_repositorio_botellas'] = ($maxr >= 1 && $maxr <= 50) ? $maxr : 5;
        $vp = (int) $this->input->post('ventas_por_botella');
        $data['ventas_por_botella'] = ($vp >= 1 && $vp <= 1000) ? $vp : 10;
        $data['id_licor_base'] = $id_licor;
        $data['stock'] = 0;
        if ((float) $actual->repositorio_botellas > $data['max_repositorio_botellas']) {
            $data['repositorio_botellas'] = $data['max_repositorio_botellas'];
        }
    } else {
        $data['id_licor_base'] = null;
        $data['repositorio_botellas'] = 0;
        $data['max_repositorio_botellas'] = 5;
        $data['ventas_por_botella'] = 10;
        $data['contador_ventas_coctel'] = 0;
        $data['stock'] = $this->input->post('stock');
    }

    if (!empty($_FILES['imagen']['name'])) {
        $path = './uploads/productos/';
        if (!is_dir($path)) { mkdir($path, 0777, true); }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $id . "." . $extension; 

        $config['upload_path']   = $path;
        $config['file_name']     = $nombre_archivo;
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['overwrite']     = TRUE;
        $config['max_size']      = '10240'; 

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('imagen')) {
            $uploadData = $this->upload->data();
            $full_path = $uploadData['full_path'];

            // --- CORRECCIÓN DE ROTACIÓN MANUAL ---
            $this->load->library('image_lib');
            
            // Leemos los datos EXIF para ver si la foto está rotada
            $exif = @exif_read_data($full_path);
            if($exif && isset($exif['Orientation'])) {
                $ort = $exif['Orientation'];
                $degrees = 0;

                if($ort == 6) $degrees = 270; // Rotar 90 a la derecha
                if($ort == 8) $degrees = 90;  // Rotar 90 a la izquierda
                if($ort == 3) $degrees = 180; // Rotar 180

                if($degrees != 0) {
                    $config_r['image_library'] = 'gd2';
                    $config_r['source_image']  = $full_path;
                    $config_r['rotation_angle'] = $degrees;
                    $this->image_lib->initialize($config_r);
                    $this->image_lib->rotate();
                    $this->image_lib->clear();
                }
            }

            // --- COMPRESIÓN Y REDIMENSIÓN ---
            $config_img['image_library']  = 'gd2';
            $config_img['source_image']   = $full_path;
            $config_img['maintain_ratio'] = TRUE;
            $config_img['width']          = 800;
            $config_img['height']         = 800;
            $config_img['quality']        = '60%'; 

            $this->image_lib->initialize($config_img);
            $this->image_lib->resize();
            $this->image_lib->clear();

            $data['imagen'] = $uploadData['file_name'];
            $data['version'] = time(); 
        } else {
            $this->session->set_flashdata('error', 'Error al subir imagen: ' . $this->upload->display_errors('', ''));
        }
    }

        // Sincronización inteligente de receta: Si envió insumos, forzamos tiene_receta = 1
        $insumos_id = $this->input->post('insumos_id');
        if (!empty($insumos_id) && count($insumos_id) > 0) {
            $data['tiene_receta'] = 1;
        }

        if ($this->Producto_model->actualizar($id, $id_sucursal, $data)) {
            
            // --- SINCRONIZACIÓN DE RECETA ---
            // Usamos $data['tiene_receta'] recien validado
            if ($data['tiene_receta'] == 1) {
                // 1. Borrar receta anterior
                $this->Receta_model->eliminar_receta($id);

                // 2. Insertar nueva lista
                $insumos_cantidad = $this->input->post('insumos_cantidad');
                $insumos_unidad   = $this->input->post('insumos_unidad');

                if (!empty($insumos_id)) {
                    foreach ($insumos_id as $key => $iid) {
                        $cant = (float) $insumos_cantidad[$key];
                        $uni  = $insumos_unidad[$key];
                        if ($iid > 0 && $cant > 0) {
                            $this->Receta_model->agregar_insumo($id, $iid, $cant, $uni ?: null);
                        }
                    }
                }
            } else {
                // Si se desactivó la receta o no tiene insumos, limpiamos
                $this->Receta_model->eliminar_receta($id);
            }

        $this->session->set_flashdata('success', 'Producto actualizado correctamente');
    } else {
        $this->session->set_flashdata('error', 'Error al guardar en la base de datos');
    }

    redirect('productos');
}

    // ─── GESTIÓN DE RECETAS ─────────────────────────────────────────────────

    /**
     * Agrega un insumo a la receta de un producto (via form POST desde editar).
     */
    public function agregar_insumo_receta($id_producto)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $prod = $this->Producto_model->get_producto($id_producto, $id_sucursal);
        if (!$prod || !$prod->tiene_receta) {
            $this->session->set_flashdata('error', 'Producto no válido o no tiene receta activada.');
            redirect('productos/editar/' . $id_producto);
            return;
        }

        $id_insumo = (int) $this->input->post('id_insumo');
        $cantidad  = (float) $this->input->post('cantidad');
        $unidad    = trim($this->input->post('unidad'));

        if ($id_insumo < 1 || $cantidad <= 0) {
            $this->session->set_flashdata('error', 'Selecciona un insumo válido y una cantidad mayor a 0.');
            redirect('productos/editar/' . $id_producto);
            return;
        }

        $insumo = $this->Producto_model->get_producto($id_insumo, $id_sucursal);
        if (!$insumo) {
            $this->session->set_flashdata('error', 'El insumo seleccionado no existe en esta sucursal.');
            redirect('productos/editar/' . $id_producto);
            return;
        }

        $this->Receta_model->agregar_insumo($id_producto, $id_insumo, $cantidad, $unidad ?: null);
        $this->session->set_flashdata('success', 'Insumo agregado a la receta correctamente.');
        redirect('productos/editar/' . $id_producto . '#receta');
    }

    /**
     * Elimina un ingrediente específico de la receta.
     */
    public function eliminar_insumo_receta($id_fila)
    {
        $fila = $this->db->get_where('producto_receta', ['id' => (int) $id_fila])->row();
        $id_producto = $fila ? $fila->id_producto : 0;
        $this->Receta_model->eliminar_insumo($id_fila);
        $this->session->set_flashdata('success', 'Insumo eliminado de la receta.');
        redirect('productos/editar/' . $id_producto . '#receta');
    }
    // --- GESTIÓN DE CATEGORÍAS (AJAX) ---

    public function get_categorias_json() {
        echo json_encode($this->Categoria_model->get_todas(false));
    }

    public function guardar_categoria_ajax() {
        $id = $this->input->post('id');
        $data = [
            'nombre'         => $this->input->post('nombre'),
            'icono'          => $this->input->post('icono') ?: 'fa-tag',
            'color'          => $this->input->post('color') ?: 'blue',
            'comportamiento' => $this->input->post('comportamiento') ?: 'produccion',
            'orden'          => (int) $this->input->post('orden')
        ];

        // Validamos que el ID sea numérico y mayor a 0 para considerar edición
        if (is_numeric($id) && (int)$id > 0) {
            $res = $this->Categoria_model->actualizar($id, $data);
        } else {
            $res = $this->Categoria_model->insertar($data);
        }
        echo json_encode(['success' => (bool)$res]);
    }

    public function eliminar_categoria_ajax() {
        $id = $this->input->post('id');
        // No permitir borrar las 3 primeras por seguridad (son las base)
        if (in_array((int)$id, [1, 2, 3])) {
            echo json_encode(['success' => false, 'error' => 'No se pueden eliminar las categorías base del sistema.']);
            return;
        }
        $res = $this->Categoria_model->eliminar($id);
        echo json_encode(['success' => (bool)$res]);
    }
}