// src/app/core/services/cart.service.ts
import { Injectable, signal, computed, effect, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { Cart, CartItem } from '../../shared/models/cart.model';
import { Product } from '../../shared/models/product.model';
import { environment } from '../../../environments/environment';

const INITIAL_STATE: Cart = {
  items: [],
  totalItems: 0,
  totalPrice: 0,
};

@Injectable({
  providedIn: 'root'
})
export class CartService {
  private readonly CART_KEY = 'shopping_cart';
  private http = inject(HttpClient);
  private readonly API_URL = `${environment.API_BASE_URL}/pedidos`;

  private _cartState = signal<Cart>(INITIAL_STATE);
  public readonly cartState = this._cartState.asReadonly();

  constructor() {
    this.loadCartFromStorage();
    effect(() => {
      this.saveCartToStorage(this._cartState());
    });
  }

  // --- MÉTODOS PÚBLICOS PARA MANIPULAR EL CARRITO ---

  addItem(product: Product): void {
    this._cartState.update(currentCart => {
      const existingItem = currentCart.items.find(item => item.id === product.id);
      let updatedItems: CartItem[];

      if (existingItem) {
        updatedItems = currentCart.items.map(item =>
          item.id === product.id ? { ...item, cantidad: item.cantidad + 1 } : item
        );
      } else {
        const newItem: CartItem = {
          id: product.id,
          nombre: product.nombre,
          precio: parseFloat(product.precio),
          imagen_url: product.imagen_url,
          cantidad: 1,
        };
        updatedItems = [...currentCart.items, newItem];
      }
      return this.recalculateCart(updatedItems);
    });
  }

  updateItemQuantity(productId: string, newQuantity: number): void {
    this._cartState.update(currentCart => {
      let updatedItems: CartItem[];
      if (newQuantity > 0) {
        updatedItems = currentCart.items.map(item =>
          item.id === productId ? { ...item, cantidad: newQuantity } : item
        );
      } else {
        // Si la cantidad es 0 o menos, eliminamos el item.
        updatedItems = currentCart.items.filter(item => item.id !== productId);
      }
      return this.recalculateCart(updatedItems);
    });
  }

  removeItem(productId: string): void {
    this._cartState.update(currentCart => {
      const updatedItems = currentCart.items.filter(item => item.id !== productId);
      return this.recalculateCart(updatedItems);
    });
  }

  clearCart(): void {
    this._cartState.set(INITIAL_STATE);
  }

  /**
   * Procesa el checkout llamando a la API de pedidos.
   * @param shippingMethod - El método de envío elegido ('standard', 'express', 'pickup').
   * @returns Un Observable con la respuesta del backend.
   */
  checkout(shippingMethod: string): Observable<{success: boolean, message: string}> {
    const cart = this._cartState();
    const payload = {
      shipping_method: shippingMethod,
      items: cart.items.map(item => ({ id: item.id, cantidad: item.cantidad }))
    };

    return this.http.post<{success: boolean, message: string}>(this.API_URL, payload).pipe(
      tap(response => {
        // Si el pedido se crea exitosamente, vaciamos el carrito.
        if (response.success) {
          this.clearCart();
        }
      })
    );
  }

  // --- MÉTODOS PRIVADOS ---
  private recalculateCart(items: CartItem[]): Cart {
    const totalItems = items.reduce((acc, item) => acc + item.cantidad, 0);
    const totalPrice = items.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
    return { items, totalItems, totalPrice };
  }

  private loadCartFromStorage(): void {
    if (typeof window !== 'undefined') {
      const savedCartJson = localStorage.getItem(this.CART_KEY);
      if (savedCartJson) {
        this._cartState.set(JSON.parse(savedCartJson));
      }
    }
  }

  private saveCartToStorage(cart: Cart): void {
     if (typeof window !== 'undefined') {
      localStorage.setItem(this.CART_KEY, JSON.stringify(cart));
    }
  }
}
