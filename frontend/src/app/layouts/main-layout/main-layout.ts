import { Component, signal } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive, Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-main-layout',
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './main-layout.html',
  styleUrl: './main-layout.scss'
})
export class MainLayoutComponent {
  mobileMenuOpen = signal(false);
  openGroup = signal<string | null>(null);
  profileMenuOpen = signal(false);

  constructor(public auth: AuthService, private router: Router) {
    this.router.events.pipe(filter(e => e instanceof NavigationEnd))
      .subscribe(() => { this.mobileMenuOpen.set(false); this.openGroup.set(null); this.profileMenuOpen.set(false); });
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

  isGroupActive(paths: string[]): boolean {
    return paths.some(p => this.router.url === '/' + p || this.router.url.startsWith('/' + p + '/'));
  }
}
