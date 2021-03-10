DROP table quote_id_mask;

CREATE TABLE quote_id_mask (
    entity_id STRING(MAX) NOT NULL, -- From: entity_id int(11)
    quote_id INT64 NOT NULL,  -- From: quote_id int(11)
    masked_id STRING(32)      -- From: masked_id varchar(32)
) PRIMARY KEY (entity_id, quote_id);

DROP table quote_item;

CREATE TABLE quote_item (
    item_id STRING(MAX) NOT NULL,                        -- From: item_id int(11)
    quote_id INT64 NOT NULL,                       -- From: quote_id int(11)
    created_at TIMESTAMP NOT NULL,                 -- From: created_at timestamp
    updated_at TIMESTAMP NOT NULL,                 -- From: updated_at timestamp
    product_id INT64,                              -- From: product_id int(11)
    store_id INT64,                                -- From: store_id smallint(6)
    parent_item_id INT64,                          -- From: parent_item_id int(11)
    is_virtual INT64,                              -- From: is_virtual smallint(6)
    sku STRING(255),                               -- From: sku varchar(255)
    name STRING(255),                              -- From: name varchar(255)
    description STRING(MAX),                       -- From: description text
    applied_rule_ids STRING(MAX),                  -- From: applied_rule_ids text
    additional_data STRING(MAX),                   -- From: additional_data text
    is_qty_decimal INT64,                          -- From: is_qty_decimal smallint(6)
    no_discount INT64,                             -- From: no_discount smallint(6)
    weight NUMERIC,                                -- From: weight decimal(12,4)
    qty NUMERIC NOT NULL,                          -- From: qty decimal(12,4)
    price NUMERIC NOT NULL,                        -- From: price decimal(12,4)
    base_price NUMERIC NOT NULL,                   -- From: base_price decimal(12,4)
    custom_price NUMERIC,                          -- From: custom_price decimal(12,4)
    discount_percent NUMERIC,                      -- From: discount_percent decimal(12,4)
    discount_amount NUMERIC,                       -- From: discount_amount decimal(20,4)
    base_discount_amount NUMERIC,                  -- From: base_discount_amount decimal(20,4)
    tax_percent NUMERIC,                           -- From: tax_percent decimal(12,4)
    tax_amount NUMERIC,                            -- From: tax_amount decimal(20,4)
    base_tax_amount NUMERIC,                       -- From: base_tax_amount decimal(20,4)
    row_total NUMERIC NOT NULL,                    -- From: row_total decimal(20,4)
    base_row_total NUMERIC NOT NULL,               -- From: base_row_total decimal(20,4)
    row_total_with_discount NUMERIC,               -- From: row_total_with_discount decimal(20,4)
    row_weight NUMERIC,                            -- From: row_weight decimal(12,4)
    product_type STRING(255),                      -- From: product_type varchar(255)
    base_tax_before_discount NUMERIC,              -- From: base_tax_before_discount decimal(20,4)
    tax_before_discount NUMERIC,                   -- From: tax_before_discount decimal(20,4)
    original_custom_price NUMERIC,                 -- From: original_custom_price decimal(12,4)
    redirect_url STRING(255),                      -- From: redirect_url varchar(255)
    base_cost NUMERIC,                             -- From: base_cost decimal(12,4)
    price_incl_tax NUMERIC,                        -- From: price_incl_tax decimal(20,4)
    base_price_incl_tax NUMERIC,                   -- From: base_price_incl_tax decimal(20,4)
    row_total_incl_tax NUMERIC,                    -- From: row_total_incl_tax decimal(20,4)
    base_row_total_incl_tax NUMERIC,               -- From: base_row_total_incl_tax decimal(20,4)
    discount_tax_compensation_amount NUMERIC,      -- From: discount_tax_compensation_amount decimal(20,4)
    base_discount_tax_compensation_amount NUMERIC, -- From: base_discount_tax_compensation_amount decimal(20,4)
    weee_tax_applied STRING(MAX),                  -- From: weee_tax_applied text
    weee_tax_applied_amount NUMERIC,               -- From: weee_tax_applied_amount decimal(12,4)
    weee_tax_applied_row_amount NUMERIC,           -- From: weee_tax_applied_row_amount decimal(12,4)
    weee_tax_disposition NUMERIC,                  -- From: weee_tax_disposition decimal(12,4)
    weee_tax_row_disposition NUMERIC,              -- From: weee_tax_row_disposition decimal(12,4)
    base_weee_tax_applied_amount NUMERIC,          -- From: base_weee_tax_applied_amount decimal(12,4)
    base_weee_tax_applied_row_amnt NUMERIC,        -- From: base_weee_tax_applied_row_amnt decimal(12,4)
    base_weee_tax_disposition NUMERIC,             -- From: base_weee_tax_disposition decimal(12,4)
    base_weee_tax_row_disposition NUMERIC,         -- From: base_weee_tax_row_disposition decimal(12,4)
    gift_message_id INT64,                         -- From: gift_message_id int(11)
    free_shipping INT64 NOT NULL                   -- From: free_shipping smallint(6)
) PRIMARY KEY (item_id);

