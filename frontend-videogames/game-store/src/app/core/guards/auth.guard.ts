// src/app/core/guards/auth.guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

// Este es un Guardia Funcional, el enfoque moderno en Angular.
export const AuthGuard: CanActivateFn = (route, state) => {

  // Inyectamos los servicios que necesitamos dentro de la función.
  const authService = inject(AuthService);
  const router = inject(Router);

  // Usamos la señal computada 'isAuthenticated' de nuestro AuthService.
  // Si devuelve 'true', el usuario está logueado.
  if (authService.isAuthenticated()) {
    return true; // El usuario puede acceder a la ruta.
  } else {
    // Si el usuario no está logueado, lo redirigimos a la página de login.
    console.log('Auth Guard: Acceso denegado. Redirigiendo a /login');
    router.navigate(['/login']);
    return false; // El usuario no puede acceder a la ruta.
  }
};
