// src/app/core/guards/admin.guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const AdminGuard: CanActivateFn = (route, state) => {

  const authService = inject(AuthService);
  const router = inject(Router);

  // Usamos la señal computada 'isAdmin' de nuestro AuthService.
  // Esta señal ya comprueba internamente si el usuario está logueado y si su rol es 'admin'.
  if (authService.isAdmin()) {
    return true; // El usuario es admin y puede acceder.
  } else {
    // Si no es admin, lo redirigimos a la página principal (o a una de acceso denegado).
    console.log('Admin Guard: Acceso denegado. Se requieren permisos de administrador.');
    router.navigate(['/']); // Redirigir al catálogo de productos.
    return false; // El usuario no puede acceder.
  }
};
