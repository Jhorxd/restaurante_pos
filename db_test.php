<?php
// Script de diagnóstico SQL Directo
$host = 'localhost';
$user = 'root';
$pass = ''; // XAMPP por defecto es vacio
$db   = 'restaurante_pos';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$nombre = 'salchialitas';
echo "--- CONSULTA SQL PARA: $nombre ---\n";

$sql = "SELECT p.id, p.nombre, p.tiene_receta, p.stock, p.id_categoria, c.nombre as cat_nombre, c.comportamiento 
        FROM productos p 
        LEFT JOIN categorias c ON c.id = p.id_categoria 
        WHERE p.nombre LIKE '%$nombre%'";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($p = $result->fetch_assoc()) {
        echo "ID: " . $p['id'] . "\n";
        echo "Nombre: " . $p['nombre'] . "\n";
        echo "Stock: " . $p['stock'] . "\n";
        echo "ID Categoria: " . $p['id_categoria'] . " (" . $p['cat_nombre'] . ")\n";
        echo "Comportamiento: " . $p['comportamiento'] . "\n";
        echo "Tiene Receta (DB): " . $p['tiene_receta'] . "\n";
        
        $pid = $p['id'];
        $sql_r = "SELECT pr.*, p.nombre as insumo_nombre FROM producto_receta pr 
                  JOIN productos p ON p.id = pr.id_insumo WHERE pr.id_producto = $pid";
        $res_r = $conn->query($sql_r);
        echo "Receta: " . $res_r->num_rows . " ingredientes.\n";
        while($r = $res_r->fetch_assoc()) {
            echo "  - " . $r['insumo_nombre'] . " (ID: ".$r['id_insumo']."): " . $r['cantidad'] . "\n";
        }
        echo "---------------------------\n";
    }
} else {
    echo "No se encontró el producto.\n";
}
$conn->close();
