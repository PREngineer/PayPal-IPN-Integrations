# Base Image is latest Alpine
FROM alpine:3.18

# Maintainer information and description
LABEL maintainer="Jorge Pabón <pianistapr@hotmail.com>" description="A PHP based PayPal Instant Payment Notification (IPN) integration."

# Setup Apache and PHP 8, also create the directory that will hold our application files /app
RUN apk --no-cache --update \
    add apache2 \
    apache2-ssl \
    curl \
    php81-apache2 \
    php81-bcmath \
    php81-bz2 \
    php81-calendar \
    php81-common \
    php81-ctype \
    php81-curl \
    php81-dom \
    php81-gd \
    php81-iconv \
    php81-mbstring \
    php81-mysqli \
    php81-mysqlnd \
    php81-openssl \
    php81-pdo \
    php81-pdo_dblib \
    php81-pdo_mysql \
    php81-pdo_odbc \
    php81-pdo_pgsql \
    php81-pdo_sqlite \
    php81-phar \
    php81-session \
    php81-xml \
    && mkdir /app

# Copy our application to the /app directory
COPY ./App /app
RUN chmod -R 777 /app

# Expose our web ports
EXPOSE 80

# Add the entrypoint script
ADD entrypoint.sh /
RUN ["chmod", "+x", "/entrypoint.sh"]

# Execute the entrypoint script
ENTRYPOINT ["/entrypoint.sh"]