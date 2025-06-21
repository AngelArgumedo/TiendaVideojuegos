import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../../core/services/auth.service';

// Tipo para el estado del formulario
type FormState = 'idle' | 'loading' | 'error';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, RouterLink, FormsModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent {
  // --- Inyección de Dependencias ---
  private authService = inject(AuthService);
  private router = inject(Router);

  // --- Estado del Componente ---
  // Objeto para almacenar los datos del formulario (vinculado con ngModel)
  public credentials = {
    correo: '',
    password: ''
  };

  // Señales para manejar el estado de la UI
  public formState = signal<FormState>('idle');
  public errorMessage = signal<string | null>(null);

  // --- Método de Envío del Formulario ---
  onSubmit(): void {
    // 1. Reiniciar estado y mostrar carga
    this.errorMessage.set(null);
    this.formState.set('loading');

    // 2. Llamar al servicio de autenticación
    this.authService.login(this.credentials).subscribe({
      next: (response) => {
        if (response.success) {
          // 3. Si el login es exitoso, redirigir al catálogo de productos
          this.router.navigate(['/productos']);
        } else {
          // 4. Si la API devuelve un error conocido (ej. credenciales incorrectas)
          this.formState.set('error');
          this.errorMessage.set(response.message);
        }
      },
      error: (err) => {
        // 5. Si hay un error de red o un fallo inesperado
        this.formState.set('error');
        this.errorMessage.set('Hubo un problema al conectar con el servidor. Por favor, inténtelo de nuevo.');
        console.error('Error en el login:', err);
      }
    });
  }
}
