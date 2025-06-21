import { Component, EventEmitter, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Product } from '../../../../shared/models/product.model';

// Definimos un tipo para los datos que emitirá el modal
export type NewProductEvent = {
  data: Partial<Product>;
  file: File | null;
};

@Component({
  selector: 'app-product-add-modal',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './product-add-modal.component.html',
  styleUrl: './product-add-modal.component.scss'
})
export class ProductAddModalComponent {
  @Output() save = new EventEmitter<NewProductEvent>();
  @Output() close = new EventEmitter<void>();

  // Objeto para los datos del nuevo producto, vinculado con ngModel
  public newProduct: Partial<Product> = {
    nombre: '',
    consola: 'PlayStation', // Valor por defecto
    categoria: 'Juego',
    precio: '0',
    stock: 0,
    descripcion: ''
  };

  // Lógica para la previsualización y subida de la imagen
  public selectedFile: File | null = null;
  public imagePreview: string | ArrayBuffer | null = null;

  // Se ejecuta cuando el usuario selecciona un archivo
  onFileSelected(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    const file = element.files?.[0];
    if (file) {
      this.selectedFile = file;
      const reader = new FileReader();
      reader.onload = e => this.imagePreview = reader.result;
      reader.readAsDataURL(this.selectedFile);
    }
  }

  // Al guardar, emite tanto los datos del formulario como el archivo seleccionado
  onSaveChanges(): void {
    this.save.emit({ data: this.newProduct, file: this.selectedFile });
  }

  onClose(): void {
    this.close.emit();
  }
}
