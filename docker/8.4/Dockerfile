FROM laravelsail/php84-composer:latest

# 必要なシステムパッケージとPHP拡張モジュールをインストール
RUN apt-get update \
    && apt-get install -y \
        libonig-dev \
        curl \
        ca-certificates \
        gnupg \
    && docker-php-ext-install mbstring pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Node.js 20.x をインストール
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# sailユーザーを作成（Laravel Sailで必要）
ARG WWWGROUP=1000
ARG WWWUSER=1000

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER sail

# 作業ディレクトリを設定
WORKDIR /var/www/html

# ポートを公開
EXPOSE 80

# sailユーザーに切り替え
USER sail

# Laravelの内蔵サーバーを起動
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
