<?php
putenv('SPANNER_EMULATOR_HOST=localhost:9010');
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
    protected $INSTANCE  = 'mag-instance';
    protected $DATABASE  = 'magentocs';
    protected $IS_EMULATOR = true;

    protected $_connection = null;

    /**
     * Constructor
     * init connection
     */

    public function __construct() {
        $this->_connect();
    }

    /**
     * Creates a Spanner object and connects to the database.
     *
     * @return void
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

    /**
     * Returns First row
     *
     * @param array $data
     * @return object
     */
    public function fetchOne($data)
    {
        $items = [];
        foreach ($data as $d) {
            $items[] = $d;
        }
        return $items[0];
    }

    /**
     * Returns All row
     *
     * @param array $data
     * @return array
     */
    public function fetch($data)
    {
        $items = [];
        foreach ($data as $d) {
            $items[] = $d;
        }
        return $items;
    }

    /**
     * Fetch all rows
     *
     * @param string $sql
     * @return array
     */
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
     * query
     *
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @throws Exception
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
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @throws LocalizedException In case multiple queries are attempted at once, to protect from SQL injection
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

    
    /**
     * Closes the connection.
     *
     */
    public function closeConnection()
    {
        if ($this->_connection) {
            $this->_connection->close();
        }
    }
}
