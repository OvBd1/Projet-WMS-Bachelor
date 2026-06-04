export type StatutCommande = 'EN_ATTENTE' | 'PREPAREE' | 'EXPEDIEE' | 'ANNULEE';

export interface LigneCommande {
  id: number;
  quantite: number;
  article: { id: number; reference: string; libelle: string };
}

export interface Commande {
  id: number;
  dateCommande: string;
  statut: StatutCommande;
  tiers: { id: number; nom: string; type: string } | null;
  utilisateur: { id: number; email: string };
  lignes?: LigneCommande[];
  nbLignes?: number;
}
