<?php
// src/api/usuarios/actualizar.php

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
include_once '../../core/auth/AuthMiddleware.php';
include_once '../../models/Usuario.php';

// --- INICIO DE LA PROTECCIÓN ---
// El middleware se ejecuta primero.
// Si el token no es válido o el usuario no es 'admin', el script se detendrá aquí.
AuthMiddleware::isAdmin();
// --- FIN DE LA PROTECCIÓN ---

// Si el script continúa, significa que el usuario es un administrador validado.

$usuario = new Usuario();

// Obtener los datos enviados en el cuerpo de la petición
$data = json_decode(file_get_contents("php://input"));

// Asegurarse de que los datos no estén vacíos y que el ID esté presente
if (
    !empty($data->id) &&
    !empty($data->nombre) &&
    !empty($data->apellido) &&
    !empty($data->correo) &&
    !empty($data->tipo_usuario)
) {
    // Asignar los valores al objeto Usuario
    $usuario->id = $data->id;
    $usuario->nombre = $data->nombre;
    $usuario->apellido = $data->apellido;
    $usuario->correo = $data->correo;
    $usuario->telefono = $data->telefono ?? null;
    $usuario->direccion = $data->direccion ?? null;
    $usuario->pais = $data->pais ?? null;
    $usuario->tipo_usuario = $data->tipo_usuario;

    // Intentar actualizar el usuario
    if ($usuario->actualizar()) {
        http_response_code(200); // OK
        echo json_encode(array("success" => true, "message" => "El usuario ha sido actualizado."));
    } else {
        http_response_code(503); // Servicio no disponible
        echo json_encode(array("success" => false, "message" => "No se pudo actualizar el usuario."));
    }
} else {
    http_response_code(400); // Petición incorrecta
    echo json_encode(array("success" => false, "message" => "Datos incompletos. Asegúrese de incluir el id del usuario."));
}
?>
