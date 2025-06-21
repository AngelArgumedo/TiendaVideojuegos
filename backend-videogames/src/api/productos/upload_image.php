<?php
// src/api/productos/upload_image.php

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
include_once '../../core/auth/AuthMiddleware.php';
include_once '../../models/Producto.php';

// --- INICIO DE LA PROTECCIÓN ---
AuthMiddleware::isAdmin();
// --- FIN DE LA PROTECCIÓN ---

$producto = new Producto();

// Obtenemos el ID del producto desde el parámetro de la URL
$producto->id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(["message" => "No se proporcionó ID de producto."]));

// --- LÓGICA DE SUBIDA DE ARCHIVO ---
if (isset($_FILES['imagen'])) {
    
    // Directorio de destino para las imágenes
    $target_directory = "../../public/uploads/productos/";
    $file_extension = pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
    
    // Crear un nombre de archivo único usando el ID del producto
    $new_file_name = $producto->id . "." . $file_extension;
    $target_file = $target_directory . $new_file_name;
    
    // Validaciones
    $check = getimagesize($_FILES["imagen"]["tmp_name"]);
    if($check === false) {
        http_response_code(400);
        die(json_encode(["message" => "El archivo no es una imagen."]));
    }

    if ($_FILES["imagen"]["size"] > 2000000) { // 2MB
        http_response_code(400);
        die(json_encode(["message" => "El archivo es demasiado grande. Máximo 2MB."]));
    }

    $allowed_types = array("jpg", "jpeg", "png", "gif");
    if(!in_array(strtolower($file_extension), $allowed_types)) {
        http_response_code(400);
        die(json_encode(["message" => "Solo se permiten archivos JPG, JPEG, PNG y GIF."]));
    }

    // Intentar mover el archivo subido al directorio de destino
    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
        
        // El archivo se movió, ahora actualizamos la base de datos
        $producto->imagen_url = "/public/uploads/productos/" . $new_file_name;
        
        if ($producto->actualizarImagenUrl()) {
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "La imagen ha sido subida y actualizada.",
                "url" => $producto->imagen_url
            ]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "La imagen fue subida pero no se pudo actualizar la base de datos."]);
        }
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Hubo un error al subir la imagen."]);
    }

} else {
    http_response_code(400);
    echo json_encode(["message" => "No se encontró ningún archivo de imagen en la petición."]);
}
?>
