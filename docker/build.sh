#!/bin/bash
chsh -s /bin/bash www-data

# Install additional tools and dependencies
apt-get update \
    && apt-get install -y --no-install-recommends unzip wget less vim git wget colordiff curl rsync ssh mariadb-client zip cron netcat nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install wp-cli
curl -L "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar" > /usr/bin/wp && \
    chmod +x /usr/bin/wp


# Install woocommerce & plugins
su -s /bin/bash www-data <<EOSU

    plugins[0]="https://downloads.wordpress.org/plugin/woocommerce.zip"
    plugins[1]="https://downloads.wordpress.org/plugin/wordpress-importer.zip"
    plugins[2]="https://downloads.wordpress.org/plugin/woocommerce-gateway-paypal-express-checkout.zip"
    plugins[3]="https://downloads.wordpress.org/plugin/woocommerce-gateway-stripe.zip"

    for plugin in ${plugins[@]}; do
        wget $plugin -O /tmp/temp.zip \
        && cd /usr/src/wordpress/wp-content/plugins \
        && unzip /tmp/temp.zip \
        && rm /tmp/temp.zip
    done;
EOSU

usermod -u 1000 www-data \
    && groupmod -g 1000 www-data
