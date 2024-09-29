# Utilisez l'image php:8.3-fpm
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    apt-utils \
    libz-dev \
    libssl-dev \
    pkg-config \
    libcurl4-openssl-dev \
    libgmp-dev \
    libgrpc-dev \
    && rm -rf /var/lib/apt/lists/*

# Installation de grpc via PECL
RUN pecl install grpc && docker-php-ext-enable grpc

# Activer composer superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copier et installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le contenu du projet
COPY . .

# Modifier les permissions
RUN chown -R www-data:www-data /var/www

# Installer les dépendances Composer
RUN composer install

# Crée le fichier firebase-key.json à partir de la variable d'environnement base64
RUN echo $FIREBASE_KEY_BASE64 | base64 -d > /var/www/firebase-key.json 

# Copier le fichier d'environnement et générer la clé
COPY .env.example .env
RUN php artisan key:generate

# Configurer les permissions sur le stockage et le cache
RUN chown -R www-data:www-data /var/www/storage \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/vendor \
    && chmod -R 755 /var/www/vendor

# Exposer le port
EXPOSE $PORT

# Copie le script de démarrage
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Commande pour démarrer l'application
CMD php artisan serve --host=0.0.0.0 --port=$PORT