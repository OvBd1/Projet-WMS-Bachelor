export interface Stock {
  id: number;
  quantite: number;
  article: { id: number; reference: string; libelle: string };
  emplacement: { id: number; code: string };
}
