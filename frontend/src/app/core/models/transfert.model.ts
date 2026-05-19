export interface Transfert {
  id: number;
  dateTransfert: string;
  quantite: number;
  utilisateur: { id: number; email: string };
  article: { id: number; reference: string; libelle: string };
  emplacementSource: { id: number; code: string };
  emplacementDestination: { id: number; code: string };
}
