<?php

namespace Magento\Framework\DB\Adapter\Spanner;

use Magento\Framework\DB\Ddl\Table;

/**
 * Cloud Spanner Database Adapter Interface
 *
 */
interface SpannerInterface
{

    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed $sql The SQL statement.
     * @return array
     */
    public function query($sql);

    /**
     * Fetches all SQL result rows as a sequential array.
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @return array
     */
    public function fetchAll($sql);

    /**
     * Fetches the first row of the SQL result.
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @return mixed Array, object.
     */
    public function fetchRow($sql);

    /**
     * Fetches all SQL result rows as an associative array.
     *
     * The first column is the key, the entire row array is the
     * value.  You should construct the query to be sure that
     * the first column contains unique values, or else
     * rows with duplicate values in the first column will
     * overwrite previous data.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @return object
     */
    public function fetchOne($sql);

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
     * Insert array into a table based on columns definition
     *
     *
     * @param   array $table
     * @param   array $data
     * @return  Commit timestamp
     */
    public function insertArray(array $table, array $data);

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return Commit timestamp
     */
    public function insert($table, array $bind);

    /**
     * Inserts a table row with specified data
     *
     * Special for Zero values to identity column
     *
     * @param string $table
     * @param string $bind
     * @param string $bind
     * @param string $whereCol
     * @param string $where
     * @return Commit timestamp
     */
    public function update($table, $bind);

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed $table The table to update.
     * @param  mixed $where DELETE WHERE clause(s).
     * @return Commit timestamp
     */
    public function delete($table, $where);

    /**
     * Deletes table rows based on a WHERE clause.
     * @param  string $sql
     * @param  string $col
     * @param  string $type
     * @return string
     */
    public function addCast($sql, $col, $type);

    /**
     * Formates the sql for cloud spanner
     * @param  string $sql
     */
    public function sanitize_sql($sql);
}
