export interface User {
  id: number;
  email: string;
  nom: string | null;
  prenom: string | null;
  role: string;
  dossier: { id: number; code: string; raisonSociale: string } | null;
}

export interface LoginResponse {
  token: string;
}
