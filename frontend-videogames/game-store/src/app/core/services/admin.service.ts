// src/app/core/services/admin.service.ts
import { Injectable } from '@angular/core';
import { inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { User } from '../../shared/models/user.model';
import { Product, ProductApiResponse } from '../../shared/models/product.model';
import { Order, OrdersApiResponse, OrderDetail, OrderItem } from '../../shared/models/order.model';

// Interfaz para la respuesta del endpoint de leer todos los usuarios
interface UsersApiResponse {
  records: User[];
}

// Interfaz genérica para respuestas de éxito/error (como en DELETE o PUT)
interface ApiResponse {
  success: boolean;
  message: string;
  id?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AdminService {

  private http = inject(HttpClient);
  private readonly USERS_API_URL = `${environment.API_BASE_URL}/usuarios`;
  private readonly PRODUCTS_API_URL = `${environment.API_BASE_URL}/productos`;
  private readonly ORDERS_API_URL = `${environment.API_BASE_URL}/pedidos`;

  // --- Métodos de Usuario ---

  /**
   * Obtiene la lista completa de todos los usuarios.
   * La API se encarga de que esta ruta solo sea accesible para administradores.
   * @returns Un Observable con la respuesta que contiene el array de usuarios.
   */
  getUsers(): Observable<UsersApiResponse> {
    return this.http.get<UsersApiResponse>(this.USERS_API_URL);
  }

  /**
   * Elimina un usuario por su ID.
   * @param id - El ID del usuario a eliminar.
   * @returns Un Observable con la respuesta de éxito/error del backend.
   */
  deleteUser(id: string): Observable<ApiResponse> {
    // La petición se hace a la URL del recurso específico con el método DELETE.
    return this.http.delete<ApiResponse>(`${this.USERS_API_URL}/${id}`);
  }

  /**
   * Actualiza los datos de un usuario por su ID.
   * @param id - El ID del usuario a actualizar.
   * @param userData - Un objeto con los datos del usuario a modificar.
   * @returns Un Observable con la respuesta de éxito/error del backend.
   */
  updateUser(id: string, userData: Partial<User>): Observable<ApiResponse> {
    // La petición se hace a la URL del recurso específico con el método PUT.
    return this.http.put<ApiResponse>(`${this.USERS_API_URL}/${id}`, userData);
  }


  // --- ¡NUEVOS MÉTODOS DE PRODUCTOS! ---

  /**
   * Obtiene la lista completa de todos los productos.
   * La API se encarga de que esta ruta sea pública, pero la usamos aquí para el panel.
   */
  getProducts(): Observable<ProductApiResponse> {
    return this.http.get<ProductApiResponse>(this.PRODUCTS_API_URL);
  }

  /**
   * Elimina un producto por su ID.
   * @param id - El ID del producto a eliminar.
   */
  deleteProduct(id: string): Observable<ApiResponse> {
    return this.http.delete<ApiResponse>(`${this.PRODUCTS_API_URL}/${id}`);
  }

  /**
   * Actualiza los datos de un producto por su ID.
   * @param id - El ID del producto a actualizar.
   * @param productData - Los nuevos datos del producto.
   */
  updateProduct(id: string, productData: Partial<Product>): Observable<ApiResponse> {
    return this.http.put<ApiResponse>(`${this.PRODUCTS_API_URL}/${id}`, productData);
  }

  /**
   * Crea un nuevo producto.
   * @param productData - Los datos del nuevo producto.
   */
  addProduct(productData: Partial<Product>): Observable<ApiResponse> {
    return this.http.post<ApiResponse>(this.PRODUCTS_API_URL, productData);
  }

  /**
   * Sube una imagen para un producto específico.
   * @param id El ID del producto.
   * @param imageFile El archivo de imagen a subir.
   */
  uploadProductImage(id: string, imageFile: File): Observable<{ success: boolean; message: string; url: string }> {
    const formData = new FormData();
    // La clave 'imagen' debe coincidir con la que espera el backend en $_FILES['imagen']
    formData.append('imagen', imageFile, imageFile.name);

    return this.http.post<{ success: boolean; message: string; url: string }>(
      `${this.PRODUCTS_API_URL}/${id}/imagen`,
      formData
    );
  }

  // Ahora los Pedidos

  /**
   * Obtiene la lista completa de todos los pedidos.
   * La API se encarga de que esta ruta solo sea accesible para administradores.
   */
  getOrders(): Observable<OrdersApiResponse> {
    return this.http.get<OrdersApiResponse>(this.ORDERS_API_URL);
  }

  /**
   * Obtiene los detalles completos de un pedido específico.
   */
  getOrderDetails(id: string): Observable<OrderDetail> {
    return this.http.get<OrderDetail>(`${this.ORDERS_API_URL}/${id}`);
  }

  /**
   * Actualiza el estado de un pedido.
   */
  updateOrderStatus(id: string, newStatus: string): Observable<ApiResponse> {
    return this.http.put<ApiResponse>(`${this.ORDERS_API_URL}/${id}`, { estado: newStatus });
  }
}
