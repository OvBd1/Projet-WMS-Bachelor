import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DashboardService } from '../../core/services/dashboard.service';

interface Card { label: string; value: number | string; icon: string; route: string; color: string }

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.html'
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
