import { TypeConditionnement } from './type-conditionnement.model';

export interface ArticleStock {
  id: number;
  quantite: number;
  emplacement: { id: number; code: string };
}

export interface Article {
  id: number;
  reference: string;
  libelle: string;
  description: string | null;
  gestionDlc: boolean;
  gestionNumeroSerie: boolean;
  typeConditionnement: TypeConditionnement | null;
  imagePath: string | null;
  stocks?: ArticleStock[];
}
