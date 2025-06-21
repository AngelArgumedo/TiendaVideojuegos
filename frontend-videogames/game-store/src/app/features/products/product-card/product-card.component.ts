import { Component, computed, input } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { Product } from '../../../shared/models/product.model';
import { environment } from '../../../../environments/environment';
import { RouterLink } from '@angular/router'; // ¡Importamos RouterLink!

@Component({
  selector: 'app-product-card',
  standalone: true,
  // Añadimos RouterLink a las importaciones del componente
  imports: [CommonModule, CurrencyPipe, RouterLink],
  templateUrl: './product-card.component.html',
  styleUrl: './product-card.component.scss'
})
export class ProductCardComponent {
  product = input.required<Product>();

  public fullImageUrl = computed(() => {
    const prod = this.product();
    if (prod && prod.imagen_url) {
      if (prod.imagen_url.startsWith('http')) {
        return prod.imagen_url;
      }
      const baseUrl = environment.API_BASE_URL.replace(/\/api\/?$/, '');
      return `${baseUrl}${prod.imagen_url}`;
    }
    return '';
  });
}
