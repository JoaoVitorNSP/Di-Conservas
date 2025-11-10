# Use uma imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    default-mysql-client \
    nano \
    curl \
    wait-for-it \
    && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP necessárias
RUN docker-php-ext-install pdo_mysql zip bcmath

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Ativa módulos necessários do Apache
RUN a2enmod rewrite expires deflate headers

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos de configuração do Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copia scripts auxiliares
COPY scripts/ /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

# Copia todos os arquivos da aplicação
COPY . .

# Cria estrutura de diretórios necessária e define permissões
RUN mkdir -p assets && \
    mkdir -p storage/logs && \
    mkdir -p bootstrap/cache && \
    chmod 755 assets && \
    chmod 775 storage && \
    chmod 775 storage/logs && \
    chmod 775 bootstrap/cache && \
    touch products.json && \
    chmod 666 products.json && \
    chown -R www-data:www-data /var/www/html

# Copia arquivo .env se não existir
RUN if [ ! -f .env ]; then \
      cp .env.example .env; \
    fi

# Instala Laravel (opcional) - pode ser controlado via build arg
ARG INSTALL_LARAVEL=0
RUN if [ "$INSTALL_LARAVEL" = "1" ]; then \
      composer create-project laravel/laravel:^11.0 laravel --no-interaction --prefer-dist; \
      chown -R www-data:www-data laravel/storage laravel/bootstrap/cache; \
      chmod -R 775 laravel/storage laravel/bootstrap/cache; \
    fi

# Se existe composer.json, instala dependências
RUN if [ -f composer.json ]; then \
      composer install --no-interaction --prefer-dist --no-progress; \
    fi

# Configura PHP para upload de arquivos
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Expõe a porta 80 do Apache
EXPOSE 80

# Comando para iniciar a aplicação (aguarda MySQL e inicia Apache)
CMD ["/usr/local/bin/start-app.sh"]
