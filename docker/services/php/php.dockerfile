FROM php:8.3-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000
ARG USER_NAME=luciano
ARG GROUP_NAME=app

RUN groupadd -g ${GROUP_ID} ${GROUP_NAME} \
    && useradd -u ${USER_ID} -g ${GROUP_NAME} -m ${USER_NAME}

WORKDIR /var/www

RUN apt-get update && apt-get install -y --no-install-recommends \
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
    cron \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring bcmath exif pdo_mysql intl zip pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
    
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install -y gnupg ca-certificates \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN echo "* * * * * ${USER_NAME} /usr/local/bin/php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1" > /etc/cron.d/laravel-scheduler \
    && chmod 0644 /etc/cron.d/laravel-scheduler \
    && crontab -u ${USER_NAME} /etc/cron.d/laravel-scheduler \
    && touch /var/log/cron.log \
    && chown ${USER_NAME}:${GROUP_NAME} /var/log/cron.log

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R ${USER_NAME}:${GROUP_NAME} /var/www

RUN sed -i "s/user = www-data/user = ${USER_NAME}/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = ${GROUP_NAME}/g" /usr/local/etc/php-fpm.d/www.conf

RUN echo "[www]\nlisten = /var/run/php-fpm/php-fpm.sock\nlisten.mode = 0666\n" > /usr/local/etc/php-fpm.d/zz-socket.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]