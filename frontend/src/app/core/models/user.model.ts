export interface User {
  id: number;
  email: string;
  nom: string | null;
  prenom: string | null;
  role: string;
}

export interface LoginResponse {
  token: string;
}
