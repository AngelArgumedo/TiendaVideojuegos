// src/app/shared/models/order.model.ts

export interface OrdersApiResponse {
  records: Order[];
}

export interface Order {
  id: string;
  nombre_usuario: string;
  correo_usuario: string;
  subtotal: string;
  costo_envio: string;
  total: string;
  estado: 'procesando' | 'enviado' | 'completado' | 'cancelado';
  fecha_pedido: string;
}


// Interfaz para cada artículo dentro de un pedido
export interface OrderItem {
  nombre_producto: string;
  cantidad: number;
  precio_unitario: string;
}

// Interfaz para la respuesta completa de los detalles de un pedido
export interface OrderDetail {
  id: string;
  // Propiedades actualizadas para los detalles completos del cliente
  cliente_nombre: string;
  cliente_correo: string;
  cliente_direccion: string;
  cliente_pais: string;
  total: string;
  estado: 'procesando' | 'enviado' | 'completado' | 'cancelado';
  items: OrderItem[];
}
