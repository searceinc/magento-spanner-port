<?php
namespace Magento\Framework\DB\Adapter\Spanner;

use Google\Cloud\Spanner\SpannerClient;
use Magento\Framework\DB\Adapter\Spanner\SpannerInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Cloud Spanner database adapter
 *
 */

class Spanner implements SpannerInterface
{
    /**
     * Google cloud project id
     * @var string
     */
    protected $PROJECT_ID = 'mag-project';

    /**
     * Google cloud instance name
     * @var string
     */
    protected $INSTANCE  = 'mag-instance';

    /**
     * Cloud spanner database name
     * @var string
     */
    protected $DATABASE  = 'magentocs';

    /**
     * Is cloud spanner emulator
     * @var bool
     */
    protected $IS_EMULATOR = true;

    /**
     * Connection Object
     * Magento\Framework\DB\Adapter\Spanner\SpannerInterface
     */
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
        if($this->IS_EMULATOR) {
            putenv('SPANNER_EMULATOR_HOST=localhost:9010');
        }
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
        if(count($items) > 0 ) {
            return $items[0];
        } else {
            return [];
        }
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
     * @throws Exception
     */
    public function query($sql)
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
     * @throws Exception
     */
    public function multiQuery($sql)
    {
        return $this->query($sql);
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
     * Insert multiple rows in multiple tables
     * @param array $table
     * @param array $data
     * @throws Exception
     */
    public function insertArray(array $table, array $data) 
    {
        try {
            $session = $this->_connection->transaction(['singleUse' => true]);
            for ($i = 0; $i <= count($table); $i++) {
                $session->insertBatch($table[$i], $data[$i]);
            }
            $results = $session->commit();
            return $results;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Insert multiple rows in single table
     * @param string $table
     * @param array $data
     * @throws Exception
     */
    public function insert($table, array $data) 
    {
        try {
            $results = $this->_connection->transaction(['singleUse' => true])
            ->insertBatch($table, [$data])
            ->commit();
            return $results;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Single col update in the table
     * @param string $table
     * @param array $data
     * @throws Exception
     */
    public function update($table, $bind) 
    {
        try {
            $results = $this->_connection->transaction(['singleUse' => true])
            ->updateBatch($table, [ $bind ])
            ->commit();
            return $results;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete from table
     * @param string $table
     * @param string $where
     * @throws Exception
     */
    public function delete($table, $where) 
    {
        try {
            $sql =  "DELETE FROM ".$table." WHERE ".$where;
            $results = $this->_connection->executeUpdate($sql);
            return $results;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Format Date to T and Z iso format
     * @param string $date
     * @throws Exception
     */
    public function formatDate()
    {
        $date = (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
        return str_replace('+00:00', '.000Z', gmdate('c', strtotime($date)));
    }

    /**
     * Generate UUID
     *
     */
    public function getAutoIncrement() 
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));    
    }

    /**
     * Returns the single row
     * @param string $sql
     * @throws Exception
     */
    public function fetchRow($sql) 
    {
        try {
            $result = $this->query($sql);
            return $this->fetchOne($result);
        } catch (exception $e) {
            throw $e;
        }
    }

    /**
     * Cast the column with type
     * @param string $sql
     * @param string $col
     * @param string $type
     */
    public function addCast($sql, $col, $type) 
    {
       $cast = "cast(".$col." as ".$type.")";
       return str_replace($col, $cast, $sql);
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

    /**
     * Formates the sql for cloud spanner
     * @param string $sql
     */
    public function sanitize_sql($sql)
    {
        if (preg_match_all("/('[^']*')/", $sql, $m)) {
            $matches = array_shift($m);
            for($i = 0; $i < count($matches); $i++) {
                $curr =  $matches[$i];
                $curr = filter_var($curr, FILTER_SANITIZE_NUMBER_INT);
                if (is_numeric($curr))
                {
                    $sql = str_replace($matches[$i],(int) $curr, $sql);
                }
            }
        }

        $sql = str_replace('RAND()','1', $sql);

        return $sql;
    }

    /**
     * Convert to T and Z iso format
     * @param string $date
     * @throws Exception
     */
    public function convertDate($date)
    {
        $date = (new \DateTime($date))->format(DateTime::DATETIME_PHP_FORMAT);
        return str_replace('+00:00', '.000Z', gmdate('c', strtotime($date)));
    }
}
