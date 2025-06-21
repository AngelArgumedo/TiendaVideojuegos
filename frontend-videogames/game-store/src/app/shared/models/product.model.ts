// src/app/shared/models/product.model.ts

export interface Product {
  id: string;
  nombre: string;
  descripcion: string;
  precio: string; // La API lo devuelve como string, podemos convertirlo a número si es necesario.
  categoria: 'Juego' | 'Consola' | 'Tarjeta' | 'Figura';
  consola: string;
  stock: number;
  imagen_url: string;
}

// A menudo, la API devuelve una respuesta anidada.
// Esta interfaz representa la estructura completa de la respuesta de la API.
export interface ProductApiResponse {
  records: Product[];
}
