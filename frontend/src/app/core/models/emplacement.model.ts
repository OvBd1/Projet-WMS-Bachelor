export interface Emplacement {
  id: number;
  code: string;
  description: string | null;
  typeEmplacement: { id: number; libelle: string };
}
