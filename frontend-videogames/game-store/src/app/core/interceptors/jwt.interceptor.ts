import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

export const jwtInterceptor: HttpInterceptorFn = (req, next) => {
  // Inyectamos el servicio de autenticación.
  const authService = inject(AuthService);
  const token = authService.getToken();

  // Si tenemos un token, clonamos la petición y le añadimos la cabecera de autorización.
  if (token) {
    const clonedRequest = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    // Pasamos la petición clonada y modificada al siguiente manejador.
    return next(clonedRequest);
  }

  // Si no hay token, simplemente dejamos que la petición original continúe sin modificaciones.
  return next(req);
};
