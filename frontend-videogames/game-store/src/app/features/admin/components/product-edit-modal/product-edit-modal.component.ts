// src/app/features/admin/components/product-edit-modal/product-edit-modal.component.ts
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Product } from '../../../../shared/models/product.model';

// Usamos Partial<Product> para permitir que el formulario de "añadir" empiece vacío.
export type ProductPayload = Partial<Product>;
export type SaveEvent = {
  data: ProductPayload;
  file: File | null;
};

@Component({
  selector: 'app-product-edit-modal',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './product-edit-modal.component.html',
  styleUrl: './product-edit-modal.component.scss'
})
export class ProductEditModalComponent implements OnInit {
  @Input() product: ProductPayload | null = null;
  @Output() save = new EventEmitter<SaveEvent>();
  @Output() close = new EventEmitter<void>();

  public editableProduct!: ProductPayload;
  public isEditMode = false;

  public selectedFile: File | null = null;
  public imagePreview: string | ArrayBuffer | null = null;

  // --- Lista de consolas para el menú desplegable ---
  public consoleOptions: string[] = ['PlayStation', 'Xbox', 'Nintendo', 'PC'];

  ngOnInit(): void {
    if (this.product && this.product.id) {
      // Si recibimos un producto, estamos en modo edición. Lo clonamos.
      this.isEditMode = true;
      this.editableProduct = { ...this.product };
      // Mostramos la imagen actual del producto como vista previa inicial.
      this.imagePreview = this.product.imagen_url || null;
    } else {
      // Si no, estamos en modo creación. Empezamos con un objeto vacío.
      this.isEditMode = false;
      this.editableProduct = {
        nombre: '',
        consola: 'PlayStation', // Valor por defecto
        categoria: 'Juego',
        precio: '0',
        stock: 0,
        descripcion: ''
      };
    }
  }

  // Se ejecuta cuando el usuario selecciona un archivo en el input
  onFileSelected(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    const file = element.files?.[0];
    if (file) {
      this.selectedFile = file;
      // Generamos una previsualización de la imagen seleccionada
      const reader = new FileReader();
      reader.onload = e => this.imagePreview = reader.result;
      reader.readAsDataURL(this.selectedFile);
    }
  }

  // Al guardar, emite tanto los datos del formulario como el archivo seleccionado.
  onSaveChanges(): void {
    this.save.emit({ data: this.editableProduct, file: this.selectedFile });
  }

  onClose(): void {
    this.close.emit();
  }
}
