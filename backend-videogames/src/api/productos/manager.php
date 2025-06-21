<?php
// src/api/productos/manager.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../core/auth/AuthMiddleware.php';
include_once '../../models/Producto.php';

$producto = new Producto();
$producto->id = isset($_GET['id']) ? $_GET['id'] : die();

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if ($producto->leerUno()) {
            $producto_item = array(
                "id" => $producto->id,
                "nombre" => $producto->nombre,
                "descripcion" => $producto->descripcion,
                "precio" => $producto->precio,
                "categoria" => $producto->categoria,
                "consola" => $producto->consola,
                "stock" => $producto->stock,
                "imagen_url" => $producto->imagen_url
            );
            http_response_code(200);
            echo json_encode($producto_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Producto no encontrado."));
        }
        break;

    case 'PUT':
        // --- PROTECCIÓN DE RUTA ---
        AuthMiddleware::isAdmin();
        
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->nombre)) {
            $producto->nombre = $data->nombre;
            $producto->precio = $data->precio;
            $producto->categoria = $data->categoria;
            $producto->stock = $data->stock;
            $producto->consola = $data->consola ?? null;
            $producto->descripcion = $data->descripcion ?? null;
            $producto->imagen_url = $data->imagen_url ?? null;

            if ($producto->actualizar()) {
                http_response_code(200);
                echo json_encode(array("success" => true, "message" => "Producto actualizado."));
            } else {
                http_response_code(503);
                echo json_encode(array("success" => false, "message" => "No se pudo actualizar el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "Datos incompletos."));
        }
        break;

    case 'DELETE':
        // --- PROTECCIÓN DE RUTA ---
        AuthMiddleware::isAdmin();

        if ($producto->eliminar()) {
            http_response_code(200);
            echo json_encode(array("success" => true, "message" => "Producto eliminado."));
        } else {
            http_response_code(503);
            echo json_encode(array("success" => false, "message" => "No se pudo eliminar el producto."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
