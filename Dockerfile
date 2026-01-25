# Usa a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala extensões do PHP necessárias (MySQLi e ativamos o módulo rewrite do Apache)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite headers

# Copia a configuração de segurança para a pasta de confs do Apache (sobrescreve a original)
COPY apache-security.conf /etc/apache2/conf-available/security.conf

# Habilita a nova configuração
RUN a2enconf security

# Copia o código fonte para dentro do container
COPY . /var/www/html/BookShell

# Define permissões corretas (O Apache roda como www-data)
RUN chown -R www-data:www-data /var/www/html/BookShell