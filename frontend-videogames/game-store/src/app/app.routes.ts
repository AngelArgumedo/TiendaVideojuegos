// src/app/app.routes.ts
import { Routes } from '@angular/router';

// --- Importación de Guardianes ---
import { AuthGuard } from './core/guards/auth.guard';
import { AdminGuard } from './core/guards/admin.guard';

// --- Importación de todos los Componentes de las Rutas ---
import { ProductListComponent } from './features/products/product-list/product-list.component';
import { ProductDetailComponent } from './features/products/product-detail/product-detail.component';
import { LoginComponent } from './features/auth/login/login.component';
import { RegisterComponent } from './features/auth/register/register.component';
import { CartViewComponent } from './features/cart/cart-view/cart-view.component';
import { AdminPanelComponent } from './features/admin/admin-panel/admin-panel.component';
import { UserListComponent } from './features/admin/components/user-list/user-list.component';
import { ProductManagementComponent } from './features/admin/components/product-management/product-management.component';
import { OrderListComponent } from './features/admin/components/order-list/order-list.component';

export const routes: Routes = [
  { path: '', redirectTo: 'productos', pathMatch: 'full' },

  // --- Rutas de Productos ---
  { path: 'productos', component: ProductListComponent },
  { path: 'productos/categoria/:category', component: ProductListComponent },
  { path: 'productos/consola/:name', component: ProductListComponent },
  { path: 'productos/:id', component: ProductDetailComponent },

  // --- Rutas de Autenticación ---
  { path: 'login', component: LoginComponent },
  { path: 'registro', component: RegisterComponent },

  // --- Rutas Protegidas ---
  { path: 'carrito', component: CartViewComponent, canActivate: [AuthGuard] },

  // --- Rutas de Administración ---
  {
    path: 'admin',
    component: AdminPanelComponent,
    canActivate: [AuthGuard, AdminGuard],
    children: [
      { path: '', redirectTo: 'usuarios', pathMatch: 'full' },
      { path: 'usuarios', component: UserListComponent },
      { path: 'productos', component: ProductManagementComponent },
      { path: 'pedidos', component: OrderListComponent }
    ]
  },

  { path: '**', redirectTo: 'productos' }
];
