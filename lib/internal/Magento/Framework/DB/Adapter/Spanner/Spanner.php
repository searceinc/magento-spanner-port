<?php

namespace Magento\Framework\DB\Adapter\Spanner;

use Google\Cloud\Spanner\SpannerClient;
use Magento\Framework\DB\Adapter\Spanner\SpannerInterface;

/**
 * Cloud Spanner database adapter
 *
 */

class Spanner
{

    protected $PROJECT_ID = 'mag-project';
    protected $INSTANCE  = 'test-instance';
    protected $DATABASE  = 'magentocs';
    protected $IS_EMULATOR = true;

    protected $_connection = null;

    /**
     * Constructor
     *
     * @param StringUtils $string
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param SelectFactory $selectFactory
     * @param array $config
     * @param SerializerInterface|null $serializer
     */

    public function __construct() {
        $this->_connect();
    }

    /**
     * Convert date to DB format
     *
     * @param int|string|\DateTimeInterface $date
     * @return \Zend_Db_Expr
     */
    public function convertDate($date)
    {
        return $this->formatDate($date, false);
    }

    /**
     * Convert date and time to DB format
     *
     * @param int|string|\DateTimeInterface $datetime
     * @return \Zend_Db_Expr
     */
    public function convertDateTime($datetime)
    {
        return $this->formatDate($datetime, true);
    }

    /**
     * Creates a PDO object and connects to the database.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }
        $spanner = new SpannerClient([ 'projectId' => $this->PROJECT_ID ]);
        $instance = $spanner->instance($this->INSTANCE);
        $database = $instance->database($this->DATABASE);
        $this->_connection = $database;
    }

    /**
     * Run RAW Query
     *
     * @param string $sql
     * @throws \exception
     */
    public function rawQuery($sql)
    {
        try {
            $result = $this->query($sql);
        } catch (exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * Run RAW query and Fetch First row
     *
     * @param string $sql
     * @param string|int $field
     * @return mixed|null
     */
    public function rawFetchRow($sql, $field = null)
    {
        $result = $this->rawQuery($sql);
        if (!$result) {
            return false;
        }

        $row = $this->fetch($result);
        if (!$row) {
            return false;
        }

        if (empty($field)) {
            return $row;
        } else {
            return $row[$field] ?? false;
        }
    }

    public function fetchOne($data)
    {
        $items = [];
        foreach ($data as $d) {
            $items[] = $d;
        }
        return $items[0];
    }

    public function fetch($data)
    {
        $items = [];
        foreach ($data as $d) {
            $items[] = $d;
        }
        return $items;
    }

    public function fetchAll($sql)
    {
        try {
            
            $result = $this->query($sql);
            return $this->fetch($result);

        } catch (exception $e) {
            throw $e;
        }

    }

    
    /**
     * Special handling for PDO query().
     *
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function query($sql, $bind = [])
    {
        try {
            $results = $this->_connection->execute($sql);
            return $results;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Allows multiple queries
     *
     * Allows multiple queries -- to safeguard against SQL injection, USE CAUTION and verify that input
     * cannot be tampered with.
     * Special handling for PDO query().
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Pdo|void
     * @throws Zend_Db_Adapter_Exception To re-throw \PDOException.
     * @throws LocalizedException In case multiple queries are attempted at once, to protect from SQL injection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @deprecated 101.0.0
     */
    public function multiQuery($sql, $bind = [])
    {
        return $this->query($sql, $bind);
    }

    /**
     * Unquote raw string (use for auto-bind)
     *
     * @param string $string
     * @return string
     */
    protected function _unQuote($string)
    {
        $translate = [
            "\\000" => "\000",
            "\\n"   => "\n",
            "\\r"   => "\r",
            "\\\\"  => "\\",
            "\'"    => "'",
            "\\\""  => "\"",
            "\\032" => "\032",
        ];
        return strtr($string, $translate);
    }

    public function insertMultiple($table, array $data)
    {
        $row = reset($data);
        // support insert syntaxes
        if (!is_array($row)) {
            return $this->insert($table, $data);
        }

        // validate data array
        $cols = array_keys($row);
        $insertArray = [];
        foreach ($data as $row) {
            $line = [];
            if (array_diff($cols, array_keys($row))) {
                throw new \Zend_Db_Exception('Invalid data for insert');
            }
            foreach ($cols as $field) {
                $line[] = $row[$field];
            }
            $insertArray[] = $line;
        }
        unset($row);

        return $this->insertArray($table, $cols, $insertArray);
    }

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
     * @param string $table
     * @param string[] $columns
     * @param array $data
     * @param int $strategy
     * @return int
     * @throws \Zend_Db_Exception
     */
    public function insertArray($table, array $columns, array $data, $strategy = 0)
    {
        $values       = [];
        $bind         = [];
        $columnsCount = count($columns);
        foreach ($data as $row) {
            if (is_array($row) && $columnsCount != count($row)) {
                throw new \Zend_Db_Exception('Invalid data for insert');
            }
            $values[] = $this->_prepareInsertData($row, $bind);
        }

        switch ($strategy) {
            case self::REPLACE:
                $query = $this->_getReplaceSqlQuery($table, $columns, $values);
                break;
            default:
                $query = $this->_getInsertSqlQuery($table, $columns, $values, $strategy);
        }

        // execute the statement and return the number of affected rows
        $stmt   = $this->query($query, $bind);
        $result = $stmt->rowCount();

        return $result;
    }

    /**
     * Format Date to internal database date format
     *
     * @param int|string|\DateTimeInterface $date
     * @param bool $includeTime
     * @return \Zend_Db_Expr
     */
    public function formatDate($date, $includeTime = true)
    {
        $date = $this->dateTime->formatDate($date, $includeTime);

        if ($date === null) {
            return new \Zend_Db_Expr('NULL');
        }

        return new \Zend_Db_Expr($this->quote($date));
    }

    
    /**
     * Closes the connection.
     *
     * @since 102.0.4
     */
    public function closeConnection()
    {
        if (!empty($this->_config['port'])) {
            $this->_config['host'] = implode(':', [$this->_config['host'], $this->_config['port']]);
            unset($this->_config['port']);
        }
    }
}
