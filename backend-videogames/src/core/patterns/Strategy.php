<?php
// src/core/patterns/Strategy.php

interface ShippingStrategy {
    public function calculateCost($items);
}
