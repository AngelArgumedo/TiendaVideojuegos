<?php
// src/core/patterns/InventoryNotifier.php

include_once __DIR__ . '/Observer.php';

class InventoryNotifier implements Observer {
    public function update(Subject $subject) {
        // En un caso real, aquí se notificaría al sistema de gestión de almacenes.
        error_log("OBSERVER: Sistema de inventario notificado para preparar el pedido ID: " . $subject->getPedidoId());
    }
}