import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StockService } from '../../../core/services/stock.service';
import { Stock } from '../../../core/models/stock.model';

/**
 * Consultation des stocks, en lecture seule.
 *
 * Le stock est une conséquence des mouvements (réceptions, transferts, commandes) :
 * aucune quantité n'est saisie directement.
 */
@Component({
  selector: 'app-stocks-list',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './stocks-list.html'
})
export class StocksListComponent implements OnInit {
  private stocks = signal<Stock[]>([]);
  filtered  = signal<Stock[]>([]);
  loading   = signal(false);
  error     = signal('');
  filterText = '';

  constructor(private stockService: StockService) {}

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.stockService.getAll().subscribe({
      next:  data => { this.stocks.set(data); this.applyFilter(); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les stocks.'); this.loading.set(false); }
    });
  }

  applyFilter() {
    const q = this.filterText.toLowerCase();
    this.filtered.set(q
      ? this.stocks().filter(s =>
          s.article.libelle.toLowerCase().includes(q) ||
          s.article.reference.toLowerCase().includes(q) ||
          s.emplacement.code.toLowerCase().includes(q))
      : [...this.stocks()]);
  }
}
