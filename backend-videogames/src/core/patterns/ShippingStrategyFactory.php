<?php
// src/core/patterns/ShippingStrategyFactory.php

// Incluimos todas las estrategias que la fábrica puede crear
include_once __DIR__ . '/strategyShipping/StandardShipping.php';
include_once __DIR__ . '/strategyShipping/ExpressShipping.php';
include_once __DIR__ . '/strategyShipping/StorePickup.php';

class ShippingStrategyFactory {
    public static function create($method) {
        switch (strtolower($method)) {
            case 'standard':
                return new StandardShipping();
            case 'express':
                return new ExpressShipping();
            case 'pickup':
                return new StorePickup();
            default:
                throw new InvalidArgumentException("Método de envío no válido.");
        }
    }
}
