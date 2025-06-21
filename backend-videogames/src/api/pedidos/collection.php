<?php
// src/api/pedidos/collection.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../../core/auth/AuthMiddleware.php';
include_once __DIR__ . '/../../core/patterns/CheckoutFacade.php';
include_once __DIR__ . '/../../models/Pedido.php';
include_once __DIR__ . '/../../core/patterns/EmailNotifier.php';


$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'POST':
        $userData = AuthMiddleware::validateToken();
        $data = json_decode(file_get_contents("php://input"), true);

        // Verificamos que ahora se envíen los items Y el método de envío
        if (!empty($data['items']) && !empty($data['shipping_method'])) {
            $checkoutFacade = new CheckoutFacade();
            
            $checkoutFacade->attach(new EmailNotifier());
            
            // Pasamos el parámetro 'shipping_method' al método de la fachada
            $resultado = $checkoutFacade->procesarPedido($userData->id, $data['items'], $data['shipping_method']);

            if ($resultado['success']) {
                http_response_code(201);
                echo json_encode($resultado);
            } else {
                http_response_code(400);
                echo json_encode($resultado);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos. Se requieren "items" y "shipping_method".']);
        }
        break;

    case 'GET':
        AuthMiddleware::isAdmin();

        $pedido = new Pedido();
        $stmt = $pedido->leer();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $pedidos_arr = array();
            $pedidos_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // El extract ahora también creará $subtotal y $costo_envio
                extract($row);
                $pedido_item = array(
                    "id" => $id,
                    "nombre_usuario" => $nombre_usuario,
                    "correo_usuario" => $correo_usuario,
                    "subtotal" => $subtotal,
                    "costo_envio" => $costo_envio,
                    "total" => $total,
                    "estado" => $estado,
                    "fecha_pedido" => $fecha_pedido
                );
                array_push($pedidos_arr["records"], $pedido_item);
            }

            http_response_code(200);
            echo json_encode($pedidos_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron pedidos."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
