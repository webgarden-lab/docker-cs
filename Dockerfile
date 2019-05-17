
FROM webgarden/php

WORKDIR /var/docker

# Add Composer's dependency's
ADD composer.json /var/docker/composer.json
RUN composer install


## Add Slevomat Standard to PHPCS
RUN ln -s /var/docker/vendor/slevomat/coding-standard/SlevomatCodingStandard /var/docker/vendor/squizlabs/php_codesniffer/src/Standards/SlevomatCodingStandard


# Add own standard
ADD standard/WGCS /var/docker/vendor/squizlabs/php_codesniffer/src/Standards/WGCS

RUN echo "PHP Info"
RUN php -v
RUN echo "Installed Standards:"
RUN vendor/bin/phpcs -i
RUN echo "Slevomat Sniffs:"
RUN vendor/bin/phpcs -se --standard=SlevomatCodingStandard
RUN echo "WGCS Sniffs:"
RUN vendor/bin/phpcs -se --standard=WGCS

ENTRYPOINT ["php", "vendor/bin/phpcs", "--standard=WGCS"]
