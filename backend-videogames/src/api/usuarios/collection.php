<?php
// src/api/usuarios/collection.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../core/auth/AuthMiddleware.php';
include_once '../../models/Usuario.php';

// Protegemos la ruta, solo los admins pueden ver la lista de usuarios
AuthMiddleware::isAdmin();

$usuario = new Usuario();
$stmt = $usuario->leer();
$num = $stmt->rowCount();

if ($num > 0) {
    $usuarios_arr = array();
    $usuarios_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $usuario_item = array(
            "id" => $id,
            "nombre" => $nombre,
            "apellido" => $apellido,
            "correo" => $correo,
            "telefono" => $telefono,
            "direccion" => $direccion,
            "pais" => $pais,
            "tipo_usuario" => $tipo_usuario,
            "fecha_creacion" => $fecha_creacion
        );
        array_push($usuarios_arr["records"], $usuario_item);
    }
    http_response_code(200);
    echo json_encode($usuarios_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No se encontraron usuarios."));
}
