export interface Tiers {
  id: number;
  nom: string;
  type: 'FOURNISSEUR' | 'CLIENT' | 'AUTRE';
  email: string | null;
  telephone: string | null;
  adresse: string | null;
}
