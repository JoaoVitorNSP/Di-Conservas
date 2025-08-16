# Use uma imagem oficial do PHP com Apache
FROM php:8.2-apache

# Ativa módulos necessários do Apache
RUN a2enmod rewrite expires deflate headers

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos de configuração do Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copia todos os arquivos da aplicação
COPY . .

# Cria diretório assets e define permissões
RUN mkdir -p assets && \
    chmod 755 assets && \
    touch products.json && \
    chmod 666 products.json && \
    chown -R www-data:www-data /var/www/html

# Configura PHP para upload de arquivos
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Expõe a porta 80 do Apache
EXPOSE 80

# Comando para iniciar o Apache
CMD ["apache2-foreground"]
