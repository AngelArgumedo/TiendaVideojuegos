import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-footer',
  standalone: true,
  imports: [RouterLink], // Importamos RouterLink para los enlaces internos
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.scss'
})
export class FooterComponent {
  // Obtenemos el año actual dinámicamente para el copyright.
  currentYear = new Date().getFullYear();
}
