import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../../core/services/auth.service';

type FormState = 'idle' | 'loading' | 'error' | 'success';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, RouterLink, FormsModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})
export class RegisterComponent {
  private authService = inject(AuthService);
  private router = inject(Router);

  // --- Objeto userData actualizado para incluir todos los campos ---
  public userData = {
    nombre: '',
    apellido: '',
    correo: '',
    password: '',
    telefono: '',
    direccion: '',
    pais: '' // El valor inicial estará vacío hasta que el usuario seleccione uno
  };

  // --- Lista de países para el menú desplegable ---
  public countries: string[] = [
    'Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia', 'Ecuador',
    'España', 'Estados Unidos', 'México', 'Paraguay', 'Perú', 'Uruguay', 'Venezuela'
    // Se pueden añadir más países aquí
  ];

  public formState = signal<FormState>('idle');
  public message = signal<string | null>(null);

  onSubmit(): void {
    this.message.set(null);
    this.formState.set('loading');

    this.authService.register(this.userData).subscribe({
      next: (response) => {
        if (response.success) {
          this.formState.set('success');
          this.message.set(response.message + " Redirigiendo al login...");
          setTimeout(() => {
            this.router.navigate(['/login']);
          }, 3000);
        } else {
          this.formState.set('error');
          this.message.set(response.message);
        }
      },
      error: (err) => {
        this.formState.set('error');
        this.message.set('Hubo un problema al conectar con el servidor.');
        console.error('Error en el registro:', err);
      }
    });
  }
}
