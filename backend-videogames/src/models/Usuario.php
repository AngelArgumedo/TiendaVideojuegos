<?php
// src/models/Usuario.php

// Incluimos la conexión a la base de datos
include_once __DIR__ . '/../config/Database.php';

class Usuario {
    // Conexión a la BD y nombre de la tabla
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del objeto Usuario
    public $id;
    public $nombre;
    public $apellido;
    public $correo;
    public $password_hash;
    public $telefono;
    public $direccion;
    public $pais;
    public $tipo_usuario;
    public $fecha_creacion;

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    // --- MÉTODOS CRUD ---

    /**
     * Método para LEER todos los usuarios.
     * Ideal para un panel de administración.
     *
     * @return PDOStatement El objeto statement con el resultado.
     */
    public function leer() {
        $query = "SELECT id, nombre, apellido, correo, telefono, direccion ,pais, tipo_usuario, fecha_creacion FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Método para LEER los datos de un único usuario por su ID.
     * Útil para rellenar un formulario de edición.
     */
    public function leerUno() {
        $query = "SELECT nombre, apellido, correo, telefono, direccion, pais, tipo_usuario FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nombre = $row['nombre'];
            $this->apellido = $row['apellido'];
            $this->correo = $row['correo'];
            $this->telefono = $row['telefono'];
            $this->direccion = $row['direccion'];
            $this->pais = $row['pais'];
            $this->tipo_usuario = $row['tipo_usuario'];
            return true;
        }
        return false;
    }

    /**
     * Método para CREAR un nuevo usuario.
     *
     * @return bool Devuelve true si fue creado, false en caso contrario.
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . "
            SET id = :id, nombre = :nombre, apellido = :apellido, correo = :correo, password_hash = :password_hash, telefono = :telefono, direccion = :direccion, pais = :pais, tipo_usuario = :tipo_usuario";

        $stmt = $this->conn->prepare($query);
        
        // Limpiamos los datos
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->pais = htmlspecialchars(strip_tags($this->pais));
        $this->tipo_usuario = htmlspecialchars(strip_tags($this->tipo_usuario));

        // Vinculamos los valores
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':password_hash', $this->password_hash);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':pais', $this->pais);
        $stmt->bindParam(':tipo_usuario', $this->tipo_usuario);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Método para ACTUALIZAR un usuario existente.
     * Un admin podría cambiar el tipo de usuario, por ejemplo.
     *
     * @return bool Devuelve true si fue actualizado, false en caso contrario.
     */
    public function actualizar() {
        // Nota: No incluimos la actualización de contraseña aquí.
        // Eso debería ser un proceso separado y más seguro.
        $query = "UPDATE " . $this->table_name . "
            SET
                nombre = :nombre,
                apellido = :apellido,
                correo = :correo,
                telefono = :telefono,
                direccion = :direccion,
                pais = :pais,
                tipo_usuario = :tipo_usuario
            WHERE
                id = :id";

        $stmt = $this->conn->prepare($query);

        // Limpiamos los datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->pais = htmlspecialchars(strip_tags($this->pais));
        $this->tipo_usuario = htmlspecialchars(strip_tags($this->tipo_usuario));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vinculamos los valores
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':pais', $this->pais);
        $stmt->bindParam(':tipo_usuario', $this->tipo_usuario);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Método para ELIMINAR un usuario.
     * Una acción administrativa crítica.
     *
     * @return bool Devuelve true si fue eliminado, false en caso contrario.
     */
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiamos el ID
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Vinculamos el ID
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Método para encontrar un usuario por su dirección de correo electrónico.
     *
     * @param string $correo El correo del usuario a buscar.
     * @return bool|array Devuelve los datos del usuario si lo encuentra, false en caso contrario.
     */
    public function findByEmail($correo) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE correo = :correo LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Asignamos los valores a las propiedades del objeto
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->apellido = $row['apellido'];
            $this->correo = $row['correo'];
            $this->password_hash = $row['password_hash'];
            $this->tipo_usuario = $row['tipo_usuario'];
            $this->fecha_creacion = $row['fecha_creacion'];
            
            return true;
        }
        
        return false;
    }
}
?>
