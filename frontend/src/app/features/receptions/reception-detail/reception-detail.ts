import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { ReceptionService } from '../../../core/services/reception.service';
import { Reception } from '../../../core/models/reception.model';

@Component({
  selector: 'app-reception-detail',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <div class="page-header">
      <div style="display:flex;align-items:center;gap:12px">
        <a routerLink="/receptions" class="btn btn-secondary btn-sm">← Retour</a>
        <h1 style="margin:0">
          Réception #{{ reception()?.id }}
          @if (reception()) {
            <span class="badge" [class]="badgeClass(reception()!.statut)" style="vertical-align:middle;margin-left:8px">{{ reception()!.statut }}</span>
          }
        </h1>
      </div>
      @if (reception()?.statut === 'EN_ATTENTE') {
        <div style="display:flex;gap:8px">
          <button class="btn btn-success" (click)="valider()">✓ Valider la réception</button>
          <button class="btn btn-secondary" (click)="annuler()">✕ Annuler</button>
        </div>
      }
    </div>

    @if (error()) {
      <div class="alert-error">{{ error() }}</div>
    }

    @if (loading()) {
      <div class="state-loading">
        <div class="state-loading-spinner"></div>
        <span>Chargement…</span>
      </div>
    } @else if (reception()) {
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">

        <div class="card">
          <h3 style="margin:0 0 16px;font-size:1rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.05em">Informations générales</h3>
          <dl class="info-grid">
            <dt>Date de réception</dt>
            <dd>{{ reception()!.dateReception | date:'dd/MM/yyyy' }}</dd>

            <dt>Statut</dt>
            <dd><span class="badge" [class]="badgeClass(reception()!.statut)">{{ reception()!.statut }}</span></dd>

            <dt>Créée par</dt>
            <dd>{{ reception()!.utilisateur.email }}</dd>

            <dt>Nombre de lignes</dt>
            <dd>{{ reception()!.lignes.length }}</dd>
          </dl>
        </div>

        <div class="card">
          <h3 style="margin:0 0 16px;font-size:1rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.05em">Tiers</h3>
          @if (reception()!.tiers) {
            <dl class="info-grid">
              <dt>Nom</dt>
              <dd style="font-weight:600">{{ reception()!.tiers!.nom }}</dd>

              <dt>Type</dt>
              <dd>{{ reception()!.tiers!.type }}</dd>

              @if (reception()!.tiers!.email) {
                <dt>Email</dt>
                <dd><a [href]="'mailto:' + reception()!.tiers!.email">{{ reception()!.tiers!.email }}</a></dd>
              }

              @if (reception()!.tiers!.telephone) {
                <dt>Téléphone</dt>
                <dd>{{ reception()!.tiers!.telephone }}</dd>
              }
            </dl>
          } @else {
            <p style="color:#94a3b8;margin:0">Aucun tiers associé</p>
          }
        </div>
      </div>

      <div class="table-wrap">
        <div style="padding:16px 20px;font-weight:600;border-bottom:1px solid #e2e8f0">
          Lignes de réception
        </div>
        <table>
          <thead>
            <tr>
              <th>Article</th>
              <th>Libellé</th>
              <th>Emplacement</th>
              <th>Quantité</th>
              <th>DLC</th>
              <th>N° série</th>
            </tr>
          </thead>
          <tbody>
            @for (l of reception()!.lignes; track l.id) {
              <tr>
                <td style="font-weight:600">{{ l.article.reference }}</td>
                <td>{{ l.article.libelle }}</td>
                <td>
                  <span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-family:monospace">{{ l.emplacement.code }}</span>
                </td>
                <td>{{ l.quantite }}</td>
                <td>
                  @if (l.article.gestionDlc) {
                    @if (l.dlc) {
                      <span [class]="dlcClass(l.dlc)">{{ l.dlc | date:'dd/MM/yyyy' }}</span>
                    } @else {
                      <span style="color:#ef4444;font-size:.75rem">Non renseignée</span>
                    }
                  } @else {
                    <span style="color:#94a3b8">—</span>
                  }
                </td>
                <td>
                  @if (l.article.gestionNumeroSerie) {
                    @if (l.numeroSerie) {
                      <span style="font-family:monospace;font-size:.85rem">{{ l.numeroSerie }}</span>
                    } @else {
                      <span style="color:#ef4444;font-size:.75rem">Non renseigné</span>
                    }
                  } @else {
                    <span style="color:#94a3b8">—</span>
                  }
                </td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    }

    <style>
      .card { background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px }
      .info-grid { display:grid;grid-template-columns:140px 1fr;gap:8px 16px;margin:0 }
      .info-grid dt { color:#64748b;font-size:.85rem }
      .info-grid dd { margin:0;font-size:.9rem }
      .badge { display:inline-block;padding:2px 10px;border-radius:12px;font-size:.75rem;font-weight:600 }
      .badge-warning { background:#fef3c7;color:#92400e }
      .badge-success { background:#d1fae5;color:#065f46 }
      .badge-danger  { background:#fee2e2;color:#991b1b }
      .btn-success { background:#059669;color:#fff }
      .btn-success:hover { background:#047857 }
      .dlc-ok      { color:#059669;font-weight:500 }
      .dlc-warning { color:#d97706;font-weight:500 }
      .dlc-expired { color:#ef4444;font-weight:500 }
    </style>
  `
})
export class ReceptionDetailComponent implements OnInit {
  reception = signal<Reception | null>(null);
  loading   = signal(true);
  error     = signal('');

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private receptionService: ReceptionService
  ) {}

  ngOnInit() {
    const id = +this.route.snapshot.paramMap.get('id')!;
    this.receptionService.getById(id).subscribe({
      next:  data => { this.reception.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Réception introuvable.'); this.loading.set(false); }
    });
  }

  valider() {
    if (!confirm('Valider la réception ? Le stock sera mis à jour.')) return;
    this.receptionService.valider(this.reception()!.id).subscribe({
      next:  data => this.reception.set(data),
      error: err  => this.error.set(err.error?.message ?? 'Validation impossible.')
    });
  }

  annuler() {
    if (!confirm('Annuler cette réception ?')) return;
    this.receptionService.annuler(this.reception()!.id).subscribe({
      next:  data => this.reception.set(data),
      error: err  => this.error.set(err.error?.message ?? 'Annulation impossible.')
    });
  }

  badgeClass(statut: string): string {
    return statut === 'EN_ATTENTE' ? 'badge badge-warning'
         : statut === 'VALIDEE'    ? 'badge badge-success'
         :                           'badge badge-danger';
  }

  dlcClass(dlc: string): string {
    const diff = (new Date(dlc).getTime() - Date.now()) / 86400000;
    return diff < 0 ? 'dlc-expired' : diff < 30 ? 'dlc-warning' : 'dlc-ok';
  }
}