DROP table quote_item_option;

CREATE TABLE quote_item_option (
    option_id STRING(MAX) NOT NULL,  -- From: option_id int(11)
    item_id STRING(MAX) NOT NULL,    -- From: item_id int(11)
    product_id INT64 NOT NULL, -- From: product_id int(11)
    code STRING(255) NOT NULL, -- From: code varchar(255)
    value STRING(MAX)          -- From: value text
) PRIMARY KEY (option_id);

DROP table wishlist_item;

CREATE TABLE wishlist_item (
    wishlist_item_id STRING(MAX) NOT NULL, -- From: wishlist_item_id int(11)
    wishlist_id INT64 NOT NULL,      -- From: wishlist_id int(11)
    product_id INT64 NOT NULL,       -- From: product_id int(11)
    store_id INT64,                  -- From: store_id smallint(6)
    added_at TIMESTAMP,              -- From: added_at timestamp
    description STRING(MAX),         -- From: description text
    qty NUMERIC NOT NULL             -- From: qty decimal(12,4)
) PRIMARY KEY (wishlist_item_id);

DROP table wishlist_item_option;

CREATE TABLE wishlist_item_option (
    option_id STRING(MAX) NOT NULL,        -- From: option_id int(11)
    wishlist_item_id STRING(MAX) NOT NULL, -- From: wishlist_item_id int(11)
    product_id INT64 NOT NULL,       -- From: product_id int(11)
    code STRING(255) NOT NULL,       -- From: code varchar(255)
    value STRING(MAX)                -- From: value text
) PRIMARY KEY (option_id);



DROP table customer_visitor;

CREATE TABLE customer_visitor (
    visitor_id STRING(MAX) NOT NULL,        -- From: visitor_id bigint(20)
    customer_id INT64,                -- From: customer_id int(11)
    session_id STRING(64),            -- From: session_id varchar(64)
    last_visit_at TIMESTAMP NOT NULL  -- From: last_visit_at timestamp
) PRIMARY KEY (visitor_id);


DROP table vault_payment_token;

CREATE TABLE vault_payment_token (
    entity_id INT64 NOT NULL,                 -- From: entity_id int(11)
    customer_id INT64,                        -- From: customer_id int(11)
    public_hash STRING(128) NOT NULL,         -- From: public_hash varchar(128)
    payment_method_code STRING(128) NOT NULL, -- From: payment_method_code varchar(128)
    type STRING(128) NOT NULL,                -- From: type varchar(128)
    created_at TIMESTAMP NOT NULL,            -- From: created_at timestamp
    expires_at TIMESTAMP,                     -- From: expires_at timestamp
    gateway_token STRING(255) NOT NULL,       -- From: gateway_token varchar(255)
    details STRING(MAX),                      -- From: details text
    is_active INT64 NOT NULL,                  -- From: is_active tinyint(1)
    is_visible INT64 NOT NULL                  -- From: is_visible tinyint(1)
) PRIMARY KEY (entity_id);



