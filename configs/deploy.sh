
#!/bin/bash

VIDAL_HOME=/home/twigavid/vidal
VIDAL_RELEASES=/home/twigavid/vidal/releases
DIR_NAME=`date +"%Y-%m-%d_%H.%M.%S"`
PUBLIC_HTML=/home/twigavid/public_html
REPO_USERNAME=
REPO_PASSWORD=

git clone https://$REPO_USERNAME:$REPO_PASSWORD@github.com/Vidal-ru/Vidal.git $VIDAL_RELEASES/$DIR_NAME

cp $VIDAL_HOME/parameters.yml $VIDAL_RELEASES/$DIR_NAME/app/config/parameters.yml
ln -sf $VIDAL_HOME/upload $VIDAL_RELEASES/$DIR_NAME/web/upload
#ln -sf $VIDAL_HOME/download $VIDAL_RELEASES/$DIR_NAME/web/download
ln -sf $VIDAL_HOME/netcat_files $VIDAL_RELEASES/$DIR_NAME/web/netcat_files
ln -sf $VIDAL_HOME/images $VIDAL_RELEASES/$DIR_NAME/web/images
#ln -sf $VIDAL_HOME/archive $VIDAL_RELEASES/$DIR_NAME/web/archive

cp $VIDAL_HOME/composer.phar $VIDAL_RELEASES/$DIR_NAME/composer.phar
php -d "disable_functions=" $VIDAL_RELEASES/$DIR_NAME/composer.phar install --prefer-dist --no-interaction --working-dir $VIDAL_RELEASES/$DIR_NAME

chmod 755 -R $VIDAL_RELEASES/$DIR_NAME/app/cache;
chmod 755 -R $VIDAL_RELEASES/$DIR_NAME/app/logs;
chmod 444 $VIDAL_RELEASES/$DIR_NAME/web/.htaccess
chmod 444 $VIDAL_RELEASES/$DIR_NAME/web/reprotect.php
chmod 444 $VIDAL_RELEASES/$DIR_NAME/web/revtest.php
chmod 444 $VIDAL_RELEASES/$DIR_NAME/web/soap.php
chmod 444 $VIDAL_RELEASES/$DIR_NAME/web/pw/.htaccess
chmod 444 $VIDAL_RELEASES/$DIR_NAME/web/pw/.htpasswd
chmod 644 $VIDAL_RELEASES/$DIR_NAME/web/app.php
chmod 644 $VIDAL_RELEASES/$DIR_NAME/web/app_dev.php

read -n1 -p "Set current? [y,n]" doit

case $doit in
	y|Y)
		php $VIDAL_RELEASES/$DIR_NAME/app/console doctrine:schema:update --force
		php $VIDAL_RELEASES/$DIR_NAME/app/console doctrine:schema:update --force --em=drug
		php $VIDAL_RELEASES/$DIR_NAME/app/console vidal:generator_atc
		php $VIDAL_RELEASES/$DIR_NAME/app/console vidal:generator_kfu
		php $VIDAL_RELEASES/$DIR_NAME/app/console vidal:generator_nozology
		php $VIDAL_RELEASES/$DIR_NAME/app/console vidal:sitemap:generate_https
        cd $PUBLIC_HTML
        unlink current
        ln -sf $VIDAL_RELEASES/$DIR_NAME current
        echo "+++ Using the new deployement. "

        sleep 3
        wget -qO- https://www.vidal.ru/app_dev.php/opcache-reset &> /dev/null
        echo "+++ OPCACHE_RESET"
		;;
	n|N)
       	echo "--- Continuing to use previous deployment. You'll have to update 'current' "
		;;
esac



