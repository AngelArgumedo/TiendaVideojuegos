<?php
// src/config/Database.php

class Database {
    // Las propiedades de conexión provienen del constructor
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    // Propiedad para almacenar la única instancia de la clase (Patrón Singleton)
    private static $instance = null;

    /**
     * El constructor es privado para prevenir la creación de objetos
     * directamente con el operador 'new'.
     * La conexión se establece aquí, leyendo las variables de entorno.
     */
    private function __construct() {
        // Leemos las credenciales desde las variables de entorno inyectadas por Docker
        $this->host = getenv('DB_HOST');
        $this->db_name = getenv('DB_DATABASE');
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASSWORD');
        
        $this->conn = null;

        try {
            // Data Source Name (DSN) para la conexión PDO
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name;

            // Crear una nueva instancia de PDO (PHP Data Objects)
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Establecer el modo de error de PDO a excepción para un mejor manejo de errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Establecer la codificación a UTF-8
            $this->conn->exec("set names utf8");

        } catch(PDOException $exception) {
            // Si la conexión falla, muestra el error y termina la ejecución
            // En un entorno de producción, esto debería registrarse en un archivo en lugar de mostrarse.
            error_log('Error de Conexión: ' . $exception->getMessage());
            // Devolver una respuesta JSON genérica para no exponer detalles del error
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
            exit();
        }
    }
    
    /**
     * Previene la clonación del objeto (parte del patrón Singleton).
     */
    private function __clone() {}

    /**
     * Previene la deserialización del objeto (parte del patrón Singleton).
     */
    public function __wakeup() {}

    /**
     * Método estático público que controla el acceso a la única instancia.
     * Esta es la esencia del Patrón Singleton.
     *
     * @return Database La única instancia de la clase Database.
     */
    public static function getInstance() {
        // Si no existe una instancia, la creamos
        if (self::$instance == null) {
            self::$instance = new Database();
        }

        // Devolvemos la instancia (ya sea la nueva o la existente)
        return self::$instance;
    }

    /**
     * Devuelve el objeto de conexión PDO para ser utilizado en las consultas.
     *
     * @return PDO El objeto de conexión.
     */
    public function getConnection() {
        return $this->conn;
    }
}
?>
