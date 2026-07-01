export const environment = {
  // API servie en same-origin par nginx (dev et prod Docker) : http://localhost:8080/api
  // -> URL relative pour ne pas dépendre du port/host.
  production: false,
  apiUrl: '/api'
};
