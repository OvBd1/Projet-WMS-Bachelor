import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { ReceptionService } from '../../../core/services/reception.service';
import { Reception } from '../../../core/models/reception.model';
import { ConfirmService } from '../../../core/services/confirm.service';

@Component({
  selector: 'app-reception-detail',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './reception-detail.html',
  styleUrl: './reception-detail.css'
})
export class ReceptionDetailComponent implements OnInit {
  private confirm = inject(ConfirmService);

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

  async valider() {
    const ok = await this.confirm.ask({
      title: 'Valider la réception',
      message: 'Valider la réception ? Le stock sera mis à jour.',
      confirmLabel: 'Valider',
      variant: 'success'
    });
    if (!ok) return;
    this.receptionService.valider(this.reception()!.id).subscribe({
      next:  data => this.reception.set(data),
      error: err  => this.error.set(err.error?.message ?? 'Validation impossible.')
    });
  }

  async annuler() {
    const ok = await this.confirm.ask({
      title: 'Annuler la réception',
      message: this.reception()!.statut === 'VALIDEE'
        ? 'Annuler cette réception VALIDÉE ? Le stock sera décrémenté en conséquence.'
        : 'Annuler cette réception ?',
      confirmLabel: 'Annuler la réception',
      variant: 'warning'
    });
    if (!ok) return;
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

  async delete() {
    const ok = await this.confirm.ask({
      title: 'Supprimer la réception',
      message: 'Supprimer cette réception ? Cette action est irréversible.',
      confirmLabel: 'Supprimer',
      variant: 'danger'
    });
    if (!ok) return;
    this.receptionService.delete(this.reception()!.id).subscribe({
      next:  () => this.router.navigate(['/receptions']),
      error: err => this.error.set(err.error?.message ?? 'Suppression impossible.')
    });
  }

  dlcClass(dlc: string): string {
    const diff = (new Date(dlc).getTime() - Date.now()) / 86400000;
    return diff < 0 ? 'dlc-expired' : diff < 30 ? 'dlc-warning' : 'dlc-ok';
  }
}
