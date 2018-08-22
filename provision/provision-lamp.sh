#!/bin/bash

# https://github.com/mattandersen/vagrant-lamp

apache_config_file="/etc/apache2/envvars"
apache_vhost_file="/etc/apache2/sites-available/vagrant_vhost.conf"
php_config_file="/etc/php5/apache2/php.ini"
xdebug_config_file="/etc/php5/mods-available/xdebug.ini"
mysql_config_file="/etc/mysql/my.cnf"
default_apache_index="/var/www/html/index.html"

# This function is called at the very bottom of the file
main() {
	repositories_go
	locale_go
	update_go
	network_go
	tools_go
	apache_go
	php_go
	phpcomposer_go
	mysql_go
	autoremove_go
}

repositories_go() {
	echo "NOOP"
}

update_go() {
    #
    # for php 7
    #
    sudo add-apt-repository ppa:ondrej/php

	# Update the server
	apt-get update

	sudo apt-get install beanstalkd
}

autoremove_go() {
	apt-get -y autoremove
}

network_go() {
	IPADDR=$(/sbin/ifconfig eth0 | awk '/inet / { print $2 }' | sed 's/addr://')
	sed -i "s/^${IPADDR}.*//" /etc/hosts
	echo ${IPADDR} ubuntu.localhost >> /etc/hosts			# Just to quiet down some error messages
}

tools_go() {
	# Install basic tools
	apt-get -y install build-essential binutils-doc git subversion
}

locale_go() {
    # switch Locale
    locale-gen de_DE.UTF-8
    update-locale LANG=de_DE.UTF-8
    dpkg-reconfigure locales
}

apache_go() {
	# Install Apache
	apt-get -y install apache2

	sed -i "s/^\(.*\)www-data/\1vagrant/g" ${apache_config_file}
	chown -R vagrant:vagrant /var/log/apache2

	if [ ! -f "${apache_vhost_file}" ]; then
		cat << EOF > ${apache_vhost_file}
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /vagrant/public
    LogLevel debug

    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined

    <Directory /vagrant/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF
	fi

	a2dissite 000-default
	a2ensite vagrant_vhost

	a2enmod rewrite

    service apache2 restart
	# service apache2 reload
	# update-rc.d apache2 enable
}

php_go() {


    sudo apt-get -y install php7.1 php7.1-common hp7.1-cli php7.1-mysql php7.1-curl php7.1-mcrypt php7.1-intl php7.1-xdebug php7.1-xml php7.1-zip php7.1-mbstring
	sed -i "s/display_startup_errors = Off/display_startup_errors = On/g" ${php_config_file}
	sed -i "s/display_errors = Off/display_errors = On/g" ${php_config_file}
    sed -i "s/memory_limit = 128M/memory_limit = 256M/g" ${php_config_file}
	service apache2 reload
}
mysql_go() {
    # Install MySQL
	echo "mysql-server mysql-server/root_password password root" | debconf-set-selections
	echo "mysql-server mysql-server/root_password_again password root" | debconf-set-selections
	sudo apt-get -y install mysql-client mysql-server

	sed -i "s/bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" ${mysql_config_file}

	# Allow root access from any host
	echo "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'root' WITH GRANT OPTION" | mysql -u root --password=root
	echo "GRANT PROXY ON ''@'' TO 'root'@'%' WITH GRANT OPTION" | mysql -u root --password=root

    # create test db
    echo "CREATE DATABASE test DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci" | mysql -u root --password=root

	service mysql restart
	# update-rc.d apache2 enable
	service apache2 restart
}


phpunit_go() {
    cd ~
    wget https://phar.phpunit.de/phpunit-6.5.phar
    chmod +x phpunit-6.5.phar
    sudo mv phpunit-6.5.phar /usr/local/bin/phpunit
}

main
exit 0