FROM php:zts-alpine
ENV TZ Asia/Shanghai

RUN apk update && apk add make curl gcc g++ git libzip-dev autoconf nss mediainfo openssl-dev neofetch
RUN docker-php-ext-install sockets zip bcmath
RUN cd /tmp && \
    git clone https://github.com/swoole/swoole-src.git && \
    cd swoole-src && \
    phpize && \
    ./configure --enable-openssl --enable-sockets --enable-mysqlnd && \
    make && make install && \
    docker-php-ext-enable swoole.so && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apk del git gcc g++ curl autoconf nss make && \
    rm -rf /tmp/*
ENTRYPOINT [ "/bin/sh" ]
ENV PS1="\h:\w\$ "
