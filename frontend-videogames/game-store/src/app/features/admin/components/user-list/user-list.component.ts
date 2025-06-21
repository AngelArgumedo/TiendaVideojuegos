import { Component, inject, signal } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { AdminService } from '../../../../core/services/admin.service';
import { User } from '../../../../shared/models/user.model';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { UserEditModalComponent } from '../user-edit-modal/user-edit-modal.component';

type ComponentState = {
  users: User[];
  loading: boolean;
  error: string | null;
};

@Component({
  selector: 'app-user-list',
  standalone: true,
  // Se elimina UserAddModalComponent de las importaciones
  imports: [CommonModule, DatePipe, UserEditModalComponent],
  templateUrl: './user-list.component.html',
  styleUrl: './user-list.component.scss'
})
export class UserListComponent {
  private adminService = inject(AdminService);

  public state = signal<ComponentState>({
    users: [],
    loading: true,
    error: null,
  });

  // Solo mantenemos la señal para el modal de edición
  public editingUser = signal<User | null>(null);

  constructor() {
    this.loadUsers();
  }

  loadUsers(): void {
    this.state.set({ users: [], loading: true, error: null });
    this.adminService.getUsers().pipe(takeUntilDestroyed()).subscribe({
      next: (response) => this.state.update(s => ({ ...s, users: response.records, loading: false })),
      error: (err) => {
        this.state.update(s => ({ ...s, error: 'No se pudieron cargar los usuarios.', loading: false }));
        console.error(err);
      }
    });
  }

  // --- MÉTODOS PARA EL MODAL DE EDICIÓN ---
  onEditUser(user: User): void {
    this.editingUser.set(user);
  }

  onCloseEditModal(): void {
    this.editingUser.set(null);
  }

  onSaveChanges(updatedUser: User): void {
    if (!updatedUser || !updatedUser.id) return;
    this.adminService.updateUser(updatedUser.id, updatedUser).subscribe({
      next: (response) => {
        if (response.success) {
          this.state.update(current => ({
            ...current,
            users: current.users.map(u => u.id === updatedUser.id ? updatedUser : u)
          }));
          this.onCloseEditModal();
        } else {
          alert(`Error al actualizar: ${response.message}`);
        }
      },
      error: (err) => {
        alert('Hubo un error al conectar con el servidor.');
        console.error(err);
      }
    });
  }

  // --- MÉTODO PARA ELIMINAR ---
  onDeleteUser(user: User): void {
    if (confirm(`¿Estás seguro de que quieres eliminar al usuario "${user.nombre} ${user.apellido}"?`)) {
      this.adminService.deleteUser(user.id).subscribe({
        next: (response) => {
          if (response.success) {
            this.state.update(current => ({
              ...current,
              users: current.users.filter(u => u.id !== user.id)
            }));
          } else {
            alert(`Error al eliminar: ${response.message}`);
          }
        },
        error: (err) => {
          alert('Hubo un error al conectar con el servidor.');
          console.error(err);
        }
      });
    }
  }
}
