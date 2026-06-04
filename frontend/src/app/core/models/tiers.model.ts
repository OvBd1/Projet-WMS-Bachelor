export interface Tiers {
  id: number;
  code: string;
  nom: string;
  type: 'FOURNISSEUR' | 'CLIENT' | 'AUTRE';
  rue: string | null;
  codePostal: string | null;
  ville: string | null;
  pays: string | null;
}
