export interface LigneReception {
  id: number;
  quantite: number;
  dlc: string | null;
  numeroSerie: string | null;
  article: {
    id: number;
    reference: string;
    libelle: string;
    gestionDlc: boolean;
    gestionNumeroSerie: boolean;
  };
  emplacement: { id: number; code: string };
}

export interface Reception {
  id: number;
  dateReception: string;
  statut: 'EN_ATTENTE' | 'VALIDEE' | 'ANNULEE';
  tiers: { id: number; nom: string; type: string; email: string | null; telephone: string | null } | null;
  utilisateur: { id: number; email: string };
  lignes: LigneReception[];
  nbLignes?: number;
}
