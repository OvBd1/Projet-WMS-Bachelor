import { Component, OnInit, signal } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive, Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { AuthService } from '../../core/services/auth.service';
import { DossierService } from '../../core/services/dossier.service';
import { DossierContextService } from '../../core/services/dossier-context.service';
import { Dossier } from '../../core/models/dossier.model';

@Component({
  selector: 'app-main-layout',
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './main-layout.html',
  styleUrl: './main-layout.scss'
})
export class MainLayoutComponent implements OnInit {
  mobileMenuOpen = signal(false);
  openGroup = signal<string | null>(null);
  profileMenuOpen = signal(false);
  dossierMenuOpen = signal(false);
  dossiers = signal<Dossier[]>([]);

  constructor(
    public auth: AuthService,
    public dossierContext: DossierContextService,
    private dossierService: DossierService,
    private router: Router
  ) {
    this.router.events.pipe(filter(e => e instanceof NavigationEnd))
      .subscribe(() => {
        this.mobileMenuOpen.set(false);
        this.openGroup.set(null);
        this.profileMenuOpen.set(false);
        this.dossierMenuOpen.set(false);
      });
  }

  ngOnInit(): void {
    if (!this.auth.isAdmin()) return;

    this.dossierService.getAll().subscribe({
      next: data => this.dossiers.set(data)
    });
  }

  logout(): void {
    this.auth.logout();
  }

  toggleMobileMenu(): void {
    this.mobileMenuOpen.update(v => !v);
  }

  toggleGroup(name: string): void {
    this.openGroup.update(g => g === name ? null : name);
  }

  toggleProfileMenu(): void {
    this.profileMenuOpen.update(v => !v);
  }

  toggleDossierMenu(): void {
    this.dossierMenuOpen.update(v => !v);
  }

  selectDossier(id: number): void {
    this.dossierContext.setCurrentDossier(id);
    this.dossierMenuOpen.set(false);
    window.location.reload();
  }

  currentDossierLabel(): string {
    const id = this.dossierContext.currentDossierId();
    const dossier = this.dossiers().find(d => d.id === id);
    return dossier ? dossier.raisonSociale : 'Sélectionner un dossier';
  }

  isGroupActive(paths: string[]): boolean {
    return paths.some(p => this.router.url === '/' + p || this.router.url.startsWith('/' + p + '/'));
  }
}
