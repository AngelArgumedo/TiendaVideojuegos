<?php
include_once __DIR__ . '/../Strategy.php';
class ExpressShipping implements ShippingStrategy {
    public function calculateCost($items) {
        return 15.00; // Costo fijo
    }
}