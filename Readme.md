How to Setup

1.) create a webhost for the project, webroot must be ./public of hte dir

2.) Pull project into dir

3.) setup a mysql database

4.) 
configure `config/autoload/doctrine.global.php`

5.) run from webroot `vendor/bin/doctrine-module  orm:schema-tool:update --force`

6.) run from browser <Your URL>/Setup/index


Layout based on https://startbootstrap.com/template-overviews/sb-admin-2/