# Imagem oficial do PHP com Apache
FROM php:8.2-apache

# GD (com JPEG e WEBP) para processar as fotos de produto; EXIF para corrigir a rotação
RUN apt-get update \
    && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd exif \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite expires deflate headers

WORKDIR /var/www/html

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY . .

# O servidor só escreve no catálogo, nas fotos e na pasta de dados
RUN mkdir -p data assets/produtos \
    && chown -R www-data:www-data data assets/produtos products.json

RUN { \
    echo "upload_max_filesize = 10M"; \
    echo "post_max_size = 12M"; \
    echo "expose_php = Off"; \
} > /usr/local/etc/php/conf.d/di-conservas.ini

EXPOSE 80

CMD ["apache2-foreground"]
