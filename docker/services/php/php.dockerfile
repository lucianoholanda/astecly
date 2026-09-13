FROM php:8.3-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000
ARG USER_NAME=luciano
ARG GROUP_NAME=app

# Criar grupo e usuário com IDs mapeados do WSL
RUN groupadd -g ${GROUP_ID} ${GROUP_NAME} \
    && useradd -u ${USER_ID} -g ${GROUP_NAME} -m ${USER_NAME}

WORKDIR /var/www

# Instalar pacotes do sistema e extensões em uma única camada otimizada
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    git \
    jpegoptim optipng pngquant gifsicle \
    locales \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    vim \
    cron \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring bcmath exif pdo_mysql intl zip pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar Node.js 20.x e dependências (Correção do gnupg)
RUN apt-get update && apt-get install -y gnupg ca-certificates \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar cron job para o Laravel (Rodando como o usuário criado)
RUN echo "* * * * * ${USER_NAME} /usr/local/bin/php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1" > /etc/cron.d/laravel-scheduler \
    && chmod 0644 /etc/cron.d/laravel-scheduler \
    && crontab -u ${USER_NAME} /etc/cron.d/laravel-scheduler \
    && touch /var/log/cron.log \
    && chown ${USER_NAME}:${GROUP_NAME} /var/log/cron.log

# Copiar arquivo de configuração do supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ajustar permissões da pasta de trabalho
RUN chown -R ${USER_NAME}:${GROUP_NAME} /var/www

# Configura o PHP-FPM para usar o usuário luciano em vez do www-data padrão
RUN sed -i "s/user = www-data/user = ${USER_NAME}/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = ${GROUP_NAME}/g" /usr/local/etc/php-fpm.d/www.conf

# IMPORTANTE: Mantemos como root para o entrypoint conseguir subir o cron e o supervisor,
# mas os processos filhos rodarão como ${USER_NAME} conforme o supervisord.conf
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]