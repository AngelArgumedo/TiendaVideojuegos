<?php
// src/core/auth/AuthMiddleware.php

require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {

    public static function validateToken() {
        $secret_key = getenv('JWT_SECRET');
        $jwt = null;
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(array("message" => "Acceso denegado. No se proporcionó token."));
            exit();
        }

        $arr = explode(" ", $authHeader);
        $jwt = $arr[1] ?? null;

        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
                return $decoded->data;

            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(array("message" => "Acceso denegado. Token inválido.", "error" => $e->getMessage()));
                exit();
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Acceso denegado. Formato de token incorrecto."));
            exit();
        }
    }

    /**
     * Verifica si el usuario autenticado es un administrador (Versión con Depuración).
     */
    public static function isAdmin() {
        $userData = self::validateToken();

        // --- INICIO DE LA DEPURACIÓN ---
        // Vamos a registrar el tipo de usuario que estamos leyendo del token.
        // Esto nos dirá exactamente qué valor se está comparando.
        if (isset($userData->tipo_usuario)) {
            error_log("AuthMiddleware: Verificando rol. Usuario del token tiene tipo_usuario: '" . $userData->tipo_usuario . "'");
        } else {
            error_log("AuthMiddleware: Error crítico - El campo 'tipo_usuario' no existe en el payload del token.");
        }
        // --- FIN DE LA DEPURACIÓN ---

        // --- LÓGICA "BLINDADA" ---
        // Usamos trim() para eliminar cualquier espacio en blanco accidental alrededor del rol.
        if (!isset($userData->tipo_usuario) || trim($userData->tipo_usuario) !== 'admin') {
            http_response_code(403); // Prohibido (Forbidden)
            echo json_encode(array("message" => "Acceso prohibido. Se requieren permisos de administrador."));
            exit();
        }

        return $userData; // Si es admin, devuelve los datos del usuario
    }
}
?>
