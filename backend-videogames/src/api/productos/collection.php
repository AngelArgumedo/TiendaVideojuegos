<?php
// src/api/productos/collection.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../core/auth/AuthMiddleware.php';
include_once '../../models/Producto.php';

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        $producto = new Producto();
        $stmt = $producto->leer();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $productos_arr = array();
            $productos_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $producto_item = array(
                    "id" => $id,
                    "nombre" => $nombre,
                    "descripcion" => html_entity_decode($descripcion),
                    "precio" => $precio,
                    "categoria" => $categoria,
                    "consola" => $consola,
                    "stock" => $stock,
                    "imagen_url" => $imagen_url
                );
                array_push($productos_arr["records"], $producto_item);
            }
            http_response_code(200);
            echo json_encode($productos_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron productos."));
        }
        break;

    case 'POST':
        // --- PROTECCIÓN DE RUTA ---
        AuthMiddleware::isAdmin();

        $producto = new Producto();
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nombre) && !empty($data->precio) && !empty($data->categoria) && isset($data->stock)) {
            // Generamos y guardamos el ID para poder devolverlo
            $newId = uniqid('prod_');
            $producto->id = $newId;
            $producto->nombre = $data->nombre;
            $producto->precio = $data->precio;
            $producto->categoria = $data->categoria;
            $producto->stock = $data->stock;
            $producto->consola = $data->consola ?? null;
            $producto->descripcion = $data->descripcion ?? null;
            // La imagen se subirá en un segundo paso, por lo que la URL inicial es null
            $producto->imagen_url = null;

            if ($producto->crear()) {
                http_response_code(201);
                // --- ¡CORRECCIÓN CLAVE! ---
                // Devolvemos el ID del producto recién creado en la respuesta JSON.
                echo json_encode([
                    "success" => true, 
                    "message" => "Producto creado.",
                    "id" => $newId 
                ]);
            } else {
                http_response_code(503);
                echo json_encode(["success" => false, "message" => "No se pudo crear el producto."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Datos incompletos."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
