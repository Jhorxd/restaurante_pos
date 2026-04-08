<?php
// Script de diagnóstico para Restaurante POS
define('BASEPATH', 'debug');
include 'index.php';
$ci =& get_instance();
$ci->load->database();

$nombre = 'salchialitas';
echo "--- AUDITORIA DE PRODUCTO: $nombre ---\n";

$sql = "SELECT p.*, c.nombre as cat_nombre, c.comportamiento 
        FROM productos p 
        LEFT JOIN categorias c ON c.id = p.id_categoria 
        WHERE p.nombre LIKE ?";
$p = $ci->db->query($sql, ["%$nombre%"])->row();

if (!$p) {
    echo "ERROR: No se encontró el producto '$nombre'.\n";
    exit;
}

echo "Producto: " . $p->nombre . " (ID: " . $p->id . ")\n";
echo "Categoría: " . $p->cat_nombre . " (ID: " . $p->id_categoria . ")\n";
echo "Comportamiento: " . $p->comportamiento . "\n";
echo "Stock Actual: " . $p->stock . "\n";
echo "Tiene Receta (DB): " . ($p->tiene_receta ? 'SI (1)' : 'NO (0)') . "\n";

echo "\n--- RECETA DETALLADA ---\n";
$sql_r = "SELECT pr.*, p.nombre as insumo_nombre, p.stock as insumo_stock 
          FROM producto_receta pr 
          JOIN productos p ON p.id = pr.id_insumo 
          WHERE pr.id_producto = ?";
$receta = $ci->db->query($sql_r, [$p->id])->result();

if (empty($receta)) {
    echo "NO HAY INGREDIENTES REGISTRADOS PARA ESTE PRODUCTO.\n";
} else {
    foreach ($receta as $r) {
        echo "- " . $r->insumo_nombre . ": " . $r->cantidad . " (Stock actual: " . $r->insumo_stock . ")\n";
    }
}
echo "\n--- FIN DE AUDITORIA ---";
