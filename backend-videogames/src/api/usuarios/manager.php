<?php
// src/api/usuarios/manager.php
// Este archivo maneja las peticiones PUT (actualizar) y DELETE (eliminar)
// para un usuario específico, identificado por el ID en la URL.

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, DELETE"); // Permitimos ambos métodos
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
include_once __DIR__ . '/../../core/auth/AuthMiddleware.php';
include_once __DIR__ . '/../../models/Usuario.php';

// --- INICIO DE LA PROTECCIÓN ---
// El middleware se ejecuta primero para cualquier método.
AuthMiddleware::isAdmin();
// --- FIN DE LA PROTECCIÓN ---

// Si el script continúa, el usuario es un administrador validado.

$usuario = new Usuario();

// Obtenemos el ID del usuario desde el parámetro de la URL (gracias al .htaccess)
$usuario->id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(["message" => "No se proporcionó ID de usuario."]));

// Verificamos el método de la petición para decidir qué hacer
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'PUT':
        // --- LÓGICA PARA ACTUALIZAR (PUT) ---
        $data = json_decode(file_get_contents("php://input"));

        // Validamos que los datos necesarios para la actualización estén presentes
        if (!empty($data->nombre) && !empty($data->apellido) && !empty($data->correo)) {
            $usuario->nombre = $data->nombre;
            $usuario->apellido = $data->apellido;
            $usuario->correo = $data->correo;
            $usuario->telefono = $data->telefono ?? null;
            $usuario->direccion = $data->direccion ?? null;
            $usuario->pais = $data->pais ?? null;
            $usuario->tipo_usuario = $data->tipo_usuario ?? 'comprador';

            if ($usuario->actualizar()) {
                http_response_code(200);
                echo json_encode(array("success" => true, "message" => "El usuario ha sido actualizado."));
            } else {
                http_response_code(503);
                echo json_encode(array("success" => false, "message" => "No se pudo actualizar el usuario."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "Datos incompletos."));
        }
        break;

    case 'DELETE':
        // --- LÓGICA PARA ELIMINAR (DELETE) ---
        if ($usuario->eliminar()) {
            http_response_code(200);
            echo json_encode(array("success" => true, "message" => "El usuario ha sido eliminado."));
        } else {
            http_response_code(503);
            echo json_encode(array("success" => false, "message" => "No se pudo eliminar el usuario."));
        }
        break;

    default:
        // Método no permitido para esta URL
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("success" => false, "message" => "Método no permitido."));
        break;
}
?>
