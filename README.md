## Overview

The purpose of this document is to provide a guide for PHP developers to
integrate with [Cloud
Spanner](https://cloud.google.com/spanner/docs). This document
will be useful for the PHP developers in integration with Cloud Spanner,
testing and deployment of the same. There are various PHP frameworks
available and as part of this proof of concept we will choose
[Magento](https://github.com/magento/magento2) framework for
integration with Cloud Spanner.  

## Spanner Adapter
Magento is one of the most widely used PHP framework and it currently supports multiple database backends like MySQL, MariaDB, PostgreSQL and as part of adding Cloud Spanner support, we have built a Spanner adapter which acts as a layer to ensure smooth transaction of data between any PHP application and the Cloud spanner. Spanner Adapter has methods for maintaining session pool , connections and other functions to support CRUD operations

## Create session pool for connection
First will need to create a session or batches by which the connection between the application and Cloud Spanner is established and extended till the maximum session specified. 
CreateSessionPool method creates and returns the session pool object. Variable maxsessions can be configured to set the number of sessions which can be maintained in the pool.

 ```php
   /**
    * Creates the session pool for spanner connection
    * @return SessionPoolInterface
    * @throws Exception
    */
   protected function createSessionPool()
   {
       $cache = new SysVCacheItemPool();
       return new CacheSessionPool($cache, ['maxSessions' => $this->maxsessions]);
   }
 ```


## Connect to the Cloud Spanner database
A connection object is created in the Cloud Spanner which ensures stable connection for the application. Variables instance and project id can be configured while opening the connection object

```php
 /**
    * Creates a Cloud Spanner object and connects to the database.
    *
    * @return void
    */
   protected function _connect()
   {
       if ($this->is_emulator) {
           putenv('SPANNER_EMULATOR_HOST=localhost:9010');
       }
       if ($this->_connection) {
           return;
       }
       $spanner = new SpannerClient([ 'projectId' => $this->project_id ]);
       $sessionPool = $this->createSessionPool();
       $this->_connection = $spanner->connect($this->instance, $this->database, ['sessionPool' => $sessionPool]);
   }
 ```

## Close connection
When the transaction is completed then the close connection function is invoked and the connection is closed between the server and spanner 

 ```php
   /**
    * Closes the connection.
    * @return void
    */
   public function closeConnection()
   {
       if ($this->_connection) {
           $this->_connection->close();
       }
   }
 ```

## Insert
We will need to insert multiple rows in the table, which may be the data of the items chosen by the customer. We enable a function named insert(), by which whenever there is a transaction , a connection is established and the data is inserted in batches to the table within the transaction.

 ```php
   /**
    * Insert multiple rows in single table
    * @param string $table
    * @param array $data
    * @return Commit timestamp
    */
   public function insert(string $table, array $data)
   {
       $results = $this->_connection->runTransaction(function (Transaction $t) use ($table, $data) {
           $t->insertBatch($table, [ $data ]);
           $t->commit();
       });
       return $results;
   }
 ```

## Update
Incase of an updation of the cart or wishlist initiated by the user, the update() function is called upon, by which the the connection is established between the application and Cloud Spanner, thereby within the transaction the table is updated with the new data 

 ```php
   /**
    * Single col update in the table
    * @param string $table
    * @param array $bind
    * @return Commit timestamp
    */
   public function update(string $table, array $bind)
   {
       $results = $this->_connection->runTransaction(function (Transaction $t) use ($table, $bind) {
           $t->updateBatch($table, [ $bind ]);
           $t->commit();
       });
       return $results;
   }
 ```


## Delete
When the user wants to delete an item from the cart , the function delete is called upon. It enables from which table this data needs to be deleted and based on the condition the corresponding data is deleted from the table 

```php 
   /**
    * Delete from table
    * @param string $table
    * @param string $where
    * @return Commit timestamp
    */
   public function delete(string $table, string $where)
   {
       $sql = "DELETE FROM ".$table." WHERE ".$where;
       $results = $this->_connection->runTransaction(function (Transaction $t) use ($sql) {
           $rowCount = $t->executeUpdate($sql);
           $t->commit();
       });
       return $results;
   }
``` 

## Fetch All
When the user needs to fetch the data for a item search or availability, the fetchAll function is enabled, and according to the user input , application retrieves the data from Cloud Spanner from that particular table herein the data is stored 


 ```php
   /**
    * Fetch all rows
    *
    * @param string $sql
    * @return array
    */
   public function fetchAll(string $sql)
   {
       $result = $this->query($sql);
       return $this->fetch($result);
   }
 ```

## Query
Query is used to retrieve data from the table in Cloud Spanner and display the same in the page , by initiating the connection  with Cloud Spanner. 

```php 
   /**
    * query
    *
    * @param string $sql
    * @return mixed|null
    */
   public function query(string $sql)
   {
       $results = $this->_connection->execute($sql);
       return $results;
   }
``` 

## Convert iterator into array

```php
   /**
    * Returns all rows
    *
    * @param object $data
    * @return array
    */
   public function fetch(object $data)
   {
       return iterator_to_array($data->rows());
   }
```
 
## Generate the generate UUID 
Cloud Spanner does not support incremental field and recommends UUID, hence we need to convert primary key from integer to string and generate it  in  UUID format 
	
```php 
   /**
    * Generate UUID
    * @return string
    */
   public function getAutoIncrement()
   {
       if (function_exists('com_create_guid') === true) {
           return trim(com_create_guid(), '{}');
       }
 
       return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));   
   }
``` 


## Add Cast to sql
We will need to segregate the column of the tables into various categories for ease of retrieval of data according to the data type , which may be used for analysis further 
	
```php 
   /**
    * Cast the column with type
    * @param string $sql
    * @param string $col
    * @param string $type
    * @return string
    */
   public function addCast(string $sql, string $col, string $type)
   {
      $cast = "cast(".$col." as ".$type.")";
      return str_replace($col, $cast, $sql);
   }
  
```

## Sanitize Sql query
Since Cloud Spanner uses strict type of formatting the query, we use a sanitizer to filter data of non-specified formats 
	
 ```php
   /**
    * Formats the sql for Cloud Spanner
    * Example
    * Input SQL : <select statement> WHERE (`product_id` = '340') ORDER BY position  ASC
    * Output SQL <select statement> WHERE (`product_id` = 340) ORDER BY position  ASC
    * In the above example integer `340` is sanitized by removing single quotes.
    * Sanitization is required since Cloud Spanner is strict type
    * @param string $sql
    * @return string $sql
    */
   public function sanitizeSql(string $sql)
   {
       if (preg_match_all("/('[^']*')/", $sql, $m)) {
           $matches = array_shift($m);
           for($i = 0; $i < count($matches); $i++) {
               $curr =  $matches[$i];
               $curr = filter_var($curr, FILTER_SANITIZE_NUMBER_INT);
               if (is_numeric($curr)) {
                   $sql = str_replace($matches[$i], $curr, $sql);
               }
           }
       }
 
       $sql = str_replace('RAND()','1', $sql);
 
       return $sql;
   }
 ```

## Catalog 
Catalog is meant for viewing the range of products available in the online store.

Replace the _loadAttributes method in file app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php mentioned in snippet 1  with snippet 2

Snippet 1:
 ```php
         $values = $this->getConnection()->fetchAll($select);
 ```

Snippet 2: 
 ```php
         $con = $this->getSpannerConnection();
 
         /**
          * Cloud Spanner follows strict type so cast the columns in common type
          */
         $select = $con->addCast($select, "`t_d`.`value`", 'string');
         $select = $con->addCast($select, "`t_s`.`value`", 'string');
         $select = $con->addCast($select, "IF(t_s.value_id IS NULL, t_d.value, t_s.value)", 'string');
                  
         $values = $con->fetchAll($select);
 ```

## Wishlist and Add to cart
Wishlist is is the module where users add a product for future purchase, and add to cart option is the function that enables the user to move the item that he or she wishes to purchase to a final cart page wherein he or she can review the items in the list and proceed for final payment 


Replace the loadWithFilter method mentioned in file lib/internal/Magento/Framework/Data/Collection/AbstractDb.php in snippet 3 with snippet 4

snippet 3:

 ```php
       $data = $this->getData();
 ```

snippet 4:
 ```php
       $sql = $this->getSelect()->__toString();
       $con = $this->getSpannerConnection();
       $sql = $con->sanitizeSql($sql);
       $data = $con->fetchAll($sql);
```
Replace the load method mentioned in file lib/internal/Magento/Framework/Model/ResourceModel/Db/AbstractDb.php in snippet 5 with snippet 6.

Snippet 5:

 ```php
       $connection = $this->getConnection();
       if ($connection && $value !== null) {
           $select = $this->_getLoadSelect($field, $value, $object);
           $data = $connection->fetchRow($select);
           if ($data) {
               $object->setData($data);
           }
       }
 ```

Snippet 6:
 ```php
       $con = $this->getSpannerConnection();
       if ($con && $value !== null) {
          
           $select = $this->getLoadSelectForSpanner($field, $value);
           $data = $con->fetchRow($select);
           if ($data) {
               $object->setData($data);
           }
       }
 ```

Add new  method for building query for select

 ```php
   /**
    * Retrieve select object for load object data
    *
    * @param string $field
    * @param string $value
    * @return string
    */
   protected function getLoadSelectForSpanner(string $field, string $value)
   {
       $select = "select * from ".$this->getMainTable()." where ".$field;
       if(is_numeric($value)) {
           $select = $select."=".$value."";
       } else {
           $select = $select."='".$value."'";
       }
       return $select;
   }
 
```

Replace the save method mentioned in file lib/internal/Magento/Framework/Model/ResourceModel/Db/AbstractDb.php in snippet 7 with snippet 8.

snippet 7:
     ```php         
               if ($this->isObjectNotNew($object)) {
                   $this->updateObject($object);
               } else {
                   $this->saveNewObject($object);
               }
      ```  

snippet 8:
        ```php      
               if ($this->isObjectNotNew($object)) {
                   $this->updateObjectInSpanner($object);
               } else {
                   $this->saveNewObjectInSpanner($object);
               }
          ```

## Save in Cloud Spanner
Whenever the user wants to add an item to his cart or wishlist the same is initiated in the table below by saveNewObjectInSpanner function , by which Cloud Spanner gets prepared for creating a new row and data is inserted in the table.
 ```php
   /**
    * Save New Object in Cloud Spanner
    *
    * @param \Magento\Framework\Model\AbstractModel $object
    * @throws LocalizedException
    * @return void
    */
   protected function saveNewObjectInSpanner(\Magento\Framework\Model\AbstractModel $object)
   {
       $bind = $this->_prepareDataForSave($object);
       $con = $this->getSpannerConnection();
       if ($this->_isPkAutoIncrement) {
           $bind[$this->getIdFieldName()] = $con->getAutoIncrement();
       }
 
       if(isset($bind['added_at'])) {
           $bind['added_at'] =  $con->formatDate();
       }
 
      if($this->getMainTable() == 'quote_item' || $this->getMainTable() == 'quote_address') {
           $bind['created_at'] =  $con->formatDate();
           $bind['updated_at'] =  $con->formatDate();
           $bind['free_shipping'] =  1;
      }
 
       if(isset($bind['last_visit_at'])) {
           $bind['last_visit_at']  =  $con->formatDate();
       }
 
       $con->insert($this->getMainTable(), $bind);
 
       if ($this->_isPkAutoIncrement) {
           $object->setId($bind[$this->getIdFieldName()]);
       }
 
       if ($this->_useIsObjectNew) {
           $object->isObjectNew(false);
       }
   }
 ```
## Update in Cloud Spanner
When ever the user wants to update his cart or wishlist by adding an item the same is initiated in the table below by updateObjectInSpanner function , by which Cloud Spanner gets prepared for updation of data and data is updated in the table 
 ```php
   /**
    * Update existing object
    *
    * @param \Magento\Framework\Model\AbstractModel $object
    * @throws LocalizedException
    * @return void
    */
   protected function updateObjectInSpanner(\Magento\Framework\Model\AbstractModel $object)
   {
       $con = $this->getSpannerConnection();
       $data = $this->prepareDataForSpannerUpdate($object);
       if ($this->_isPkAutoIncrement) {
           $data[$this->getIdFieldName()] = $object->getId();
       }
       if(isset($data['added_at'])) {
           $data['added_at'] =  $con->formatDate();
       }
 
       if(isset($data['created_at'])) {
           $data['created_at'] =  $con->formatDate();
       }
 
       if(isset($data['updated_at'])) {
           $data['updated_at'] =  $con->formatDate();
       }
 
       if(isset($data['customer_dob'])) {
           $data['customer_dob'] =  $con->convertDate($data['customer_dob']);
       }
 
       if(isset($data['last_visit_at'])) {
           $data['last_visit_at']  =  $con->formatDate();
       }
 
       if (!empty($data)) {
           $con->update($this->getMainTable(), $data);
       }
   }
 ```

Prepare Data for Update in Cloud Spanner
```php
 
   /**
    * Get the array of data fields that was changed or added
    *
    * @param \Magento\Framework\Model\AbstractModel $object
    * @return array
    * @throws LocalizedException
    */
   protected function prepareDataForSpannerUpdate(\Magento\Framework\Model\AbstractModel $object)
   {
       $data = $object->getData();
       foreach ($object->getStoredData() as $key => $value) {
           if (array_key_exists($key, $data) && $data[$key] === $value) {
               unset($data[$key]);
           }
       }
       $dataObject = clone $object;
       $dataObject->setData($data);
       $data = $this->_prepareDataForTable($dataObject, $this->getMainTable());
       unset($dataObject);
 
       return $data;
   }
 ```

## Delete record in Cloud Spanner
When a user needs to delete a item from cart or wish list, this delteInSpanner function is initiated, by which the spanner will get connected to Cloud Spanner and delete the data 

```php 
   /**
    * Delete from Cloud Spanner
    *
    * @param \Magento\Framework\Model\AbstractModel $object
    * @return void
    */
   public function deleteInSpanner(\Magento\Framework\Model\AbstractModel $object)
   {
       $con = $this->getSpannerConnection();
       if($object->getId()) {
           if(is_numeric($object->getId())) {
               $condition = $this->getIdFieldName() . '='. $object->getId();
           } else {
               $condition = $this->getIdFieldName() . '="'. $object->getId().'"';
           }
           $con->delete($this->getMainTable(), $condition);
       }
   }
 ```
