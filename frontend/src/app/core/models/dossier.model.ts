export interface Dossier {
  id: number;
  code: string;
  raisonSociale: string;
  rue: string | null;
  codePostal: string | null;
  ville: string | null;
  pays: string | null;
}
