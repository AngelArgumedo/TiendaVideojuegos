// src/app/shared/models/user.model.ts

export interface User {
  id: string;
  nombre: string;
  apellido: string;
  correo: string;
  telefono: string | null;
  direccion: string | null;
  pais: string | null;
  tipo_usuario: 'admin' | 'comprador';
  fecha_creacion: string;
}
