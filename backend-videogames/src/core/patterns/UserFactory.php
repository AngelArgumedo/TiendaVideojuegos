<?php
// src/core/patterns/UserFactory.php

// La fábrica necesita conocer el modelo que va a crear
include_once __DIR__ . '/../../models/Usuario.php';

class UserFactory {
    /**
     * Crea y configura un objeto Usuario a partir de los datos de una petición.
     * Este es el "método de fábrica" estático.
     *
     * @param object $data Los datos decodificados de la petición (ej. desde json_decode).
     * @return Usuario Un objeto Usuario listo para ser guardado.
     */
    public static function createFromRequestData($data) {
        // 1. Instanciamos el objeto base
        $usuario = new Usuario();

        // 2. Asignamos todas las propiedades, encapsulando la lógica aquí
        $usuario->id = uniqid('user_');
        $usuario->nombre = $data->nombre;
        $usuario->apellido = $data->apellido;
        $usuario->correo = $data->correo;
        
        // La lógica de hasheo de contraseña está centralizada aquí
        $usuario->password_hash = password_hash($data->password, PASSWORD_BCRYPT);

        $usuario->telefono = $data->telefono ?? null;
        $usuario->direccion = $data->direccion ?? null;
        $usuario->pais = $data->pais ?? null;
        $usuario->tipo_usuario = $data->tipo_usuario ?? 'comprador';
        
        // 3. Devolvemos el objeto completamente configurado
        return $usuario;
    }
}
