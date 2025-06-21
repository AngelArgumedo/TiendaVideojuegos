<?php
// src/api/pedidos/manager.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../core/auth/AuthMiddleware.php';
include_once '../../models/Pedido.php';

// Protegemos la ruta, solo los admins pueden ver detalles de pedidos.
AuthMiddleware::isAdmin();

$pedido = new Pedido();

// Obtenemos el ID del pedido desde la URL
$pedido->id = isset($_GET['id']) ? $_GET['id'] : die();

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        // Intentamos leer los datos del pedido
        if ($pedido->leerUno()) {
            // Construimos la respuesta con los datos principales y la lista de items
            $pedido_details = array(
                "id" => $pedido->id,
                "cliente_nombre" => $pedido->nombre_usuario,
                "cliente_correo" => $pedido->correo_usuario,
                "cliente_direccion" => $pedido->direccion_usuario,
                "cliente_pais" => $pedido->pais_usuario,
                "total" => $pedido->total,
                "estado" => $pedido->estado,
                "items" => $pedido->items
            );
            http_response_code(200);
            echo json_encode($pedido_details);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Pedido no encontrado."));
        }
        break;
    
    // actualizar el estado del pedido.
    case 'PUT':
        // Obtenemos los datos enviados en el cuerpo de la petición
        $data = json_decode(file_get_contents("php://input"));
        
        // Verificamos que se haya enviado un nuevo estado
        if (!empty($data->estado)) {
            $pedido->estado = $data->estado;
            
            // Intentamos actualizar el estado en la base de datos
            if ($pedido->actualizarEstado()) {
                http_response_code(200);
                echo json_encode(array("success" => true, "message" => "El estado del pedido ha sido actualizado."));
            } else {
                http_response_code(503);
                echo json_encode(array("success" => false, "message" => "No se pudo actualizar el estado del pedido."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "Datos incompletos. Se requiere el nuevo 'estado'."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
