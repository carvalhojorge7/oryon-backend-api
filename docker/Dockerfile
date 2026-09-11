FROM php:8.2-fpm-alpine

# Dependencias do sistema e ferramentas de compilacao
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Extensoes PHP essenciais
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    gd \
    opcache \
    zip \
    intl \
    mbstring

# Instalacao da extensao Redis
RUN pecl install redis && docker-php-ext-enable redis

# Limpeza de pacotes desnecessarios apos compilacao
RUN apk del $PHPIZE_DEPS && rm -rf /var/cache/apk/*

# Composer oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Diretorio de trabalho
WORKDIR /var/www

# Ajuste de permissoes
RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]
