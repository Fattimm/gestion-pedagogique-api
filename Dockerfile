# Utilisez l'image php:8.3-fpm comme spécifié dans le commentaire
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
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Installation de grpc via PECL
RUN pecl install grpc && docker-php-ext-enable grpc

# Activer composer superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copier et installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances Composer avec plus de mémoire et en mode verbose
RUN php -d memory_limit=-1 /usr/bin/composer install --no-scripts --no-autoloader --no-dev --prefer-dist -vvv

# Copier le reste du contenu du projet
COPY . .

# Désactiver les scripts pour éviter les erreurs pendant le build
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --no-scripts


# Modifier les permissions
RUN chown -R www-data:www-data /var/www

# Crée le fichier firebase-key.json à partir de la variable d'environnement base64
RUN echo $FIREBASE_KEY_BASE64 | base64 -d > /var/www/firebase-key.json 

# Copier le fichier d'environnement et générer la clé
COPY .env.example .env
RUN composer install --optimize-autoloader --no-dev && \
    php artisan key:generate


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