DROP table quote_address;
CREATE TABLE quote_address (
    address_id STRING(MAX) NOT NULL,                            -- From: address_id int(11)
    quote_id INT64 NOT NULL,                              -- From: quote_id int(11)
    created_at TIMESTAMP NOT NULL,                        -- From: created_at timestamp
    updated_at TIMESTAMP NOT NULL,                        -- From: updated_at timestamp
    customer_id INT64,                                    -- From: customer_id int(11)
    save_in_address_book INT64,                           -- From: save_in_address_book smallint(6)
    customer_address_id INT64,                            -- From: customer_address_id int(11)
    address_type STRING(10),                              -- From: address_type varchar(10)
    email STRING(255),                                    -- From: email varchar(255)
    prefix STRING(40),                                    -- From: prefix varchar(40)
    firstname STRING(255),                                -- From: firstname varchar(255)
    middlename STRING(40),                                -- From: middlename varchar(40)
    lastname STRING(255),                                 -- From: lastname varchar(255)
    suffix STRING(40),                                    -- From: suffix varchar(40)
    company STRING(255),                                  -- From: company varchar(255)
    street STRING(255),                                   -- From: street varchar(255)
    city STRING(255),                                     -- From: city varchar(255)
    region STRING(255),                                   -- From: region varchar(255)
    region_id INT64,                                      -- From: region_id int(11)
    postcode STRING(20),                                  -- From: postcode varchar(20)
    country_id STRING(30),                                -- From: country_id varchar(30)
    telephone STRING(255),                                -- From: telephone varchar(255)
    fax STRING(255),                                      -- From: fax varchar(255)
    same_as_billing INT64 NOT NULL,                       -- From: same_as_billing smallint(6)
    collect_shipping_rates INT64,                -- From: collect_shipping_rates smallint(6)
    shipping_method STRING(120),                          -- From: shipping_method varchar(120)
    shipping_description STRING(255),                     -- From: shipping_description varchar(255)
    weight NUMERIC,                              -- From: weight decimal(12,4)
    subtotal NUMERIC NOT NULL,                            -- From: subtotal decimal(20,4)
    base_subtotal NUMERIC NOT NULL,                       -- From: base_subtotal decimal(20,4)
    subtotal_with_discount NUMERIC NOT NULL,              -- From: subtotal_with_discount decimal(20,4)
    base_subtotal_with_discount NUMERIC NOT NULL,         -- From: base_subtotal_with_discount decimal(20,4)
    tax_amount NUMERIC NOT NULL,                          -- From: tax_amount decimal(20,4)
    base_tax_amount NUMERIC NOT NULL,                     -- From: base_tax_amount decimal(20,4)
    shipping_amount NUMERIC NOT NULL,                     -- From: shipping_amount decimal(20,4)
    base_shipping_amount NUMERIC NOT NULL,                -- From: base_shipping_amount decimal(20,4)
    shipping_tax_amount NUMERIC,                          -- From: shipping_tax_amount decimal(20,4)
    base_shipping_tax_amount NUMERIC,                     -- From: base_shipping_tax_amount decimal(20,4)
    discount_amount NUMERIC NOT NULL,                     -- From: discount_amount decimal(20,4)
    base_discount_amount NUMERIC NOT NULL,                -- From: base_discount_amount decimal(20,4)
    grand_total NUMERIC NOT NULL,                         -- From: grand_total decimal(20,4)
    base_grand_total NUMERIC NOT NULL,                    -- From: base_grand_total decimal(20,4)
    customer_notes STRING(MAX),                           -- From: customer_notes text
    applied_taxes STRING(MAX),                            -- From: applied_taxes text
    discount_description STRING(255),                     -- From: discount_description varchar(255)
    shipping_discount_amount NUMERIC,                     -- From: shipping_discount_amount decimal(20,4)
    base_shipping_discount_amount NUMERIC,                -- From: base_shipping_discount_amount decimal(20,4)
    subtotal_incl_tax NUMERIC,                            -- From: subtotal_incl_tax decimal(20,4)
    base_subtotal_total_incl_tax NUMERIC,                 -- From: base_subtotal_total_incl_tax decimal(20,4)
    discount_tax_compensation_amount NUMERIC,             -- From: discount_tax_compensation_amount decimal(20,4)
    base_discount_tax_compensation_amount NUMERIC,        -- From: base_discount_tax_compensation_amount decimal(20,4)
    shipping_discount_tax_compensation_amount NUMERIC,    -- From: shipping_discount_tax_compensation_amount decimal(20,4)
    base_shipping_discount_tax_compensation_amnt NUMERIC, -- From: base_shipping_discount_tax_compensation_amnt decimal(20,4)
    shipping_incl_tax NUMERIC,                            -- From: shipping_incl_tax decimal(20,4)
    base_shipping_incl_tax NUMERIC,                       -- From: base_shipping_incl_tax decimal(20,4)
    vat_id STRING(MAX),                                   -- From: vat_id text
    vat_is_valid INT64,                                   -- From: vat_is_valid smallint(6)
    vat_request_id STRING(MAX),                           -- From: vat_request_id text
    vat_request_date STRING(MAX),                         -- From: vat_request_date text
    vat_request_success INT64,                            -- From: vat_request_success smallint(6)
    validated_country_code STRING(MAX),                   -- From: validated_country_code text
    validated_vat_number STRING(MAX),                     -- From: validated_vat_number text
    gift_message_id INT64,                                -- From: gift_message_id int(11)
    free_shipping INT64 NOT NULL                          -- From: free_shipping smallint(6)
) PRIMARY KEY (address_id);


