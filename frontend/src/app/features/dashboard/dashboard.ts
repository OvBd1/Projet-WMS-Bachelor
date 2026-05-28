import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DashboardService } from '../../core/services/dashboard.service';

interface Card { label: string; value: number | string; icon: string; route: string; color: string }

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  template: `
    <div class="page-header">
      <h1>Dashboard</h1>
    </div>

    <div class="cards-grid">
      @if (loading()) {
        @for (_ of [1,2,3,4,5,6]; track $index) {
          <div class="card" style="min-height:110px;animation:pulse 1.2s ease-in-out infinite alternate">
            <div style="height:12px;width:60%;background:#e2e8f0;border-radius:4px;margin-bottom:1rem"></div>
            <div style="height:32px;width:40%;background:#e2e8f0;border-radius:4px"></div>
          </div>
        }
      } @else {
        @for (c of cards(); track c.label) {
          <a [routerLink]="c.route" style="text-decoration:none">
            <div class="card" [style.border-left]="'4px solid ' + c.color">
              <div class="card-icon">{{ c.icon }}</div>
              <div class="card-value">{{ c.value }}</div>
              <div class="card-label">{{ c.label }}</div>
            </div>
          </a>
        }
      }
    </div>

    <div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,.08)">
      <h2 style="margin:0 0 1rem;font-size:1rem;color:#475569">Accès rapide</h2>
      <div style="display:flex;flex-wrap:wrap;gap:.5rem">
        <a routerLink="/receptions" class="btn btn-primary">Nouvelle réception</a>
        <a routerLink="/commandes"  class="btn btn-secondary">Nouvelle commande</a>
        <a routerLink="/transferts" class="btn btn-secondary">Transfert de stock</a>
        <a routerLink="/articles"   class="btn btn-secondary">Gérer les articles</a>
      </div>
    </div>
  `
})
export class DashboardComponent implements OnInit {
  loading = signal(false);
  cards = signal<Card[]>([
    { label: 'Articles',     value: '—', icon: '📦', route: '/articles',    color: '#1E3A5F' },
    { label: 'Emplacements', value: '—', icon: '🗂️', route: '/emplacements', color: '#8b5cf6' },
    { label: 'Stocks',       value: '—', icon: '📊', route: '/stocks',      color: '#27AE60' },
    { label: 'Réceptions',   value: '—', icon: '📥', route: '/receptions',  color: '#F39C12' },
    { label: 'Commandes',    value: '—', icon: '🛒', route: '/commandes',   color: '#06b6d4' },
    { label: 'Transferts',   value: '—', icon: '🔄', route: '/transferts',  color: '#E74C3C' },
  ]);

  constructor(private dashboardService: DashboardService) {}

  ngOnInit() {
    this.loading.set(true);
    this.dashboardService.getStats().subscribe({
      next: stats => {
        this.cards.set([
          { label: 'Articles',     value: stats.articles,     icon: '📦', route: '/articles',    color: '#1E3A5F' },
          { label: 'Emplacements', value: stats.emplacements, icon: '🗂️', route: '/emplacements', color: '#8b5cf6' },
          { label: 'Stocks',       value: stats.stocks,       icon: '📊', route: '/stocks',      color: '#27AE60' },
          { label: 'Réceptions',   value: stats.receptions,   icon: '📥', route: '/receptions',  color: '#F39C12' },
          { label: 'Commandes',    value: stats.commandes,    icon: '🛒', route: '/commandes',   color: '#06b6d4' },
          { label: 'Transferts',   value: stats.transferts,   icon: '🔄', route: '/transferts',  color: '#E74C3C' },
        ]);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }
}
