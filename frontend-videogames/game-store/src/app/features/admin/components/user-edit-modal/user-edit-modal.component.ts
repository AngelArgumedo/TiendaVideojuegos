import { Component, EventEmitter, Input, Output, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { User } from '../../../../shared/models/user.model';

@Component({
  selector: 'app-user-edit-modal',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './user-edit-modal.component.html',
  styleUrl: './user-edit-modal.component.scss'
})
export class UserEditModalComponent {
  // --- Entradas y Salidas del Componente ---
  // Recibe el usuario a editar. Es de tipo 'any' para poder clonarlo fácilmente.
  @Input() user: any;
  // Emite un evento cuando el usuario hace clic en "Guardar Cambios".
  @Output() save = new EventEmitter<User>();
  // Emite un evento para cerrar el modal.
  @Output() close = new EventEmitter<void>();

  // Clonamos el usuario para no modificar el original directamente.
  // Si el usuario cancela, los cambios no se guardan.
  public editableUser!: Partial<User>;

  ngOnChanges() {
    // Cada vez que el 'user' de entrada cambia, clonamos el objeto para la edición.
    if (this.user) {
      this.editableUser = { ...this.user };
    }
  }

  // --- Métodos del Componente ---
  onSaveChanges() {
    this.save.emit(this.editableUser as User);
  }

  onClose() {
    this.close.emit();
  }
}