DROP table quote_address_item;

CREATE TABLE quote_address_item (
    address_item_id STRING(MAX) NOT NULL,                -- From: address_item_id int(11)
    parent_item_id INT64,                          -- From: parent_item_id int(11)
    quote_address_id STRING(MAX) NOT NULL,               -- From: quote_address_id int(11)
    quote_item_id INT64 NOT NULL,                  -- From: quote_item_id int(11)
    created_at TIMESTAMP NOT NULL,                 -- From: created_at timestamp
    updated_at TIMESTAMP NOT NULL,                 -- From: updated_at timestamp
    applied_rule_ids STRING(MAX),                  -- From: applied_rule_ids text
    additional_data STRING(MAX),                   -- From: additional_data text
    weight NUMERIC,                                -- From: weight decimal(12,4)
    qty NUMERIC NOT NULL,                          -- From: qty decimal(12,4)
    discount_amount NUMERIC,                       -- From: discount_amount decimal(20,4)
    tax_amount NUMERIC,                            -- From: tax_amount decimal(20,4)
    row_total NUMERIC NOT NULL,                    -- From: row_total decimal(20,4)
    base_row_total NUMERIC NOT NULL,               -- From: base_row_total decimal(20,4)
    row_total_with_discount NUMERIC,               -- From: row_total_with_discount decimal(20,4)
    base_discount_amount NUMERIC,                  -- From: base_discount_amount decimal(20,4)
    base_tax_amount NUMERIC,                       -- From: base_tax_amount decimal(20,4)
    row_weight NUMERIC,                            -- From: row_weight decimal(12,4)
    product_id INT64,                              -- From: product_id int(11)
    super_product_id INT64,                        -- From: super_product_id int(11)
    parent_product_id INT64,                       -- From: parent_product_id int(11)
    store_id INT64,                                -- From: store_id smallint(6)
    sku STRING(255),                               -- From: sku varchar(255)
    image STRING(255),                             -- From: image varchar(255)
    name STRING(255),                              -- From: name varchar(255)
    description STRING(MAX),                       -- From: description text
    is_qty_decimal INT64,                          -- From: is_qty_decimal int(11)
    price NUMERIC,                                 -- From: price decimal(12,4)
    discount_percent NUMERIC,                      -- From: discount_percent decimal(12,4)
    no_discount INT64,                             -- From: no_discount int(11)
    tax_percent NUMERIC,                           -- From: tax_percent decimal(12,4)
    base_price NUMERIC,                            -- From: base_price decimal(12,4)
    base_cost NUMERIC,                             -- From: base_cost decimal(12,4)
    price_incl_tax NUMERIC,                        -- From: price_incl_tax decimal(20,4)
    base_price_incl_tax NUMERIC,                   -- From: base_price_incl_tax decimal(20,4)
    row_total_incl_tax NUMERIC,                    -- From: row_total_incl_tax decimal(20,4)
    base_row_total_incl_tax NUMERIC,               -- From: base_row_total_incl_tax decimal(20,4)
    discount_tax_compensation_amount NUMERIC,      -- From: discount_tax_compensation_amount decimal(20,4)
    base_discount_tax_compensation_amount NUMERIC, -- From: base_discount_tax_compensation_amount decimal(20,4)
    gift_message_id INT64,                         -- From: gift_message_id int(11)
    free_shipping INT64                            -- From: free_shipping int(11)
) PRIMARY KEY (address_item_id);

ALTER TABLE quote_shipping_rate DROP COLUMN address_id;
ALTER TABLE quote_shipping_rate ADD COLUMN address_id STRING(MAX);
