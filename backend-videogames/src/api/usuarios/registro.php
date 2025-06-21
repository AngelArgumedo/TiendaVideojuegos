<?php
// src/api/usuarios/registro.php

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

// Incluimos los archivos necesarios, incluyendo nuestra nueva fábrica
include_once '../../core/patterns/UserFactory.php';

// Obtenemos los datos enviados en el cuerpo de la petición
$data = json_decode(file_get_contents("php://input"));

// Verificamos que los datos indispensables no estén vacíos
if (
    !empty($data->nombre) &&
    !empty($data->apellido) &&
    !empty($data->correo) &&
    !empty($data->password)
) {
    // --- ¡PATRÓN FACTORY EN ACCIÓN! ---
    // Delegamos la creación completa del objeto Usuario a la fábrica.
    $usuario = UserFactory::createFromRequestData($data);

    // Intentamos crear el usuario usando el método del modelo
    if($usuario->crear()) {
        http_response_code(201);
        echo json_encode(array("success" => true, "message" => "El usuario ha sido creado exitosamente."));
    } else {
        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "No se pudo crear el usuario. El correo puede ya estar en uso."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Datos incompletos. Por favor, proporcione nombre, apellido, correo y contraseña."));
}
?>
