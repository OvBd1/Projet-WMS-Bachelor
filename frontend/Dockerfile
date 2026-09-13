# syntax=docker/dockerfile:1

# ---------- Build Angular ----------
FROM node:22-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
# Build production (defaultConfiguration=production dans angular.json -> apiUrl '/api')
RUN npm run build

# ---------- Runtime nginx ----------
FROM nginx:1.27-alpine AS runtime

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist/logitrack-frontend/browser /usr/share/nginx/html

EXPOSE 80
