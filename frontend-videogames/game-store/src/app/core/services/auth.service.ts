// src/app/core/services/auth.service.ts

// --- Importaciones de Angular ---
import { Injectable, inject, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';

// --- Importaciones de RxJS ---
import { Observable, of } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';

// --- Importaciones Locales (Modelos y Entorno) ---
import { environment } from '../../../environments/environment';
import { User } from '../../shared/models/user.model';

// --- Interfaces para las respuestas de la API ---
interface AuthResponse {
  success: boolean;
  message: string;
  token?: string;
}

interface LoginCredentials {
  correo: string;
  password: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  // --- Inyección de Dependencias y Constantes ---
  private http = inject(HttpClient);
  private readonly API_BASE_URL = environment.API_BASE_URL; // Usamos la variable del entorno
  private readonly TOKEN_KEY = 'auth_token';

  // --- Estado Reactivo con Signals ---
  private _currentUser = signal<User | null | undefined>(undefined);

  public currentUser = computed(() => this._currentUser());
  public isAuthenticated = computed(() => !!this._currentUser());
  public isAdmin = computed(() => this._currentUser()?.tipo_usuario === 'admin');

  constructor() {
    // La lógica del constructor no cambia
    const token = this.getToken();
    if (token) {
      const user = this.decodeToken(token);
      this._currentUser.set(user);
    } else {
      this._currentUser.set(null);
    }
  }

  // --- Métodos Públicos para Autenticación ---

  login(credentials: LoginCredentials): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.API_BASE_URL}/usuarios/login`, credentials).pipe(
      tap(response => {
        if (response.success && response.token) {
          this.saveToken(response.token);
          const user = this.decodeToken(response.token);
          this._currentUser.set(user);
        }
      }),
      catchError(err => {
        this._currentUser.set(null);
        return of(err.error);
      })
    );
  }

  logout(): void {
    this.removeToken();
    this._currentUser.set(null);
    // Aquí se podría inyectar el Router y navegar a la página de inicio
  }

  register(userInfo: any): Observable<any> {
    return this.http.post(`${this.API_BASE_URL}/usuarios/registro`, userInfo);
  }

  getToken(): string | null {
    if (typeof window !== 'undefined') {
        return localStorage.getItem(this.TOKEN_KEY);
    }
    return null;
  }

  // --- Métodos Privados ---

  private saveToken(token: string): void {
    if (typeof window !== 'undefined') {
        localStorage.setItem(this.TOKEN_KEY, token);
    }
  }

  private removeToken(): void {
    if (typeof window !== 'undefined') {
        localStorage.removeItem(this.TOKEN_KEY);
    }
  }

  private decodeToken(token: string): User | null {
    try {
      const payloadBase64 = token.split('.')[1];
      const payloadJson = atob(payloadBase64);
      const payload = JSON.parse(payloadJson);
      return payload.data as User;
    } catch (error) {
      console.error("Error al decodificar el token:", error);
      return null;
    }
  }
}
