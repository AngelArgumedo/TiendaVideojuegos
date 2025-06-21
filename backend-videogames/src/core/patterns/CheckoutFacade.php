<?php
// src/core/patterns/CheckoutFacade.php

include_once __DIR__ . '/../../models/Producto.php';
include_once __DIR__ . '/../../models/Pedido.php';
include_once __DIR__ . '/../../models/Usuario.php';
include_once __DIR__ . '/Observer.php';
// Incluimos la nueva fábrica de estrategias que crearemos en el siguiente paso
include_once __DIR__ . '/ShippingStrategyFactory.php';

class CheckoutFacade implements Subject {
    // Propiedades y métodos de Observer no cambian
    private $observers = [];
    private $pedidoDetails;

    public function __construct() {
        $this->observers = [];
    }

    public function attach(Observer $observer) { $this->observers[] = $observer; }
    public function detach(Observer $observer) {}
    public function notify() {
        foreach ($this->observers as $observer) { $observer->update($this); }
    }
    public function getPedidoDetails() {
        return $this->pedidoDetails;
    }

    // --- El método principal ahora acepta un tercer parámetro: $shippingMethod ---
    public function procesarPedido($usuarioId, $items, $shippingMethod) {
        if (empty($items)) { return ['success' => false, 'message' => 'El carrito está vacío.']; }
        
        try {
            // 1. Validar y enriquecer items (sin cambios)
            $validacionYDatos = $this->validarYObtenerDatosItems($items);
            if (!$validacionYDatos['success']) { return $validacionYDatos; }
            $itemsEnriquecidos = $validacionYDatos['items'];

            // 2. Calcular subtotal (costo de los productos)
            $subtotalPedido = $this->calcularSubtotal($itemsEnriquecidos);

            // 3. --- ¡PATRÓN STRATEGY EN ACCIÓN! ---
            $shippingStrategy = ShippingStrategyFactory::create($shippingMethod);
            $costoEnvio = $shippingStrategy->calculateCost($itemsEnriquecidos);
            
            // 4. Calcular el total final
            $totalPedido = $subtotalPedido + $costoEnvio;

            // 5. Crear el pedido con los nuevos valores
            $pedido = new Pedido();
            $pedido->id = uniqid('pedido_');
            $pedido->usuario_id = $usuarioId;
            $pedido->subtotal = $subtotalPedido; // Nuevo
            $pedido->costo_envio = $costoEnvio;   // Nuevo
            $pedido->total = $totalPedido;       // Nuevo
            $pedido->items = $itemsEnriquecidos;

            if ($pedido->crear()) {
                $this->pedidoDetails = $this->prepararDetallesParaNotificacion($pedido->id, $usuarioId, $totalPedido, $itemsEnriquecidos);
                $this->notify();
                return ['success' => true, 'message' => 'Pedido creado exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Hubo un error al procesar el pedido.'];
            }
        } catch (InvalidArgumentException $e) {
            // Captura el error si se pasa un método de envío inválido desde la Factory
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // --- MÉTODOS PRIVADOS ---
    private function calcularSubtotal($itemsEnriquecidos) {
        $total = 0;
        foreach ($itemsEnriquecidos as $item) { $total += $item['precio'] * $item['cantidad']; }
        return $total;
    }
    
    // Estos métodos no cambian, pero los incluyo para que tengas el archivo completo
    private function validarYObtenerDatosItems($items) {
        $productoModel = new Producto();
        $itemsEnriquecidos = [];
        foreach ($items as $item) {
            $productoModel->id = $item['id'];
            if (!$productoModel->leerUno()) { 
                return ['success' => false, 'message' => 'El producto con ID ' . $item['id'] . ' no existe.']; 
            }
            if ($productoModel->stock < $item['cantidad']) { 
                return ['success' => false, 'message' => 'Stock insuficiente para el producto: ' . $productoModel->nombre]; 
            }
            $item['precio'] = $productoModel->precio;
            $item['nombre'] = $productoModel->nombre;
            $itemsEnriquecidos[] = $item;
        }
        return ['success' => true, 'items' => $itemsEnriquecidos];
    }
    
    private function prepararDetallesParaNotificacion($pedidoId, $usuarioId, $total, $items) {
        $usuarioModel = new Usuario();
        $usuarioModel->id = $usuarioId;
        $usuarioModel->leerUno();

        $resumenPedido = "";
        foreach ($items as $item) {
            $resumenPedido .= "- " . $item['nombre'] . " (x" . $item['cantidad'] . ")\n";
        }

        return [
            "pedido_id" => $pedidoId,
            "usuario_nombre" => $usuarioModel->nombre,
            "usuario_email" => $usuarioModel->correo,
            "total" => $total,
            "resumen_pedido" => $resumenPedido
        ];
    }
}
