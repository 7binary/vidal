
#!/bin/bash

VIDAL_HOME=/home/twigavid/vidal
DIR_NAME=`date +"%Y-%m-%d_%H.%M.%S"`
PUBLIC_HTML=/home/twigavid/public_html
REPO_USERNAME=7binary
REPO_PASSWORD=Apache2php5

git clone https://$REPO_USERNAME:$REPO_PASSWORD@github.com/Vidal-ru/Vidal.git $VIDAL_HOME/$DIR_NAME

cp $VIDAL_HOME/parameters.yml $VIDAL_HOME/$DIR_NAME/app/config/parameters.yml
ln -sf $VIDAL_HOME/upload $VIDAL_HOME/$DIR_NAME/web/upload
#ln -sf $VIDAL_HOME/download $VIDAL_HOME/$DIR_NAME/web/download
ln -sf $VIDAL_HOME/netcat_files $VIDAL_HOME/$DIR_NAME/web/netcat_files
ln -sf $VIDAL_HOME/images $VIDAL_HOME/$DIR_NAME/web/images

cp $VIDAL_HOME/composer.phar $VIDAL_HOME/$DIR_NAME/composer.phar
php -d "disable_functions=" $VIDAL_HOME/$DIR_NAME/composer.phar install --no-dev --prefer-dist --no-interaction --working-dir $VIDA$

chmod 755 -R $VIDAL_HOME/$DIR_NAME/app/cache;
chmod 755 -R $VIDAL_HOME/$DIR_NAME/app/logs;
chmod 444 $VIDAL_HOME/$DIR_NAME/web/.htaccess
chmod 444 $VIDAL_HOME/$DIR_NAME/web/reprotect.php
chmod 444 $VIDAL_HOME/$DIR_NAME/web/revtest.php
chmod 444 $VIDAL_HOME/$DIR_NAME/web/soap.php
chmod 644 $VIDAL_HOME/$DIR_NAME/web/app.php
chmod 644 $VIDAL_HOME/$DIR_NAME/web/app_dev.php

read -n1 -p "Set current? [y,n]" doit

case $doit in
        y|Y)
                php $VIDAL_HOME/$DIR_NAME/app/console doctrine:schema:update --force
                php $VIDAL_HOME/$DIR_NAME/app/console doctrine:schema:update --force --em=drug
                php $VIDAL_HOME/$DIR_NAME/app/console vidal:sitemap:generate_https
                cd $PUBLIC_HTML
                unlink current
                ln -sf $VIDAL_HOME/$DIR_NAME current
                echo " +++ Using the new deployement. "
                ;;
        n|N)
                echo " --- Continuing to use previous deployment. You'll have to update 'current' "
                ;;
esac

