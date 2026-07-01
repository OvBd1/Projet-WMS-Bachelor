import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { FormsModule } from '@angular/forms';
import { StockService } from '../../../core/services/stock.service';
import { Stock } from '../../../core/models/stock.model';

@Component({
  selector: 'app-stocks-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, FormsModule],
  templateUrl: './stocks-list.html'
})
export class StocksListComponent implements OnInit {
  private stocks = signal<Stock[]>([]);
  filtered  = signal<Stock[]>([]);
  loading   = signal(false);
  error     = signal('');
  editingId = signal<number | null>(null);
  filterText = '';
  qtyControl!: FormControl<number | null>;

  constructor(private stockService: StockService, private fb: FormBuilder) {
    this.qtyControl = this.fb.control(0, [Validators.required, Validators.min(0)]);
  }

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

  startEdit(s: Stock) {
    this.editingId.set(s.id);
    this.qtyControl.setValue(s.quantite);
  }

  cancelEdit() { this.editingId.set(null); }

  saveQty(s: Stock) {
    if (this.qtyControl.invalid) return;
    const qty = this.qtyControl.value as number;
    this.stockService.patch(s.id, qty).subscribe({
      next:  updated => { s.quantite = updated.quantite; this.editingId.set(null); },
      error: ()      => this.error.set('Erreur lors de la mise à jour du stock.')
    });
  }
}
