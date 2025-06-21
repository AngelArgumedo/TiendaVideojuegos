// src/app/features/admin/components/order-detail-modal/order-detail-modal.component.ts
import { Component, EventEmitter, Input, OnInit, Output, inject, signal } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../../core/services/admin.service';
import { OrderDetail } from '../../../../shared/models/order.model';

@Component({
  selector: 'app-order-detail-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, CurrencyPipe],
  templateUrl: './order-detail-modal.component.html',
  styleUrl: './order-detail-modal.component.scss'
})
export class OrderDetailModalComponent implements OnInit {
  @Input({ required: true }) orderId!: string;
  @Output() close = new EventEmitter<void>();
  @Output() statusUpdated = new EventEmitter<void>();

  private adminService = inject(AdminService);

  public orderDetails = signal<OrderDetail | null>(null);
  public isLoading = signal<boolean>(true);
  public selectedStatus: string = '';

  ngOnInit(): void {
    this.adminService.getOrderDetails(this.orderId).subscribe(details => {
      this.orderDetails.set(details);
      this.selectedStatus = details.estado;
      this.isLoading.set(false);
    });
  }

  onUpdateStatus(): void {
    if (!this.orderDetails()) return;
    this.adminService.updateOrderStatus(this.orderDetails()!.id, this.selectedStatus).subscribe(() => {
      this.statusUpdated.emit();
    });
  }

  onClose(): void {
    this.close.emit();
  }
}
