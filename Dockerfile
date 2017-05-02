FROM ubuntu:14.04

RUN apt-get update && apt-get install -y \
    apache2 \
    libapache2-mod-php5 \
    php5 \
    php5-curl \
    php5-gd \
    php5-mysql \    
&& a2enmod rewrite \
&& mkdir /data \
&& chown www-data:www-data /data \
&& echo '<Directory /var/www/html/gccollab>\nOptions Indexes FollowSymLinks MultiViews\nAllowOverride All\nOrder allow,deny\nallow from all\n</Directory>\n' | sed -i '/^<VirtualHost \*:80>/r /dev/stdin' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

CMD chmod 777 /var/www/html/gccollab && apache2ctl -D FOREGROUND

# docker build -t gccollab-apache-php .
# docker run --name gccollab-mysql -e MYSQL_ROOT_PASSWORD=my-secret-pw -e MYSQL_DATABASE=gccollabdb -e MYSQL_USER=gccollab -e MYSQL_PASSWORD=password -d mysql:5.6
# docker run --name gccollab -p 8080:80 -v `pwd`:/var/www/html/gccollab --link gccollab-mysql:mysql -d gccollab-apache-php
