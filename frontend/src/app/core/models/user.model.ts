export interface User {
  id: number;
  email: string;
  role: string;
}

export interface LoginResponse {
  token: string;
}
