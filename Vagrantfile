# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "ubuntu/trusty64"

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  config.vm.network "forwarded_port", guest: 80, host: 6969

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.111"

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
    #switch to root
    sudo su
    
    apt-get update
    
    # Install Apache
    apt-get install -y apache2
    
    # Link the shared folder to the webroot if needed
    if ! [ -L /var/www ]; then
      rm -rf /var/www
      ln -fs /vagrant /var/www
    fi
    
    # Install PHP + dependencies
    apt-get -y install php5 libapache2-mod-php5 php5-mcrypt php5-curl php5-mysqlnd
    
    # Enable php extension
    php5enmod mcrypt
    
    # Install Composer
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    
    # Install project dependenceies
    ( cd /var/www ; composer install)
    
    # Configure apache to rewrite
    echo -e "<VirtualHost *:80>" > /etc/apache2/sites-available/000-default.conf
    echo -e "\tDocumentRoot /var/www" >> /etc/apache2/sites-available/000-default.conf
    echo -e "\t<Directory /var/www/>" >> /etc/apache2/sites-available/000-default.conf
    echo -e "\t\tAllowOverride All" >> /etc/apache2/sites-available/000-default.conf
    echo -e "\t</Directory>" >> /etc/apache2/sites-available/000-default.conf
    echo -e "\tErrorLog /error.log" >> /etc/apache2/sites-available/000-default.conf
    echo -e "\tCustomLog /access.log combined" >> /etc/apache2/sites-available/000-default.conf
    echo -e "</VirtualHost>" >> /etc/apache2/sites-available/000-default.conf
    
    # Enable Apache mod_rewrite
    a2enmod rewrite
    
    # Restart Apache2
    service apache2 restart
    
  SHELL
end
