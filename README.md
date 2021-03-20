## **Overview**

The purpose of this document is to provide a guide for PHP developers to
integrate with [Cloud
Spanner](https://cloud.google.com/spanner/docs). This document
will be useful for the PHP developers in integration with Cloud Spanner,
testing and deployment of the same. There are various PHP frameworks
available and as part of this proof of concept we will choose
[Magento](https://github.com/magento/magento2) framework for
integration with Cloud Spanner.

[Magento](https://github.com/magento/magento2) is a widely
popular PHP based open source ecommerce platform. In 2020, Magento was
considered to be the top eCommerce platform,offering an unprecedented
level of flexibility,excellent features and great capabilities . Magento
has over 250k active users and approximately 185K active websites have
been built with Magento. The default mysql backend works well for basic
websites but performance bottlenecks ,inability to scale for handling
large volumes of data and multi-region support has inhibited most of the
PHP application's usage for large scale enterprise applications with
MySql as a backend. Challenges in customization and time consumption
have led enterprises to migrate from MySQL to other DB for wider
support.

Cloud Spanner on the other hand is a distributed relational database
service that runs on **Google Cloud**. It is designed to support global
online transaction processing deployments, SQL semantics, highly
available horizontal scaling and transactional consistency. The Cloud
Spanner is capable of handling large volumes of data. Its use is not
limited to applications of large size but it allows the standardisation
of a single database engine for all workloads requiring RDBMS.
Scalability is an essential feature of cloud databases . Google Cloud
Spanner is automatically scalable . Spanner has low latency and strong
consistency which makes it ideal for data transactions with any
sophisticated system.

In order to solve some of these problems, we have come up with the idea
of integrating PHP application with Cloud Spanner, wherein we would be
able to leverage the technologies and features to sync in for a one stop
solution.

#### The Solution

Integrating PHP applications with Cloud Spanner would take care of the
following:

-   Efficient and effective way to handle high volume of data

-   Performance Enhancement

-   All time global support

-   Cloud Spanner delivers industry-leading 99.99% availability for
    > multi-regional instances

-   Scaling RDBMS solutions without complex sharding or clustering

-   Provides transparent, synchronous replication across region and
    > multi-region configurations

-   Process Optimization

#### Approach

We would be doing the following sequentially, as below:

-   Clone version 2 from Magento Repository -- We would be starting with
    > the integration , by cloning Magento Repository. This step has to
    > be done in order to fully access the Magento site and store , so
    > that we would be able to leverage on the database and services

-   Checking for Prerequisites -- This step involves the installation of
    > some softwares on the machine, which would facilitate installation
    > of Magento. This would include PHP modules, configuring virtual
    > hosts, installing elastic search . A handy description to install
    > these softwares in mentioned in the document

-   Installing Magento- A sequential step by step instruction as to how
    > to install Magento is given in the below document.

-   Installing Cloud Spanner Emulator

-   Installing HarbourBridge Tool for Data migration of MySQL to Cloud
    > Spanner

-   Working on Magento Modules like Catalog, Wishlist and Cart and
    > integrating with Cloud Spanner

## Clone from Magento2 Repository

The Magento Commerce Cloud project is a
[Git](https://github.com/magento/magento2) repository of Magento
code and it includes a database and services to fully access the Magento
site and store. We will clone the Magento 2 project to demonstrate
integration of PHP application with Cloud Spanner

#### Command 

  ```javascript
  git clone https://github.com/magento/magento2.git
  ```

#### Checkout to stable version 

You can checkout a specific release branch after cloning the latest
code.

#### Command

  ```javascript
  git checkout tags/<tag_name> -b <branch_name>
  ```


## Install PreRequisite Softwares

#### PHP Installation

We will need some prerequisite software to be installed before we can
install the Magento 2.0 version.

The below are the list of software's that are required to be installed
in the local device:

#### Install PHP modules required for magento

```javascript
sudo apt install php7.4-fpm php7.4-common php7.4-mysql php7.4-gmp php7.4-curl
sudo apt install php7.4-intl php7.4-mbstring php7.4-xmlrpc php7.4-gd php7.4-xml php7.4-cli php7.4-zip php7.4-bcmath php7.4-soap php7.4-intl
```

### Install Elastic search

```javascript
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
sudo apt-get install apt-transport-https
echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-7.x.list
sudo apt-get update && sudo apt-get install elasticsearch
sudo systemctl start elasticsearch.service
```

### Set folder permissions

```javascript
sudo find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
sudo find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
sudo chown -R :www-data . 
sudo chmod u+x bin/magento
```

### Configure virtual host 

> Configure Apache server for Magento
> Create /etc/apache2/sites-available/magento.conf and configure the
> magento folder


### Install packages

> Magento 2 requires composer 1.x version

  ```javascript
  Composer install
  ```

### Create Database for magento
```javascript
Create database magento
```

### Install magento

```javascript
sudo bin/magento setup:install --base-url=http://magento-poc.com/​ --db-host=localhost --db-name=magento --db-user=magentouser --db-password=<***> --admin-firstname=admin --admin-lastname=demo --admin-email=good@example.com.com --admin-user=admin --admin-password=<***> --language=en_US --currency=INR --timezone=Asia/Kolkata --use-rewrites=1
```

### Verify your local workspace

To verify the local, access the store using the URL you passed in the
install command. For this example, you can access the local Magento
store using the following URL formats:

-   http://\<DomainName\>/

-   http://\<DomainName\>/admin

To change the URI for the Admin panel, use this command to locate it:

  ```javascript
  php bin/magento info:adminuri
  ```

To verify the Integration master branch environment, log into the
Project Web Interface and select your named project. In the list of
branches, select the Master. Click Access site to pull up a list of URLs
(HTTP and HTTPS) and click the preferred link to open the site. To view
the admin, add /admin or other configured Admin URI.

## Using the Cloud Spanner Emulator

The Cloud SDK provides a local, in-memory emulator, which you can use to
develop and test your applications for free without creating a GCP
Project or a billing account. As the emulator stores data only in
memory, all state, including data, schema, and configs, is lost on
restart. The emulator offers the same APIs as the Cloud Spanner
production service and is intended for local development and testing,
not for production deployments.

Please use the below link to refer further for installation, usage and
deployment of Emulator:

[Using the Cloud Spanner Emulator](https://cloud.google.com/spanner/docs/emulator)

Once this is completed , in order to ensure seamless data retrieval and
processing, we now use the latest technology of connecting the SQL
database with Cloud Spanner using Harbour Bridge

## Using HarbourBridge to migrate MYSQL to Cloud Spanner 

Before we dive into integrating Cloud Spanner, we will use a tool called
HarbourBridge to convert the MySQL database that was created as part of
our Magento installation above to Cloud Spanner.

HarbourBridge

At its core,
[HarbourBridge](https://github.com/cloudspannerecosystem/harbourbridge)
provides an automated workflow for loading the contents of an existing
MySQL or PostgreSQL database into Spanner. It requires zero
configuration---no manifests or data maps to write. Instead, it imports
the source database, builds a Spanner schema, creates a new Spanner
database populated with data from the source database, and generates a
detailed assessment report. It is intended for loading databases up to a
few tens of GB for evaluation purposes, not full-scale migrations.

Bootstrapping for early stage migration

HarbourBridge bootstraps early-stage migration to Spanner by using an
existing MySQL or PostgreSQL source database to quickly get you running
on Spanner. It generates an assessment report with an overall
migration-fitness score for Spanner, a table-by-table analysis of type
mappings and a list of features used in the source database that aren\'t
supported by Spanner.

HarbourBridge can be used with the [Cloud Spanner
Emulator](https://cloud.google.com/spanner/docs/emulator), or
directly with a Cloud Spanner instance. The Emulator is a local,
in-memory emulation of Spanner that implements the same APIs as Cloud
Spanner's production service, and allows you to try out Spanner's
functionality without creating a GCP Project. The [HarbourBridge
README](https://github.com/cloudspannerecosystem/harbourbridge/blob/master/README.md) contains
a step-by-step [quick-start
guide](https://github.com/cloudspannerecosystem/harbourbridge/blob/master/README.md#quickstart-guide) for
using the tool with a Cloud Spanner instance.

HarbourBridge: Turnkey Spanner Evaluation

Installing HarbourBridge

1.  Installing HarbourBridge

> Download the tool to your machine and install it.

  ```javascript
  GO111MODULE=on go get github.com/cloudspannerecosystem/harbourbridge
  ```

2.  Migrate data

  ```javascript
  mysqldump --user='root' --password='<password>' <mysql db name>| go run github.com/cloudspannerecosystem/harbourbridge -driver=mysqldump -dbname=<cloud spanner dbname>
  ```

3.  Install spanner cli for running queries in command line

> spanner-cli is an interactive command line tool for Google Cloud
> Spanner.
>
> You can control your Spanner databases with idiomatic SQL commands.

  ```javascript
  go get -u github.com/cloudspannerecosystem/spanner-cli
  ```

AutoIncrement field to UUID

By default MySql supports autoincrement primary key identifier. While
converting to Cloud Spanner it will get converted to an identifier but
since Cloud Spanner doesn't support auto increment fields we need to
generate the sequence programmatically. This is not the recommended
approach since it\'s Anti-Pattern and would lead to issues while
sharding the data across multiple nodes.

Cloud Spanner recommends using UUID for managing primary keys. Hence we
need to convert the primary key from integer to string to store the
UUID. The UUID can be generated using any standard format.

## The Feedback and the Evaluation

We have considered integrating the key functionalities which would be
ideal for implementing on the Prototype and can be extended during the
production phase with ease . Hence emphasis is given to these functions
mentioned below so that evaluation of these in the prototype would
eventually iron out any predictable as well as critical issues during
implementation.

-   Catalog module - One of the most important modules in Magento and
    > it's the main page of interaction between customer and retailer
    > wherein the product list is displayed.

-   Adding , Deletion and Updation of Wishlist - considered critical as
    > this function converts prospects to actual sales

-   Adding , Deletion and Updation of Cart - is a critical function for
    > the user to decide on the final checkout and redirect to the final
    > payment gateway

Catalog Module

The Catalogue Module is the most essential feature for attracting
customers, by means of various categories of products that are available
with the merchant , ranging for various needs and wants of the customer.
Modifying the existing catalog module, from many products to a single
category , to fetch data from Cloud Spanner

Existing screen with mysql 

![](media/image13.png){width="6.267716535433071in"
height="2.986111111111111in"}

Code Modification:

Added Cloud Spanner adapter to connect, query and perform other ORM
operations.

Refer the Github Link
[https://github.com/searceinc/magento-spanner-port](https://github.com/searceinc/magento-spanner-port)
for sample implementation.

-   Add Spanner Adapter for creating connection to Cloud Spanner.

-   Configure the Cloud Spanner instance and server information.

-   Add SpannerInterface and Spanner in the Adapter to implement the
    > connection to Cloud Spanner.

Refer 
```javascript
/lib/internal/Magento/Framework/DB/Adapter/Spanner/Spanner.php
/lib/internal/Magento/Framework/DB/Adapter/Spanner/SpannerInterface.php
```

ScreenShot of the Code Snippet:

![](media/image23.png){width="5.613194444444445in"
height="1.176388888888889in"}

![](media/image18.png){width="5.865277777777778in"
height="2.4451388888888888in"}

![](media/image20.png){width="5.772916666666666in"
height="2.3361111111111112in"}

Modify the AbstractDB class within Magento to now connect to Cloud
Spanner using the newly created Connection function within Spanner
Adapter. Refer
```javascript
/lib/internal/Magento/Framework/Data/Collection/AbstractDB.php
```
ScreenShot of the Code Snippet:

![](media/image19.png){width="5.6722222222222225in"
height="3.1006944444444446in"}

Once the connection is established we need to modify the data fetch
method from mysql adapter to Cloud Spanner adapter . Modify
_loadAttributes method in AbstractCollection to connect to Spanner and
Fetch the data from Cloud Spanner.

Refer
```javascript
/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php
```
Screenshot of Code Snippet:

![](media/image31.png){width="5.495833333333334in"
height="0.3951388888888889in"}

![](media/image29.png){width="6.26875in" height="2.4451388888888888in"}

#### Screenshots of Test Cases for Catalog :

When the user clicks on menu items it loads the products from the
Catalog and displays on the screen. In this case the items(watches) are
loaded from Cloud Spanner

Screenshot of the Site loaded from Cloud Spanner

Cloud Spanner Terminal screenshot to verify the Product in
Catalog.![](media/image4.png){width="6.26875in"
height="2.470833333333333in"}

![](media/image21.png){width="6.267716535433071in"
height="1.1388888888888888in"}

Modify Cloud Spanner Data Via Terminal for one of the product and query
the Data via terminal to confirm the modification in Cloud Spanner

![](media/image3.png){width="6.267716535433071in"
height="1.3888888888888888in"}

Reload the screen to confirm that the name of the watch is now changed
to "Aim Analog Cloud Spanner" as updated via Cloud Spanner terminal.

![](media/image27.png){width="6.26875in" height="4.1930555555555555in"}

### Wish List

The functionality of wishlist must be evaluated since it is a prominent
feature for users to browse and speculate about purchasing a product
depending on their needs.

#### Wish List Select

When a user tags a product under the wishlist category it must fetch the
data of that product and add it to the wishlist database of the customer

ScreenShot of the Code Snippet : Modify the red highlighted section with
green highlighted code. Refer the Github link for sample
![](media/image26.png){width="6.26875in" height="3.78125in"}

Add new method to load the data that is selected by the
user![](media/image30.png){width="5.739583333333333in"
height="4.042361111111111in"}

Loading the required data to be selected by the user and checking to see
if the same is reflected in the shopping page

![](media/image28.png){width="5.386805555555555in"
height="4.899305555555555in"}

#### Wish List Add

The Add to Wishlist function is used when a user wants to add a product
from the product catalogue page to his or her wishlist page. By doing so
he or she can purchase the product at a later time or when prices are at
a discounted rate. The functionality here is when the user clicks on the
Add to Wishlist Tab, the data of the product must be retrieved and sent
to the wishlist data table wherein it will be visible according to
latest entry

Refer the code snippet below

![](media/image24.png){width="5.663888888888889in"
height="6.008333333333334in"}

#### Wish List Modify

Updating the quantity of the item in the wishlist will modify the
wishlist. This happens mostly when the user wants to purchase additional
quantity of the product which was pending in line in the wishlist cart.

Refer the code snippet below

![](media/image1.png){width="6.267716535433071in"
height="5.388888888888889in"}

Modify the existing method and replace the highlighted red section with
highlighted green section to add or update wishlist in Cloud Spanner

![](media/image16.png){width="6.267716535433071in"
height="5.458333333333333in"}

#### Wish List Delete:

The Delete button or Remove button in the Wishlist page is when the user
feels that the product needs to be dropped from the wishlist and plans
to forgo any potential purchase of the same in future as well. Once the
delete button is pressed the product data in the wishlist table will be
removed thus the wishlist page will be refreshed without the deleted
product

Refer the code snippet below

![](media/image10.png){width="5.260416666666667in"
height="3.2104166666666667in"}

#### Using UUID for Autoincrement 

For the primary Key column we need to generate UUID for reference. Cloud
Spanner recommends to use UUID instead of auto incrementing ID. Add a
new method to generate UUID.

Please note that in case we still want to use the AutoIncrement logic of
Magento with MySql then modify this method to generate the next ID based
on the maximum value of the primary key column. This approach is not
scalable and has limitations. Hence the recommended approach of
generating UUID is followed

Refer code snippet below

![](media/image14.png){width="6.26875in" height="1.7229166666666667in"}

#### Screenshots of Test cases for Wish List

Scenario: Trying to update the wishlist when ,before and after adding
wishlist, and checking to see if the items chosen for wishlist are
reflected in the wishlist page

![](media/image9.png){width="6.26875in"
height="4.5375in"}![](media/image5.png){width="6.26875in"
height="1.5375in"}

Screenshots for updating wish list:

![](media/image12.png){width="6.26875in" height="4.5375in"}

The Cloud Spanner Terminal shows that the quantity is updated as 2

![](media/image15.png){width="6.26875in" height="0.7895833333333333in"}

After clicking on removing the items from the wish list, the items get
removed from the wish list screen and on refreshing the wishlist screen
it displays no items.

Execute the query in Cloud Spanner Terminal to verify the deleted items.

![](media/image2.png){width="6.267716535433071in"
height="1.1111111111111112in"}

### Cart 

Cart is the page wherein the products that the user chooses to purchase
and make further payment is visible for review . The user can Modify the
cart by deletion or Adding further items before going in for Payment of
Goods

#### Cart Select

Loading data from Cart is similar to the wishlist and would require the
Sql to be sanitized and implementation is similar to Wish List. Refer to
the Github link for samples.

#### Cart Add

When a product is visible in the catalogue page and the user chooses the
Add to Cart button the item will be added to the cart section for the
final payment. Code modification is similar to the wish list. The date
has to be formatted in ISO format while adding the items to the cart.

Refer the code snippet below

![](media/image11.png){width="6.26875in" height="6.260416666666667in"}

#### Cart Modify

Modifying the items in the cart is similar to wishlist and update the
code to handle the date format.

Refer to the code snippet below

![](media/image8.png){width="6.26875in" height="5.252083333333333in"}

#### Cart Delete 

When a user feels that they no longer require the item they may select
the delete or remove option given in the cart page against that specific
item, such that it would be deleted and the amount or cash pertaining to
the item will be discounted from the total cart .

When the Delete option is selected the data of the item in the Cart
table is removed and the cart is refreshed with final set of items for
checkout

![](media/image10.png){width="5.260416666666667in"
height="3.2104166666666667in"}

#### Screenshots of Test cases for Add Cart Screen

![](media/image6.png){width="5.772916666666666in"
height="4.436805555555556in"}

![](media/image17.png){width="6.26875in" height="1.2354166666666666in"}

## Deployment

Create a Compute Engine instance in Google Cloud Platform. Clone the PHP
application code for Magento which contains the modifications for
Catalog, Wishlist and Cart from
[Git](https://github.com/searceinc/magento-spanner-port) url
mentioned below.

  ```javascript
  https://github.com/searceinc/magento-spanner-port
  ```
Follow the steps in [PreRequisite
softwares](#install-prerequisite-softwares) mentioned above in
this document and install Apache, PHP, Elastic Search, Magento .

Install [Cloud Spanner
Emulator](https://cloud.google.com/spanner/docs/emulator) and
migrate the data using
[Harbourbridge](https://github.com/cloudspannerecosystem/harbourbridge)
as mentioned [above in this
document](#using-harbourbridge-to-migrate-mysql-to-cloud-spanner).

Deploy the Auto increment field to UUID by running the scripts mentioned
below to update table structures for the UUID changes

  ```javascript
  https://github.com/searceinc/magento-spanner-port/blob/session-pool/db/UUID-alter.sql
  ```

Deploy the Interleaved tables by running the scripts mentioned below to
update table structures for the Interleaved tables

  ```javascript
  https://github.com/searceinc/magento-spanner-port/blob/session-pool/db/interleaved-create.sql
  ```

Verify the accessibility of the deployed application using url mentioned
below wherein DomainName refers to the DNS mapped

-   http://\<DomainName>/

-   http://\<DomainName>/admin

## 

## Conclusion:

This is just a prototype model for the POC. This deals with the key
requirement of the stakeholder to migrate PHP applications with Cloud
spanner, thereby the tremendous volume of users can be handled globally
with a seamless and sustainable technology that is beneficial for the
user as well as the retailer and the third party who manages the
Data.Thus there can be seen both performance enhancement and increase of
efficiency and accuracy in terms of Process and Data Management