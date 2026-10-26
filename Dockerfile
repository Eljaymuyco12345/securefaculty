# SecureFaculty Production-Grade Container
# PHP 8.2 Apache + Python 3 Conversion Gate & Poppler/Tesseract OCR
# ISUFST - College of Informatics and Computing Innovations (CICI)

FROM php:8.2-apache

# 1. Install System Dependencies, Python 3, Poppler & Tesseract OCR
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    python3-pip \
    python-is-python3 \
    poppler-utils \
    tesseract-ocr \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    curl \
    mariadb-client \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo pdo_mysql gd zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install Python Packages for Smart OCR Pipeline & PDF Generation
RUN pip3 install --no-cache-dir requests pillow pdf2image pytesseract reportlab --break-system-packages

# 3. Configure Apache Modules & Custom Settings
RUN a2enmod rewrite headers

# 4. Copy Apache VirtualHost & PHP Settings
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/securefaculty.ini

# 5. Set Working Directory
WORKDIR /var/www/html

# 6. Copy Application Source Code
COPY . /var/www/html

# 7. Configure Permissions for Encrypted Vault & Temp Staging
RUN mkdir -p /var/www/html/storage/vault /var/www/html/storage/temp \
 && chown -R www-data:www-data /var/www/html/storage \
 && chmod -R 775 /var/www/html/storage \
 && ln -sf /var/www/html /var/www/html/securefaculty

# 8. Expose HTTP Port
EXPOSE 80

# 9. Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -f http://localhost/ || exit 1

CMD ["apache2-foreground"]
