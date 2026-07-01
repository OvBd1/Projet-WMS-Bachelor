export type StatutCommande = 'EN_ATTENTE' | 'PREPAREE' | 'EXPEDIEE' | 'ANNULEE';

export interface LigneCommande {
  id: number;
  quantite: number;
  article: { id: number; reference: string; libelle: string };
}

export interface Commande {
  id: number;
  numeroCommande: string;
  dateCommande: string;
  dateExpedition: string | null;
  statut: StatutCommande;
  tiers: { id: number; nom: string; type: string; email?: string | null } | null;
  utilisateur: { id: number; email: string };
  lignes: LigneCommande[];
  nbLignes?: number;
}
