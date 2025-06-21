<?php
include_once __DIR__ . '/../Strategy.php';
class StandardShipping implements ShippingStrategy {
    public function calculateCost($items) {
        return 5.00; // Costo fijo
    }
}