<?php
include_once __DIR__ . '/../Strategy.php';
class StorePickup implements ShippingStrategy {
    public function calculateCost($items) {
        return 0.00; // Sin costo
    }
}