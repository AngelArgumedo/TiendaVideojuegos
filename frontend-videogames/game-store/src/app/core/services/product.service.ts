// src/app/core/services/product.service.ts
import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Product, ProductApiResponse } from './../../shared/models/product.model';

@Injectable({
  // Se provee en 'root', por lo que está disponible en toda la aplicación.
  providedIn: 'root'
})
export class ProductService {
  private http = inject(HttpClient);
  private readonly API_URL = `${environment.API_BASE_URL}/productos`;

  /**
   * Obtiene la lista completa de todos los productos.
   * @returns Un Observable con la respuesta de la API que contiene el array de productos.
   */
  getProducts(): Observable<ProductApiResponse> {
    return this.http.get<ProductApiResponse>(this.API_URL);
  }

  /**
   * Obtiene los detalles de un único producto por su ID.
   * @param id - El ID del producto a buscar.
   * @returns Un Observable con el objeto del producto.
   */
  getProductById(id: string): Observable<Product> {
    return this.http.get<Product>(`${this.API_URL}/${id}`);
  }
}
