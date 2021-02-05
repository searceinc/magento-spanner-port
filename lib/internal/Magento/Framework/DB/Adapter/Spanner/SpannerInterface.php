<?php

namespace Magento\Framework\DB\Adapter;

use Magento\Framework\DB\Ddl\Table;

/**
 * Magento Database Adapter Interface
 *
 * @api
 * @since 100.0.2
 */
interface SpannerInterface
{
    /**
     * Creates and returns a new \Magento\Framework\DB\Select object for this adapter.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function select();

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     */
    public function insertOnDuplicate($table, array $data, array $fields = []);

    /**
     * Inserts a table multiply rows with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $data Column-value pairs or array of Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insertMultiple($table, array $data);

    /**
     * Insert array into a table based on columns definition
     *
     * $data can be represented as:
     * - arrays of values ordered according to columns in $columns array
     *      array(
     *          array('value1', 'value2'),
     *          array('value3', 'value4'),
     *      )
     * - array of values, if $columns contains only one column
     *      array('value1', 'value2')
     *
     * @param   string $table
     * @param   string[] $columns the data array column map
     * @param   array $data
     * @return  int
     */
    public function insertArray($table, array $columns, array $data);

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind);

    /**
     * Inserts a table row with specified data
     *
     * Special for Zero values to identity column
     *
     * @param string $table
     * @param array $bind
     * @return int The number of affected rows.
     */
    public function update($table, array $bind, $where = '');

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed $table The table to update.
     * @param  mixed $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '');

    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed $sql The SQL statement with placeholders.
     *                      May be a string or \Magento\Framework\DB\Select.
     * @param  mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Interface
     */
    public function query($sql, $bind = []);

    /**
     * Fetches all SQL result rows as a sequential array.
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @param mixed $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchAll($sql, $bind = [], $fetchMode = null);

    /**
     * Fetches the first row of the SQL result.
     *
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Magento\Framework\DB\Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @param mixed $fetchMode Override current fetch mode.
     * @return mixed Array, object, or scalar depending on fetch mode.
     */
    public function fetchRow($sql, $bind = [], $fetchMode = null);

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
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchOne($sql, $bind = []);

    /**
     * Format Date to internal database date format
     *
     * @param int|string|\DateTimeInterface $date
     * @param boolean $includeTime
     * @return \Zend_Db_Expr
     */
    public function formatDate($date, $includeTime = true);

    /**
     * Returns auto increment field if exists
     *
     * @param string $tableName
     * @param string|null $schemaName
     * @return string|bool
     * @since 100.1.0
     */
    public function getAutoIncrementField($tableName, $schemaName = null);
}
