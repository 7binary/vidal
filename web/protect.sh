chmod -R a-w css
chmod -R a-w fonts
chmod -R a-w js
chmod -R a-w sxd myadmin phpMyAdmin myAdmin cgi-bin awstats
find . -name 'cache' -type d -mtime -7 -exec chmod a+w {} \;
find . -name '*backup*' -type d -mtime -7 -exec chmod a+w {} \;
find . -name 'export' -type d -mtime -7 -exec chmod a+w {} \;
find . -name 'import' -type d -mtime -7 -exec chmod a+w {} \;
find . -name 'tmp' -type d -mtime -7 -exec chmod a+w {} \;
find . -name 'temp' -type d -mtime -7 -exec chmod a+w {} \;
find . -name 'upload*' -type d -mtime -7 -exec chmod a+w {} \;
find . -name '*.js' -exec chmod a-w {} \;
find . -name '*.css' -exec chmod a-w {} \;
find . -name '*.pl' -exec chmod a-w {} \;
find . -name '*.cgi' -exec chmod a-w {} \;
find . -name '.htaccess' -exec chmod 0444 {} \;
find . -name '*.php*' ! -mtime 1 -exec chmod a-w {} \;
find . -name 'error_log' -exec chmod a+w {} \;
find . -name '*.dat' -exec chmod a+w {} \;
find . -name '*.ini' -exec chmod a+w {} \;
find . -name '*.txt' -exec chmod a+w {} \;
find . -name '*.log' -exec chmod a+w {} \;
find . -name '*.sql' -exec chmod a+w {} \;
find . -name '*.xml' -exec chmod a+w {} \;
find . -name '*.json' -exec chmod a+w {} \;
chmod a-w `pwd`