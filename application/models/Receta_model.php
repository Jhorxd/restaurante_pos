<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Receta_model
 * Gestiona los ingredientes (insumos) de productos compuestos.
 */
class Receta_model extends CI_Model {

    /**
     * Obtiene los ingredientes de un producto compuesto.
     * Incluye nombre e info del insumo para mostrar en la vista.
     */
    public function get_receta($id_producto)
    {
        return $this->db->query("
            SELECT pr.*,
                   p.nombre        AS insumo_nombre,
                   p.stock         AS insumo_stock,
                   p.codigo_barras AS insumo_codigo
            FROM producto_receta pr
            JOIN productos p ON p.id = pr.id_insumo
            WHERE pr.id_producto = ?
            ORDER BY pr.id ASC
        ", [(int) $id_producto])->result();
    }

    /**
     * Verifica si un producto tiene receta registrada.
     */
    public function tiene_receta($id_producto)
    {
        $count = $this->db->where('id_producto', (int) $id_producto)
                          ->count_all_results('producto_receta');
        return $count > 0;
    }

    /**
     * Agrega un ingrediente a la receta de un producto.
     */
    public function agregar_insumo($id_producto, $id_insumo, $cantidad, $unidad = null)
    {
        // Evitar duplicados del mismo insumo en la misma receta
        $existe = $this->db->get_where('producto_receta', [
            'id_producto' => (int) $id_producto,
            'id_insumo'   => (int) $id_insumo
        ])->row();

        if ($existe) {
            // Si ya existe, actualizar cantidad
            return $this->db->where('id', $existe->id)->update('producto_receta', [
                'cantidad' => (float) $cantidad,
                'unidad'   => $unidad
            ]);
        }

        return $this->db->insert('producto_receta', [
            'id_producto' => (int) $id_producto,
            'id_insumo'   => (int) $id_insumo,
            'cantidad'    => (float) $cantidad,
            'unidad'      => $unidad
        ]);
    }

    /**
     * Elimina un ingrediente específico de la receta (por su ID de fila).
     */
    public function eliminar_insumo($id)
    {
        return $this->db->delete('producto_receta', ['id' => (int) $id]);
    }

    /**
     * Elimina toda la receta de un producto.
     */
    public function eliminar_receta($id_producto)
    {
        return $this->db->delete('producto_receta', ['id_producto' => (int) $id_producto]);
    }

    /**
     * Descuenta el stock de cada insumo de la receta al vender un producto compuesto.
     * Inserta en kardex cada movimiento de salida por insumo.
     *
     * @param int   $id_producto  ID del producto compuesto vendido (ej: Salchipapa)
     * @param float $cant_vendida Cantidad de productos compuestos vendidos
     * @param int   $id_sucursal
     * @param int   $id_venta     Para referencia en kardex
     * @param string $nombre_producto Para la nota del kardex
     *
     * @return array ['ok' => bool, 'message' => string]
     */
    public function descontar_insumos($id_producto, $cant_vendida, $id_sucursal, $id_venta, $nombre_producto = '')
    {
        $insumos = $this->get_receta($id_producto);

        if (empty($insumos)) {
            return ['ok' => false, 'message' => 'El producto compuesto no tiene ingredientes configurados.'];
        }

        foreach ($insumos as $insumo) {
            $cant_necesaria = (float) $insumo->cantidad * (float) $cant_vendida;

            // Obtener stock actualizado del insumo (por si ya hubo otro descuento en este lote)
            $insumo_actual = $this->db->query(
                "SELECT stock FROM productos WHERE id = ? AND id_sucursal = ?",
                [$insumo->id_insumo, $id_sucursal]
            )->row();

            if (!$insumo_actual) {
                return [
                    'ok'      => false,
                    'message' => 'Insumo no encontrado en esta sucursal: ' . $insumo->insumo_nombre
                ];
            }

            if ((float) $insumo_actual->stock < $cant_necesaria) {
                return [
                    'ok'      => false,
                    'message' => 'Stock insuficiente del insumo "' . $insumo->insumo_nombre . '". '
                                 . 'Necesario: ' . $cant_necesaria
                                 . ', Disponible: ' . $insumo_actual->stock
                ];
            }

            // Descontar stock del insumo
            $this->db->query(
                "UPDATE productos SET stock = stock - ? WHERE id = ? AND id_sucursal = ?",
                [$cant_necesaria, $insumo->id_insumo, $id_sucursal]
            );

            // Obtener stock resultante
            $stock_nuevo = $this->db->query(
                "SELECT stock FROM productos WHERE id = ? AND id_sucursal = ?",
                [$insumo->id_insumo, $id_sucursal]
            )->row()->stock;

            // Registrar en kardex
            $nota = 'Insumo de: ' . $nombre_producto . ' (x' . number_format($cant_vendida, 0) . ')';
            $this->db->insert('kardex', [
                'id_sucursal'      => $id_sucursal,
                'id_producto'      => $insumo->id_insumo,
                'tipo_movimiento'  => 'Salida',
                'motivo'           => 'Venta',
                'doc_tipo'         => 'Venta',
                'doc_id'           => $id_venta,
                'cantidad'         => $cant_necesaria,
                'stock_resultante' => $stock_nuevo,
                'nota'             => $nota,
                'fecha'            => date('Y-m-d H:i:s')
            ]);
        }

        return ['ok' => true, 'message' => ''];
    }

    /**
     * Obtiene todos los productos insumo posibles (tipo produccion/licores)
     * para un selector en la vista de receta. Excluye el producto compuesto mismo.
     */
    public function get_insumos_disponibles($id_sucursal, $excluir_id = null)
    {
        $this->db->select('id, nombre, codigo_barras, stock, categoria');
        $this->db->from('productos');
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->where('tiene_receta', 0); // Solo productos simples pueden ser insumos
        if ($excluir_id) {
            $this->db->where('id !=', (int) $excluir_id);
        }
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get()->result();
    }
}
