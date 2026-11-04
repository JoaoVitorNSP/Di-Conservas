# Imagem oficial do PHP com Apache
FROM php:8.2-apache

# MySQL, GD (com JPEG e WEBP) para as fotos e EXIF para corrigir a rotação
RUN apt-get update \
    && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd exif \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite expires deflate headers

WORKDIR /var/www/html

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY . .

# O servidor só escreve na pasta de fotos
RUN mkdir -p public/assets/produtos && chown -R www-data:www-data public/assets/produtos

RUN { \
    echo "upload_max_filesize = 10M"; \
    echo "post_max_size = 12M"; \
    echo "expose_php = Off"; \
} > /usr/local/etc/php/conf.d/di-conservas.ini

EXPOSE 80

CMD ["apache2-foreground"]
