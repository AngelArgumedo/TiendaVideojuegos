<?php
// src/api/usuarios/login.php

// Headers para CORS y tipo de contenido
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el autoloader de Composer usando una ruta más robusta
require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;

// Incluimos el modelo de Usuario
include_once __DIR__ . '/../../models/Usuario.php';

// Creamos una instancia del modelo
$usuario = new Usuario();

// Obtenemos los datos del cuerpo de la petición
$data = json_decode(file_get_contents("php://input"));

// Verificamos que los datos no estén vacíos
if (!empty($data->correo) && !empty($data->password)) {

    if ($usuario->findByEmail($data->correo)) {
        
        if (password_verify($data->password, $usuario->password_hash)) {
            
            // --- Autenticación Exitosa: Generación de JWT ---
            $secret_key = getenv('JWT_SECRET');
            $issuer_claim = getenv('JWT_ISSUER');
            $audience_claim = getenv('JWT_AUDIENCE');
            $issuedat_claim = time();
            $expire_claim = $issuedat_claim + (int)getenv('JWT_EXP_SECONDS');

            $payload = array(
                "iss" => $issuer_claim,
                "aud" => $audience_claim,
                "iat" => $issuedat_claim,
                "nbf" => $issuedat_claim,
                "exp" => $expire_claim,
                "data" => array(
                    "id" => $usuario->id,
                    "nombre" => $usuario->nombre,
                    "correo" => $usuario->correo,
                    "tipo_usuario" => $usuario->tipo_usuario
                )
            );

            $jwt = JWT::encode($payload, $secret_key, 'HS256');

            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Inicio de sesión exitoso.",
                "token" => $jwt
            ));

        } else {
            http_response_code(401);
            echo json_encode(array("success" => false, "message" => "Credenciales incorrectas."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "Credenciales incorrectas."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Datos incompletos."));
}
?>
