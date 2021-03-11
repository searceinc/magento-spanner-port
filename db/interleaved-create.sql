-- Cloud Spanner schema for Catalog tables.
-- This schema uses Cloud Spanner interleaved tables to improve read and write performance
-- Refer 
-- https://cloud.google.com/spanner/docs/schema-and-data-model#creating_a_hierarchy_of_interleaved_tables
-- for  more details.
-- For original MySql Schema please refer app/code/Magento/Catalog/etc/db_schema.xml

--
-- Interleaved Parent Table
--

CREATE TABLE catalog_product_entity (
    entity_id INT64 NOT NULL,        
    attribute_set_id INT64 NOT NULL,
    type_id STRING(32) NOT NULL,
    sku STRING(64),
    has_options INT64 NOT NULL,
    required_options INT64 NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL
) PRIMARY KEY (entity_id);

--
-- Interleaved Child Tables
--

CREATE TABLE catalog_product_entity_datetime (
    value_id INT64 NOT NULL,
    attribute_id INT64 NOT NULL,
    store_id INT64 NOT NULL,
    entity_id INT64 NOT NULL,
    value TIMESTAMP
) PRIMARY KEY (entity_id, value_id), INTERLEAVE IN PARENT catalog_product_entity;

CREATE TABLE catalog_product_entity_decimal (
    value_id INT64 NOT NULL,
    attribute_id INT64 NOT NULL,
    store_id INT64 NOT NULL,
    entity_id INT64 NOT NULL,
    value NUMERIC
) PRIMARY KEY (entity_id, value_id), INTERLEAVE IN PARENT catalog_product_entity;

CREATE TABLE catalog_product_entity_int (
    value_id INT64 NOT NULL,
    attribute_id INT64 NOT NULL,
    store_id INT64 NOT NULL,
    entity_id INT64 NOT NULL,
    value INT64
) PRIMARY KEY (entity_id, value_id), INTERLEAVE IN PARENT catalog_product_entity;

CREATE TABLE catalog_product_entity_text (
    value_id INT64 NOT NULL,
    attribute_id INT64 NOT NULL,
    store_id INT64 NOT NULL,
    entity_id INT64 NOT NULL,
    value STRING(MAX)
) PRIMARY KEY (entity_id, value_id), INTERLEAVE IN PARENT catalog_product_entity;

CREATE TABLE catalog_product_entity_varchar (
    value_id INT64 NOT NULL,
    attribute_id INT64 NOT NULL,
    store_id INT64 NOT NULL,
    entity_id INT64 NOT NULL,
    value STRING(255)
) PRIMARY KEY (entity_id, value_id), INTERLEAVE IN PARENT catalog_product_entity;
