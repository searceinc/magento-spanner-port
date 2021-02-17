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
     * Format Date to T and Z iso format
     * @param string $date
     * @return string
     */
    public function formatDate(string $date);

    /**
     * Returns auto increment field if exists
     *
     * @return string
     */
    public function getAutoIncrement();

    /**
     * Insert multiple rows in multiple tables
     * @param array $table
     * @param array $data
     * @return Commit timestamp
     */
    public function insertArray(array $table, array $data);

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
     * @param string $bindCol
     * @param string $bind
     * @param string $whereCol
     * @param string $where
     * @param array $data
     * @return Commit timestamp
     */
    public function update(string $table, string $bindCol, string $bind, string $whereCol, string $where);

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

}
