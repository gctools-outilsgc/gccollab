## Installation instructions
**Note: GCcollab does not work with PHP7 - GCcollab ne fonctionne pas avec PHP7**

### Ubuntu 14.04
#### Install Aptitude
    sudo apt-get update
    sudo apt-get install aptitude
    
#### Install Git, Apache, MySQL, PHP and libs
    sudo aptitude install git apache2 mysql-server php5 libapache2-mod-php5 php5-mysql php5-gd
When prompted, enter a root password for MySQL.


#### Fork and Clone GCCollab Github Repo
    git clone -b gccollab https://github.com/gctools-outilsgc/gccollab.git

#### Install Composer 
Setup [Composer](https://getcomposer.org/download/). 
Download the install off the site. Default name of the file is "installer"
Go into the directory the file was downloaded to (Example: cd /home/username/Downloads).

    sudo php installer --install-dir=/bin --filename=composer
    
#### Composer Dependencies
Go into your gccollab directory. (Example: cd /home/username/gccollab)

    composer install

#### Create data directory
Create a directory (not in gccollab). 

    mkdir gccollab_data

Example: /home/username/gccollab_data/

#### Set permissions
    chmod 777 gccollab
    chmod 777 gccollab/engine
    chmod 700 gccollab_data
    sudo chown www-data:www-data gccollab_data

#### Create link to gccollab in /var/www/html folder
    cd /var/www/html/
    sudo ln -s /EXAMPLE/PATH/TO/gccollab gccollab
    
Use the path to gccollab. (Example: /home/username/gccollab)

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

Save and close (Ctrl-o then Ctrl-x if you are using nano)

    sudo service apache2 restart

#### Install Elgg
Goto ```http://localhost/gccollab```.  Follow instructions.  You will need to enter the database information and the path to the data folder.

| Parameter             | Value         |
| --------------------- | ------------- |
| Database Username     | gccollab      |
| Database Password     | password      |
| Database Name         | gccollabdb    |
| Database Host         | localhost     |

#### Reset permissions
    chmod 775 gccollab
    chmod 775 gccollab/engine

#### Configure Plugins
The final step to getting the GCcollab experience is to reorder and
enable/disable plugins in the Administration section of your installation.

A quick way to sort and activate plugins in the correct order is to activate
the "Plugin Loader" plugin. Do this by going into `Configure`-> `Plugins` -> ctrl+f `Plugin Loader` -> `Activate`
Then go to
`Configure`->`Utilities`->`Plugin Loader` menu and click on the `Import`
button.

### Elgg Installation Instructions
http://learn.elgg.org/en/2.0/intro/install.html
