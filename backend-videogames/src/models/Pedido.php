<?php
// src/models/Pedido.php

include_once __DIR__ . '/../config/Database.php';

class Pedido {
    private $conn;
    private $table_pedidos = "pedidos";
    private $table_detalle = "detalle_pedidos";
    private $table_productos = "productos";

    public $id;
    public $usuario_id;
    public $subtotal;
    public $costo_envio;
    public $total;
    public $estado;
    public $fecha_pedido;
    // Propiedades para el detalle del pedido
    public $items; // Será un array de productos

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    /**
     * Crea un nuevo pedido completo (pedido + detalles) dentro de una transacción.
     *
     * @return bool Devuelve true si el pedido fue creado exitosamente, false en caso contrario.
     */
    public function crear() {
        // --- Iniciamos una transacción ---
        // Esto asegura que todas las operaciones de la base de datos se completen con éxito.
        // Si algo falla, podemos revertir todos los cambios.
        $this->conn->beginTransaction();

        try {
            // 1. Crear el registro principal en la tabla 'pedidos'
            $query_pedido = "INSERT INTO " . $this->table_pedidos . "
                SET id=:id, usuario_id=:usuario_id, subtotal=:subtotal, costo_envio=:costo_envio, total=:total, estado=:estado";
            
            $stmt_pedido = $this->conn->prepare($query_pedido);
            
            // Limpiar datos
            $this->id = htmlspecialchars(strip_tags($this->id));
            $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));
            $this->subtotal = htmlspecialchars(strip_tags($this->subtotal));
            $this->costo_envio = htmlspecialchars(strip_tags($this->costo_envio));
            $this->total = htmlspecialchars(strip_tags($this->total));
            $this->estado = htmlspecialchars(strip_tags('procesando'));

            // Vincular datos
            $stmt_pedido->bindParam(':id', $this->id);
            $stmt_pedido->bindParam(':usuario_id', $this->usuario_id);
            $stmt_pedido->bindParam(':subtotal', $this->subtotal);
            $stmt_pedido->bindParam(':costo_envio', $this->costo_envio);
            $stmt_pedido->bindParam(':total', $this->total);
            $stmt_pedido->bindParam(':estado', $this->estado);
            
            $stmt_pedido->execute();

            // 2. Crear los registros en 'detalle_pedidos' y actualizar el stock
            foreach ($this->items as $item) {
                // Insertar en detalle_pedidos
                $query_detalle = "INSERT INTO " . $this->table_detalle . "
                    SET pedido_id=:pedido_id, producto_id=:producto_id, cantidad=:cantidad, precio_unitario=:precio_unitario";
                
                $stmt_detalle = $this->conn->prepare($query_detalle);
                
                $stmt_detalle->bindParam(':pedido_id', $this->id);
                $stmt_detalle->bindParam(':producto_id', $item['id']);
                $stmt_detalle->bindParam(':cantidad', $item['cantidad']);
                $stmt_detalle->bindParam(':precio_unitario', $item['precio']);
                
                $stmt_detalle->execute();

                // Actualizar el stock del producto
                $query_stock = "UPDATE " . $this->table_productos . " SET stock = stock - :cantidad WHERE id = :producto_id";
                $stmt_stock = $this->conn->prepare($query_stock);
                
                $stmt_stock->bindParam(':cantidad', $item['cantidad']);
                $stmt_stock->bindParam(':producto_id', $item['id']);
                
                $stmt_stock->execute();
            }

            // 3. Si todo fue exitoso, confirmamos la transacción
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // 4. Si algo falló, revertimos todos los cambios
            $this->conn->rollBack();
            // En un entorno real, registraríamos el error: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * LEER todos los pedidos (para el admin)
     */
    // El método leer() se actualiza para obtener los nuevos campos
    public function leer() {
        $query = "SELECT
                    p.id, p.subtotal, p.costo_envio, p.total, p.estado, p.fecha_pedido,
                    CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario, 
                    u.correo AS correo_usuario
                  FROM " . $this->table_pedidos . " p
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  ORDER BY p.fecha_pedido DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * LEE los detalles completos de un único pedido por su ID.
     */
    public function leerUno() {
        // Obtenemos los datos principales del pedido y del usuario
        $query_main = "SELECT
                        p.id, p.subtotal, p.costo_envio, p.total, p.estado, p.fecha_pedido,
                        CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario, 
                        u.correo AS correo_usuario, u.direccion AS direccion_usuario, u.pais AS pais_usuario
                      FROM " . $this->table_pedidos . " p
                      LEFT JOIN usuarios u ON p.usuario_id = u.id
                      WHERE p.id = ? LIMIT 1";
        
        $stmt_main = $this->conn->prepare($query_main);
        $stmt_main->bindParam(1, $this->id);
        $stmt_main->execute();
        $row = $stmt_main->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Asignamos todas las propiedades correctamente
            $this->id = $row['id'];
            $this->total = $row['total'];
            $this->estado = $row['estado'];
            $this->nombre_usuario = $row['nombre_usuario'];
            $this->correo_usuario = $row['correo_usuario'];
            $this->direccion_usuario = $row['direccion_usuario'];
            $this->pais_usuario = $row['pais_usuario'];

            // Obtenemos los items del detalle del pedido
            $query_items = "SELECT d.cantidad, d.precio_unitario, pr.nombre AS nombre_producto
                            FROM " . $this->table_detalle . " d
                            LEFT JOIN productos pr ON d.producto_id = pr.id
                            WHERE d.pedido_id = ?";
            
            $stmt_items = $this->conn->prepare($query_items);
            $stmt_items->bindParam(1, $this->id);
            $stmt_items->execute();
            $this->items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            return true;
        }
        return false;
    }

    /**
     * Actualiza el estado del pedido.
     */
    public function actualizarEstado() {
        $query = "UPDATE " . $this->table_pedidos . " SET estado = :estado WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vincular datos
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

}
?>
