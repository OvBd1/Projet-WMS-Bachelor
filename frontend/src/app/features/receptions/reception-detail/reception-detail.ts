import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ReceptionService } from '../../../core/services/reception.service';
import { EmplacementService } from '../../../core/services/emplacement.service';
import { Reception, LigneReception } from '../../../core/models/reception.model';
import { Emplacement } from '../../../core/models/emplacement.model';
import { ConfirmDialogComponent } from '../../../shared/confirm-dialog/confirm-dialog';

interface ConfirmConfig {
  title: string;
  message: string;
  confirmLabel: string;
  variant: 'primary' | 'danger' | 'success' | 'warning';
  action: () => void;
}

@Component({
  selector: 'app-reception-detail',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './reception-detail.html'
})
export class ReceptionDetailComponent implements OnInit {
  reception = signal<Reception | null>(null);
  loading   = signal(true);
  error     = signal('');

  emplacements   = signal<Emplacement[]>([]);
  editingLigne   = signal<LigneReception | null>(null);
  savingLigne    = signal(false);
  ligneFormError = signal('');
  ligneForm: FormGroup;

  confirmBox     = signal<ConfirmConfig | null>(null);
  confirmLoading = signal(false);

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private receptionService: ReceptionService,
    private emplacementService: EmplacementService,
    private fb: FormBuilder
  ) {
    this.ligneForm = this.fb.group({
      emplacementId: ['', Validators.required],
      quantite:      [1, [Validators.required, Validators.min(1)]],
      dlc:           [''],
      numeroSerie:   ['']
    });
  }

  ngOnInit() {
    const id = +this.route.snapshot.paramMap.get('id')!;
    this.receptionService.getById(id).subscribe({
      next:  data => { this.reception.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Réception introuvable.'); this.loading.set(false); }
    });
    this.emplacementService.getAll().subscribe({
      next: data => this.emplacements.set(data)
    });
  }

  openLigneEdit(l: LigneReception) {
    this.editingLigne.set(l);
    this.ligneFormError.set('');
    this.ligneForm.reset({
      emplacementId: String(l.emplacement.id),
      quantite:      l.quantite,
      dlc:           l.dlc ?? '',
      numeroSerie:   l.numeroSerie ?? ''
    });

    // DLC / N° série requis selon l'article de la ligne (article non modifiable).
    const dlcCtrl = this.ligneForm.get('dlc')!;
    const nsCtrl  = this.ligneForm.get('numeroSerie')!;
    dlcCtrl.setValidators(l.article.gestionDlc ? [Validators.required] : []);
    nsCtrl.setValidators(l.article.gestionNumeroSerie ? [Validators.required] : []);
    dlcCtrl.updateValueAndValidity();
    nsCtrl.updateValueAndValidity();
  }

  closeLigneEdit() {
    this.editingLigne.set(null);
    this.savingLigne.set(false);
    this.ligneForm.reset({ emplacementId: '', quantite: 1, dlc: '', numeroSerie: '' });
  }

  submitLigne() {
    const ligne = this.editingLigne();
    const reception = this.reception();
    if (!ligne || !reception) return;
    if (this.ligneForm.invalid) { this.ligneForm.markAllAsTouched(); return; }

    this.savingLigne.set(true);
    this.ligneFormError.set('');
    const v = this.ligneForm.value;
    const payload = {
      emplacementId: +v.emplacementId,
      quantite:      +v.quantite,
      dlc:           v.dlc || null,
      numeroSerie:   v.numeroSerie || null
    };

    this.receptionService.updateLigne(reception.id, ligne.id, payload).subscribe({
      next:  data => { this.reception.set(data); this.closeLigneEdit(); },
      error: err  => { this.ligneFormError.set(err.error?.message ?? 'Modification impossible.'); this.savingLigne.set(false); }
    });
  }

  onConfirmAccept() { this.confirmBox()?.action(); }
  onConfirmCancel() { this.confirmBox.set(null); this.confirmLoading.set(false); }

  valider() {
    this.confirmBox.set({
      title: 'Valider la réception',
      message: 'Le stock sera mis à jour en conséquence. Confirmer la validation ?',
      confirmLabel: 'Valider',
      variant: 'success',
      action: () => {
        this.confirmLoading.set(true);
        this.receptionService.valider(this.reception()!.id).subscribe({
          next:  data => { this.reception.set(data); this.onConfirmCancel(); },
          error: err  => { this.error.set(err.error?.message ?? 'Validation impossible.'); this.onConfirmCancel(); }
        });
      }
    });
  }

  annuler() {
    this.confirmBox.set({
      title: 'Annuler la réception',
      message: 'Cette réception sera marquée comme annulée. Continuer ?',
      confirmLabel: 'Annuler la réception',
      variant: 'warning',
      action: () => {
        this.confirmLoading.set(true);
        this.receptionService.annuler(this.reception()!.id).subscribe({
          next:  data => { this.reception.set(data); this.onConfirmCancel(); },
          error: err  => { this.error.set(err.error?.message ?? 'Annulation impossible.'); this.onConfirmCancel(); }
        });
      }
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
