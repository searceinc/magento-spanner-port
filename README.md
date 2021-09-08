## Setup and Prerequisites:

The Magento Commerce Cloud project is a [Git
repository](https://github.com/searceinc/magento-spanner-port) of
Magento code that is a fork of the Magento repository with modifications
to demonstrate an integration with Cloud Spanner. You can clone the
project and create an active branch to develop code and add extensions
using your local environment.

The following steps are tested on the Ubuntu 20.04 version, with 16GB
RAM. But similar steps can be followed for other operating systems with
some research on similar dependencies.

## Install gRPC and composer

If not already available on the default installation of your Ubuntu OS
version, [gRPC](https://grpc.io/) and
[composer](https://getcomposer.org/) are required dependencies
for installation of the magento software, which can be installed by
following the steps mentioned in
[here](https://grpc.io/blog/installation/) for gRPC and
[here](https://getcomposer.org/download/) for composer
respectively.

## Install Apache

Apache is available within Ubuntu's default software repositories,
making it possible to install it using conventional package management
tools.

Let's begin by updating the local package index to reflect the latest
upstream changes:

> sudo apt update
  
Then, install the apache2 package:

> sudo apt install apache2

After confirming the installation, apt will install Apache and all
required dependencies.

## Install PHP

Add the ondrej/php repository, which has the PHP 7.4 package and other
required PHP extensions.

> sudo apt install software-properties-common\
  sudo add-apt-repository ppa:ondrej/php\
  sudo apt update

Execute the following command to install PHP 7.4

> sudo apt install php7.4

## Firewall Config

After installing Apache, it's necessary to modify the firewall settings
to allow outside access to the default web ports. Assuming that you
followed the instructions in the prerequisites, you should have a UFW
firewall configured to restrict access to your server.

During installation, Apache registers itself with UFW to provide a few
application profiles that can be used to enable or disable access to
Apache through the firewall.

List the ufw application profiles by typing:

> sudo ufw app list

If you run into ufw: command not found error, make sure to run the
following command before running the above:

> sudo app install ufw

You will see a list of the application profiles:

Output

  > Available applications:\
  Apache\
  Apache Full\
  Apache Secure\
  OpenSSH

As you can see, there are three profiles available for Apache:

Apache: This profile opens only port 80 (normal, unencrypted web
traffic)

Apache Full: This profile opens both port 80 (normal, unencrypted web
traffic) and port 443 (TLS/SSL encrypted traffic)

Apache Secure: This profile opens only port 443 (TLS/SSL encrypted
traffic)

It is recommended that you enable the most restrictive profile that will
still allow the traffic you've configured. Since we haven't configured
SSL for our server yet in this guide, we will only need to allow traffic
on port 80:

> sudo ufw allow \'Apache\'

You can verify the change by typing:

> sudo ufw enable
> sudo ufw status

You should see HTTP traffic allowed in the displayed output:

Output

  > Status: active\
  \
  To Action From\
  \-- \-\-\-\-\-- \-\-\--\
  OpenSSH ALLOW Anywhere\
  Apache ALLOW Anywhere\
  OpenSSH (v6) ALLOW Anywhere (v6)\
  Apache (v6) ALLOW Anywhere (v6)


As you can see, the profile has been activated to allow access to the
web server.

## Clone from the Repository

git clone https://github.com/searceinc/magento-spanner-port

## Magento Installation

The version of magento being used in the repository is Magento 2.0.

Before proceeding with the installation, please register for a magento
user by following the instructions on the official website
[here](https://account.magento.com/applications/customer/create/).

We will need some prerequisite software to be installed before we can
install Magento.

The below are the list of software's that are required to be installed
in the local environment:

### Install PHP modules required for magento

>  sudo apt install php7.4-fpm php7.4-common php7.4-mysql php7.4-gmp php7.4-curl

> sudo apt install php7.4-intl php7.4-mbstring php7.4-xmlrpc php7.4-gd php7.4-xml php7.4-cli php7.4-zip php7.4-bcmath php7.4-soap php7.4-intl

### Install Elastic search

> sudo sh -c \'echo \"deb https://artifacts.elastic.co/packages/7.x/apt
stable main\" \> /etc/apt/sources.list.d/elastic-7.x.list\'

If you want to install a previous version of Elasticsearch, change 7.x
in the command above with the version you need.

Once the repository is enabled, install Elasticsearch by typing

> sudo apt update

> sudo apt install elasticsearch

> sudo systemctl start elasticsearch.service

Set magento folder permissions

Change directory to the repository folder and execute the following
steps

> sudo find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {}

> sudo find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {}

> sudo chown -R :www-data

> sudo chmod u+x bin/magento 

### Configure virtual host 

Configure Apache server for Magento

Create /etc/apache2/sites-available/magento.conf and configure the
magento folder

    <VirtualHost *:80>
      ServerAdmin admin\@local-magento.com
      DocumentRoot /var/www/html/magento/
      ServerName magento-poc.com
      ServerAlias www.magento-poc.com
      <Directory /var/www/html/magento/>
        Options Indexes FollowSymlinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
      </Directory>
      ErrorLog ${APACHE_LOG_DIR}/error.log
      CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>

Along with the above mentioned configuration file, follow the steps
mentioned
[here](https://httpd.apache.org/docs/2.4/vhosts/index.html), to
the virtual host to be available at
[www.magento-poc.com](http://www.magento-poc.com) url on the
browser.

### Install packages

Magento 2 requires the composer 1.x version.

> sudo apt install composer

> sudo composer install

### Create Database for magento

Ensure mysql is installed in your environment. After logging into the
mysql terminal with a user that has CREATE DATABASE privileges, execute
the following command to create the empty database for the magento to be
installed in the next step

> create database magento

## Using the Cloud Spanner Emulator

The Cloud SDK provides a local, in-memory
[emulator](https://cloud.google.com/spanner/docs/emulator), which
you can use to develop and test your applications for free without
creating a GCP Project or a billing account. As the emulator stores data
only in memory, all state, including data, schema, and configs, is lost
on restart. The emulator offers the same APIs as the Cloud Spanner
production service and is intended for local development and testing,
not for production deployments.

Since the approach to installing and running the Spanner instance in the
Emulator may change in near future. Please follow the instructions
[here](https://cloud.google.com/spanner/docs/emulator), to start
the emulator to use a substitute of the Spanner database initially.

## Install magento Database

Change directory to the Magento installation root directory and run the
following command

> sudo bin/magento setup:install \--base-url=http://magento-poc.com
\--db-host=localhost \\ \--db-name=magento \--db-user=\<\*\*\*\>
\--db-password=\<\*\*\*\> \--admin-firstname=admin \\
\--admin-lastname=demo \--admin-email=good\@example.com.com
\--admin-user=admin \\ \--admin-password=\<\*\*\*\> \--language=en_US
\--currency=INR \--timezone=Asia/Kolkata \--use-rewrites=1

The \--db-user mentioned above is a placeholder for referencing later.
If needed, please replace it with the DB username of choice. Please
\--db-user and \--db-password fields to specify a new username and
password you would want to get created to authorise to access in the
future.

### Verify your local workspace

To verify the local environment is hosting the server, access the store
using the URL you passed in the install command. For this example, you
can access the local Magento store using the following URL formats:

-   http://\<domainname>/

-   http://\<domainname>/admin

**To change the URI for the Admin panel, use this command to locate
it:**

Change directory to the folder /var/www/html/magento and run the
following command

  -----------------------------------------------------------------------
  php bin/magento info:adminuri
  -----------------------------------------------------------------------

  -----------------------------------------------------------------------

To verify the Integration master branch environment, log into the
Project Web Interface and select your named project. In the list of
branches, select the Master. Click Access site to pull up a list of URLs
(HTTP and HTTPS) and click the preferred link to open the site. To view
the admin, add /admin or other configured Admin URI.

After the installation for Magento which comes with the default database
of MySQL. We need to port the database and its schema to the Spanner
format, which can be accomplished by using the migration tool -
[HarbourBridge](https://github.com/cloudspannerecosystem/harbourbridge).

## Migrate mysql data to Cloud Spanner

[HarbourBridge](https://github.com/cloudspannerecosystem/harbourbridge)
is a stand-alone open source tool for Cloud Spanner evaluation, using
data from an existing PostgreSQL or MySQL database. The tool ingests
schema and data from either a pg_dump/mysqldump file or directly from
the source database, automatically builds a Spanner schema, and creates
a new Spanner database populated with data from the source database.

1.  Installing HarbourBridge

> Download the tool to your machine and install it.

  > GO111MODULE=on go get github.com/cloudspannerecosystem/harbourbridge

2.  Migrate data

  > mysqldump \--user=\'root\' \--password=\'\<password>\' \<mysql db
  name>\| go run github.com/cloudspannerecosystem/harbourbridge
  -driver=mysqldump -dbname=\<cloud spanner dbname>

3.  Install spanner cli for running queries in command line

> spanner-cli is an interactive command line tool for Google Cloud
> Spanner.
>
> You can control your Spanner databases with idiomatic SQL commands.

  > go get -u github.com/cloudspannerecosystem/spanner-cli

4.  Convert Auto increment field to UUID

> There are 2 different ways of using the migrated data in Cloud
> Spanner.

a)  One is default migration with primary key as INT64 data type which
    > has to be incremented programmatically from the Magento System.
    > This will use the same structure which is ported from MySql
    > database

b)  Modify the primary key with Cloud Spanner recommended process and
    > update the column to accept UUID value. Run the scripts in the
    > following file to update table structures for the UUID changes.
    > This script will drop and recreate the tables that will support
    > UUID. As such, any data in these tables will be lost.

  > https://github.com/searceinc/magento-spanner-port/blob/session-pool/db/UUID-alter.sql

5.  Interleaved tables

> Cloud Spanner recommends Interleaved tables for better data
> optimization and you can run the script in the following file to
> create the table schemas for the Interleaved tables.
>
> These are sample scripts which can be used for reference in case we
> need to implement interleaved tables. Please backup data and drop the
> existing tables before migrating to interleaved tables.

  > https://github.com/searceinc/magento-spanner-port/blob/session-pool/db/interleaved-create.sql

## Conclusion

The steps are your guide to setting up the repository and a note on all
the dependencies needed. Post the setup, you are ready to explore
[this](https://docs.google.com/document/d/1NTzNa0yWGecxOXDePzYfzfB0y6_HJTV4c8cSRIWdOVA/edit)
article which walks the user through the code snippets and instances of
the e-commerce website and features.
