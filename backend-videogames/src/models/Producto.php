<?php
// src/models/Producto.php

include_once __DIR__ . '/../config/Database.php';

class Producto {
    // Conexión a la BD y nombre de la tabla
    private $conn;
    private $table_name = "productos";

    // Propiedades del objeto Producto
    public $id;
    public $nombre;
    public $consola;
    public $categoria;
    public $precio;
    public $stock;
    public $descripcion;
    public $imagen_url;
    public $fecha_creacion;

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    /**
     * LEER todos los productos
     */
    public function leer() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * LEER un único producto por su ID
     */
    public function leerUno() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nombre = $row['nombre'];
            $this->consola = $row['consola'];
            $this->categoria = $row['categoria'];
            $this->precio = $row['precio'];
            $this->stock = $row['stock'];
            $this->descripcion = $row['descripcion'];
            $this->imagen_url = $row['imagen_url'];
            $this->fecha_creacion = $row['fecha_creacion'];
            return true;
        }
        return false;
    }

    /**
     * CREAR un nuevo producto
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . "
            SET id=:id, nombre=:nombre, consola=:consola, categoria=:categoria, precio=:precio, stock=:stock, descripcion=:descripcion, imagen_url=:imagen_url";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->consola = htmlspecialchars(strip_tags($this->consola));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        
        // Vincular datos
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":consola", $this->consola);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":imagen_url", $this->imagen_url);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * ACTUALIZAR un producto existente
     */
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . "
            SET nombre=:nombre, consola=:consola, categoria=:categoria, precio=:precio, stock=:stock, descripcion=:descripcion, imagen_url=:imagen_url
            WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->consola = htmlspecialchars(strip_tags($this->consola));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vincular datos
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":consola", $this->consola);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * ELIMINAR un producto
     */
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    
    /**
     * ACTUALIZA únicamente la URL de la imagen para un producto específico.
     *
     * @return bool Devuelve true si la URL fue actualizada, false en caso contrario.
     */
    public function actualizarImagenUrl() {
        $query = "UPDATE " . $this->table_name . " SET imagen_url = :imagen_url WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vincular datos
        $stmt->bindParam(':imagen_url', $this->imagen_url);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

}
?>
