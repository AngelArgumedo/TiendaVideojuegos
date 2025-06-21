// src/app/shared/models/cart.model.ts

import { Product } from "./product.model";

/**
 * Representa un único artículo dentro del carrito de compras.
 * Incluye los detalles más importantes del producto y la cantidad seleccionada.
 */
export interface CartItem {
  id: string;          // El ID del producto
  nombre: string;
  precio: number;        // Guardaremos el precio como número para cálculos fáciles
  imagen_url: string;
  cantidad: number;      // La cantidad de este producto en el carrito
}

/**
 * Representa el estado completo del carrito de compras.
 */
export interface Cart {
  items: CartItem[];      // La lista de todos los artículos en el carrito
  totalItems: number;     // El número total de artículos (sumando cantidades)
  totalPrice: number;     // El costo total de todos los artículos en el carrito
}
