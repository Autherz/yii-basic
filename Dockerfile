FROM php:7.4.0-fpm-alpine

MAINTAINER Sathit Seethaphon <dixonsatit@gmail.com>

ENV TIMEZONE Asia/Bangkok

RUN apk upgrade --update && apk --no-cache add \
     libzip-dev  oniguruma-dev autoconf tzdata file g++ gcc imagemagick-dev libtool binutils isl libatomic libc-dev musl-dev make re2c libstdc++ libgcc libcurl curl-dev  mpc1 mpfr3 gmp libgomp coreutils freetype-dev libjpeg-turbo-dev libltdl libmcrypt-dev libpng-dev openssl-dev libxml2-dev expat-dev \
    && docker-php-ext-install -j$(nproc) iconv mysqli pdo pdo_mysql curl bcmath mbstring json xml zip opcache \
	&& docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd

# Install PECL extensions
# see http://stackoverflow.com/a/8154466/291573) for usage of `printf`
RUN printf "\n" | pecl install \
        imagick-beta \
        mongodb && \
    docker-php-ext-enable \
        imagick \
        mongodb

# TimeZone
RUN cp /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
&& echo "${TIMEZONE}" >  /etc/timezone

# Install Composer && Assets Plugin
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
&& composer global require --no-progress "fxp/composer-asset-plugin:~1.2" \
&& apk del tzdata \
&& rm -rf /var/cache/apk/*

EXPOSE 2020

CMD ["php-fpm"]
