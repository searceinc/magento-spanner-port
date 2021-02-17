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
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string $sql
     * @return array
     */
    public function fetchAll(string $sql);

    /**
     * Fetches the first row of the SQL result.
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string $sql
     * @return object
     */
    public function fetchRow(string $sql);

    /**
     * Returns first row
     *
     * @param array $data
     * @return object
     */
    public function fetchOne(array $data);

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
     * @param string $table
     * @param array $data
     * @return Commit timestamp
     */
    public function insertArray(string $table, array $data);

    /**
     * Insert multiple rows in single table
     * @param string $table
     * @param array $data
     * @return Commit timestamp
     */
    public function insert(string $table, array $data);

    /**
     * Single col update in the table
     *
     * @param string $table
     * @param string $bind
     * @return Commit timestamp
     */
    public function update(string $table, string $bind);

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param string $table
     * @param string $where
     * @return Commit timestamp
     */
    public function delete(string $table, string $where);

    /**
     * Cast the column with type
     * @param string $sql
     * @param string $col
     * @param string $type
     * @return string
     */
    public function addCast(string $sql, string $col, string $type);

    /**
     * Formates the sql for cloud spanner
     * @param  string $sql
     * @return string| SQL statement
     */
    public function sanitizeSql($sql);
}
