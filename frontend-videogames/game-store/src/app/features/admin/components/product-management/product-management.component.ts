// src/app/features/admin/components/product-management/product-management.component.ts
import { Component, inject, signal, DestroyRef } from '@angular/core';
import { CommonModule, CurrencyPipe, DatePipe } from '@angular/common';
import { AdminService } from '../../../../core/services/admin.service';
import { Product } from '../../../../shared/models/product.model';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { environment } from '../../../../../environments/environment';
import { of } from 'rxjs';
import { switchMap } from 'rxjs/operators';

// Importamos ambos modales y sus tipos de datos necesarios
import { ProductEditModalComponent, ProductPayload as EditPayload, SaveEvent as EditEvent } from '../product-edit-modal/product-edit-modal.component';
import { ProductAddModalComponent, NewProductEvent } from '../product-add-modal/product-add-modal.component';

type ComponentState = {
  products: Product[];
  loading: boolean;
  error: string | null;
};

@Component({
  selector: 'app-product-management',
  standalone: true,
  imports: [CommonModule, DatePipe, CurrencyPipe, ProductEditModalComponent, ProductAddModalComponent],
  templateUrl: './product-management.component.html',
  styleUrl: './product-management.component.scss'
})
export class ProductManagementComponent {
  private adminService = inject(AdminService);
  private destroyRef = inject(DestroyRef);

  public state = signal<ComponentState>({ products: [], loading: true, error: null });

  // Señales separadas para cada modal
  public editingProduct = signal<EditPayload | null>(null);
  public isAddingProduct = signal<boolean>(false);

  constructor() {
    this.loadProducts();
  }

  loadProducts(): void {
    this.state.set({ products: [], loading: true, error: null });
    this.adminService.getProducts()
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (response) => {
          const productsWithFullUrl = response.records.map(product => {
            const productCopy = { ...product };
            if (productCopy.imagen_url && !productCopy.imagen_url.startsWith('http')) {
              const baseUrl = environment.API_BASE_URL.replace(/\/api\/?$/, '');
              productCopy.imagen_url = `${baseUrl}${productCopy.imagen_url}`;
            }
            return productCopy;
          });
          this.state.update(s => ({ ...s, products: productsWithFullUrl, loading: false }));
        },
        error: (err) => {
          this.state.update(s => ({ ...s, error: 'No se pudieron cargar los productos.', loading: false }));
          console.error(err);
        }
      });
  }

  // --- Lógica para el Modal de Edición ---
  onOpenEditModal(product: EditPayload): void {
    this.editingProduct.set({ ...product });
  }

  onCloseEditModal(): void {
    this.editingProduct.set(null);
  }

  onSaveEdit(event: EditEvent): void {
    const { data: productData, file } = event;
    if (!productData.id) return;

    this.adminService.updateProduct(productData.id, productData).pipe(
      takeUntilDestroyed(this.destroyRef),
      switchMap(response => {
        if (file && response.success) {
          return this.adminService.uploadProductImage(productData.id!, file);
        }
        return of(response);
      })
    ).subscribe({
      next: () => {
        this.loadProducts();
        this.onCloseEditModal();
      },
      error: (err) => alert(err.error?.message || 'Error en el servidor.')
    });
  }

  // --- Lógica para el Modal de Añadir ---
  onOpenAddModal(): void {
    this.isAddingProduct.set(true);
  }

  onCloseAddModal(): void {
    this.isAddingProduct.set(false);
  }

  onSaveNewProduct(event: NewProductEvent): void {
    const { data: productData, file } = event;

    this.adminService.addProduct(productData).pipe(
      takeUntilDestroyed(this.destroyRef),
      // --- ¡LÓGICA CORREGIDA! ---
      // Encadenamos la subida de la imagen después de crear el producto.
      switchMap((response) => {
        // Si hay un archivo, la creación fue exitosa y la API nos devolvió el nuevo ID...
        if (file && response.success && response.id) {
          // ...entonces procedemos a subir la imagen usando ese nuevo ID.
          return this.adminService.uploadProductImage(response.id, file);
        }
        // Si no, simplemente pasamos la respuesta original.
        return of(response);
      })
    ).subscribe({
      next: (res) => {
        if(res.success){
          this.loadProducts();
          this.onCloseAddModal();
        } else {
           alert(res.message || 'No se pudo crear el producto.');
        }
      },
      error: (err) => alert(err.error?.message || 'Error en el servidor.')
    });
  }

  // --- Lógica para Eliminar ---
  onDeleteProduct(product: Product): void {
    if (confirm(`¿Estás seguro de que quieres eliminar "${product.nombre}"?`)) {
      this.adminService.deleteProduct(product.id).subscribe({
        next: () => this.loadProducts(),
        error: (err) => alert(err.error?.message || 'Error en el servidor.')
      });
    }
  }
}
