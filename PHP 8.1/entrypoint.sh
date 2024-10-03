#!/bin/sh

# Exit on non defined variables and on non zero exit codes
set -eu

echo '[...] Grabbing environment variables'

SERVER_ADMIN="${SERVER_ADMIN:-you@example.com}"
HTTP_SERVER_NAME="${HTTP_SERVER_NAME:-www.example.com}"
HTTPS_SERVER_NAME="${HTTPS_SERVER_NAME:-www.example.com}"
LOG_LEVEL="${LOG_LEVEL:-info}"
TZ="${TZ:-UTC}"
PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-256M}"

echo '[...] Updating configurations'

# Change Server Admin, Name, Document Root
sed -i "s/ServerAdmin\ you@example.com/ServerAdmin\ ${SERVER_ADMIN}/" /etc/apache2/httpd.conf
sed -i "s/#ServerName\ www.example.com:80/ServerName\ ${HTTP_SERVER_NAME}/" /etc/apache2/httpd.conf
sed -i 's#^DocumentRoot ".*#DocumentRoot "/app"#g' /etc/apache2/httpd.conf
sed -i 's#Directory "/var/www/localhost/htdocs"#Directory "/app"#g' /etc/apache2/httpd.conf
sed -i 's#AllowOverride None#AllowOverride All#' /etc/apache2/httpd.conf

# Change TransferLog after ErrorLog - Log to console
sed -i 's#^ErrorLog .*#ErrorLog "/dev/stderr"\nTransferLog "/dev/stdout"#g' /etc/apache2/httpd.conf
sed -i 's#CustomLog .* combined#CustomLog "/dev/stdout" combined#g' /etc/apache2/httpd.conf

# Change SSL Log locations
sed -i 's#^ErrorLog .*#ErrorLog "/dev/stderr"#g' /etc/apache2/conf.d/ssl.conf
sed -i 's#^TransferLog .*#TransferLog "/dev/stdout"#g' /etc/apache2/conf.d/ssl.conf

# Change DocumentRoot to /app
sed -i 's#^DocumentRoot ".*#DocumentRoot "/app"#g' /etc/apache2/conf.d/ssl.conf

# Change ServerAdmin and ServerName - From ENV variables
sed -i "s/ServerAdmin\ you@example.com/ServerAdmin\ ${SERVER_ADMIN}/" /etc/apache2/conf.d/ssl.conf
sed -i "s/ServerName\ www.example.com:443/ServerName\ ${HTTPS_SERVER_NAME}/" /etc/apache2/conf.d/ssl.conf

# Re-define LogLevel - From ENV variable
sed -i "s#^LogLevel .*#LogLevel ${LOG_LEVEL}#g" /etc/apache2/httpd.conf

# Enable commonly used apache modules
sed -i 's/#LoadModule\ rewrite_module/LoadModule\ rewrite_module/' /etc/apache2/httpd.conf
sed -i 's/#LoadModule\ deflate_module/LoadModule\ deflate_module/' /etc/apache2/httpd.conf
sed -i 's/#LoadModule\ expires_module/LoadModule\ expires_module/' /etc/apache2/httpd.conf

# Modify php memory limit and timezone - From ENV Variables
sed -i "s/memory_limit = .*/memory_limit = ${PHP_MEMORY_LIMIT}/" /etc/php81/php.ini
sed -i "s#^;date.timezone =\$#date.timezone = \"${TZ}\"#" /etc/php81/php.ini

echo '[...] Starting Apache'

httpd -D FOREGROUND