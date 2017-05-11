## Installation instructions
**Note: GCcollab does not work with PHP7 - GCcollab ne fonctionne pas avec PHP7**

### Ubuntu 14.04
#### Install Git, Apache, MySQL, PHP and libs
    sudo aptitude install git apache2 mysql-server php5 libapache2-mod-php5 php5-mysql php5-gd
When prompted, enter a root password for MySQL.

#### Fork and Clone GCConnex Github Repo
    git clone -b gcconnex https://github.com/gctools-outilsgc/gcconnex.git

#### Create data directory

This directory can be anywhere.  Its absolute path will be specified during installation of Ellg.

    mkdir gccollab_data

#### Set permissions
    chmod 777 gccollab
    chmod 777 gccollab/engine
    chmod 700 gccollab_data
    sudo chown www-data:www-data gccollab_data

#### Create link to gccollab in /var/www/html folder
    cd /var/www/html/
    sudo ln -s /path/to/gccollab gccollab

#### Create a database and a user for the database
    mysql -u root -p
    CREATE DATABASE gccollabdb;
    GRANT ALL ON gccollabdb.* TO gccollab@localhost IDENTIFIED BY 'password';
    QUIT;
Choose a better password

#### Enable mod_rewrite in Apache
    sudo a2enmod rewrite
    sudo nano /etc/apache2/sites-available/000-default.conf

Add the following inside the ```<VirtualHost *:80></VirtualHost>``` tag
```
<Directory /var/www/html/gccollab>
  Options Indexes FollowSymLinks MultiViews
  AllowOverride All
  Order allow,deny
  allow from all
</Directory>
```

If you copied from another vhost apache configuration file, make sure the path after the Directory instructions matches where the gccollab symlink is.

Save and close (Ctrl-o then Ctrl-x if you are using nano)

    sudo service apache2 restart

#### Install Elgg
Goto ```http://localhost/gccollab```.  Follow instructions.  You will need to enter the database information and the path to the data folder.

#### Reset permissions
    chmod 775 gccollab
    chmod 775 gccollab/engine

#### Configure Plugins
The final step to getting the GCcollab experience is to reorder and enable/disable plugins in the Administration section of your installation.

### Elgg Installation Instructions
http://learn.elgg.org/en/1.x/intro/install.html
