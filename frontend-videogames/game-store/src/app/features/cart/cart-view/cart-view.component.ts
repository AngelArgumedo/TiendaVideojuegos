import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CartService } from '../../../core/services/cart.service';

type CheckoutState = 'idle' | 'loading' | 'success' | 'error';

@Component({
  selector: 'app-cart-view',
  standalone: true,
  imports: [CommonModule, CurrencyPipe, RouterLink, FormsModule],
  templateUrl: './cart-view.component.html',
  styleUrl: './cart-view.component.scss'
})
export class CartViewComponent {
  cartService = inject(CartService);

  // Señal para el estado del checkout
  checkoutState = signal<CheckoutState>('idle');
  checkoutMessage = signal<string>('');

  // Señal para el método de envío seleccionado
  selectedShippingMethod = signal<string>('standard');

  // Señales computadas para los costos
  shippingCost = computed(() => {
    switch(this.selectedShippingMethod()) {
      case 'express': return 15;
      case 'pickup': return 0;
      default: return 5;
    }
  });

  totalPrice = computed(() => this.cartService.cartState().totalPrice + this.shippingCost());

  // Métodos para interactuar con el carrito desde la plantilla
  updateQuantity(id: string, event: Event) {
    const newQuantity = parseInt((event.target as HTMLInputElement).value, 10);
    this.cartService.updateItemQuantity(id, newQuantity);
  }

  removeItem(id: string) {
    this.cartService.removeItem(id);
  }

  // Método para procesar el pago
  handleCheckout() {
    this.checkoutState.set('loading');
    this.cartService.checkout(this.selectedShippingMethod()).subscribe({
      next: (response) => {
        this.checkoutState.set('success');
        this.checkoutMessage.set(response.message);
      },
      error: (err) => {
        this.checkoutState.set('error');
        this.checkoutMessage.set(err.error?.message || 'Hubo un error inesperado.');
      }
    });
  }
}
