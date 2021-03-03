<?php

namespace Magento\Framework\DB\Adapter\Spanner;

/**
 * Cloud Spanner Database Adapter Interface
 *
 */
interface SpannerInterface
{
    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  string $sql
     * @return mixed|null
     */
    public function query(string $sql);

    /**
     * Fetches all SQL result rows as a sequential array.
     * Uses the current fetchMode for the adapter.
     *
     * @param string $sql
     * @return array
     */
    public function fetchAll(string $sql);

    /**
     * Fetches the first row of the SQL result.
     * Uses the current fetchMode for the adapter.
     *
     * @param string $sql
     * @return object
     */
    public function fetchRow(string $sql);

    /**
     * Returns first row
     *
     * @param object $data
     * @return object
     */
    public function fetchOne(object $data);

    /**
     * Format Date to internal database date format
     *
     * @return string
     */
    public function formatDate();

    /**
     * Returns auto increment field if exists
     *
     * @return string
     */
    public function getAutoIncrement();

    /**
     * Insert multiple rows in multiple tables
     *
     * @param array $table
     * @param array $data
     * @return Commit timestamp
     */
    public function insertArray(array $table, array $data);

    /**
     * Insert multiple rows in single table
     *
     * @param string $table
     * @param array $data
     * @return Commit timestamp
     */
    public function insert(string $table, array $data);

    /**
     * Single col update in the table
     *
     * @param string $table
     * @param array $bind
     * @return Commit timestamp
     */
    public function update(string $table, array $bind);

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param string $table
     * @param string $where
     * @param array $params
     * @return Commit timestamp
     */
    public function delete(string $table, string $where, array $params);

    /**
     * Cast the column with type
     *
     * @param string $sql
     * @param string $col
     * @param string $type
     * @return string
     */
    public function addCast(string $sql, string $col, string $type);

    /**
     * Formats the sql for Cloud Spanner
     * Example 
     * Input SQL : <select statement> WHERE (`product_id` = '340') ORDER BY position  ASC
     * Output SQL <select statement> WHERE (`product_id` = 340) ORDER BY position  ASC
     * In the above example integer `340` is sanitized by removing single quotes.
     * Sanitization is required since Cloud Spanner is strict type
     *
     * @param string $sql
     * @return string $sql
     */
    public function sanitizeSql(string $sql);

    /**
     * Convert to T and Z iso format
     *
     * @param string $date
     * @return string
     */
    public function convertDate(string $date);
}
