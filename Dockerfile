FROM php:7.3-apache

COPY site.conf /etc/apache2/sites-available/site.conf

RUN DEBIAN_FRONTEND=noninteractive apt-get update && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install pdo_mysql

RUN apt-get update -y && apt-get install -y sendmail libpng-dev

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev
RUN apt-get update && apt-get -y install cron

RUN docker-php-ext-install mbstring


RUN docker-php-ext-install gd



RUN mkdir /var/www/site

RUN a2enmod rewrite env headers alias && \
    a2dismod status && \
    a2dissite 000-default.conf && \
    a2ensite site.conf && \
    apachectl restart

COPY crontab.txt /etc/cron.d/hello-cron
COPY entry.sh /var/www/entry.sh


# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/hello-cron
RUN chmod +x /var/www/entry.sh

# Apply cron job
RUN crontab /etc/cron.d/hello-cron

# Create the log file to be able to run tail
RUN touch /var/log/script.log

# Run the command on container startup
CMD ["/bin/bash", "/var/www/entry.sh"]
CMD cron && tail -f /var/log/script.log

