<?php
namespace Magento\Framework\DB\Adapter\Spanner;

use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;
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
    private $project_id = 'mag-project';

    /**
     * Google cloud instance name
     * @var string
     */
    private $instance  = 'mag-instance';

    /**
     * Cloud Spanner database name
     * @var string
     */
    private $database  = 'magentocs';

    /**
     * Is Cloud Spanner emulator
     * @var bool
     */
    private $is_emulator = true;

    /**
     * Connection Object
     * Magento\Framework\DB\Adapter\Spanner\SpannerInterface
     */
    private $_connection = null;

    /**
     * Constructor
     * init connection
     */

    public function __construct() {
        $this->_connect();
    }

    /**
     * Creates a Cloud Spanner object and connects to the database.
     *
     * @return void
     */
    protected function _connect()
    {
        if ($this->is_emulator) {
            putenv('SPANNER_EMULATOR_HOST=localhost:9010');
        }
        if ($this->_connection) {
            return;
        }
        $spanner = new SpannerClient([ 'projectId' => $this->project_id ]);
        $instance = $spanner->instance($this->instance);
        $this->_connection = $instance->database($this->database);
    }

    /**
     * Run raw Query
     *
     * @param string $sql
     * @return mixed|null
     */
    public function rawQuery(string $sql)
    {
        $result = $this->query($sql);
        return $result;
    }

    /**
     * Run row query and Fetch data
     *
     * @param string $sql
     * @param string $field
     * @return mixed|null
     */
    public function rawFetchRow(string $sql, string $field = null)
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
     * Returns first row
     *
     * @param object $data
     * @return object
     */
    public function fetchOne(object $data)
    {
        return $data->rows()->current();
    }

    /**
     * Returns all rows
     *
     * @param object $data
     * @return array
     */
    public function fetch(object $data)
    {
        return iterator_to_array($data->rows());
    }

    /**
     * Fetch all rows
     *
     * @param string $sql
     * @return array
     */
    public function fetchAll(string $sql)
    {
        $result = $this->query($sql);
        return $this->fetch($result);
    }

    /**
     * query
     *
     * @param string $sql
     * @return mixed|null
     */
    public function query(string $sql)
    {
        $results = $this->_connection->execute($sql);
        return $results;
    }

    /**
     * Allows multiple queries
     *
     * @param string $sql
     * @return mixed|null
     */
    public function multiQuery(string $sql)
    {
        return $this->query($sql);
    }

    /**
     * Unquote raw string (use for auto-bind)
     *
     * @param string $string
     * @return string
     */
    protected function unQuote(string $string)
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
     * @return Commit timestamp
     */
    public function insertArray(array $table, array $data) 
    {
        $results = $this->_connection->runTransaction(function (Transaction $t) use ($table, $data) {
            for ($i = 0; $i <= count($table); $i++) {
                $t->insertBatch($table[$i], $data[$i]);
            }
            $t->commit();
        });
        
        return $results;
    }

    /**
     * Insert multiple rows in single table
     * @param string $table
     * @param array $data
     * @return Commit timestamp
     */
    public function insert(string $table, array $data) 
    {
        $results = $this->_connection->runTransaction(function (Transaction $t) use ($table, $data) {
            $t->insertBatch($table, [ $data ]);
            $t->commit();
        });
        return $results;
    }

    /**
     * Single col update in the table
     * @param string $table
     * @param array $bind
     * @return Commit timestamp
     */
    public function update(string $table, array $bind) 
    {
        $results = $this->_connection->runTransaction(function (Transaction $t) use ($table, $bind) {
            $t->updateBatch($table, [ $bind ]);
            $t->commit();
        });
        return $results;
    }

    /**
     * Delete from table
     * @param string $table
     * @param string $where
     * @return Commit timestamp
     */
    public function delete(string $table, string $where) 
    {
        $sql = "DELETE FROM ".$table." WHERE ".$where;
        $results = $this->_connection->runTransaction(function (Transaction $t) use ($sql) {
            $rowCount = $t->executeUpdate($sql);
            $t->commit();
        });
        return $results;
    }

    /**
     * Format Date to T and Z iso format
     * @return string
     */
    public function formatDate()
    {
        $date = (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
        return str_replace('+00:00', '.000Z', gmdate('c', strtotime($date)));
    }

    /**
     * Generate UUID
     * @return string
     */
    public function getAutoIncrement() 
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));    
    }

    /**
     * Returns the single row
     * @param string $sql
     * @return object
     */
    public function fetchRow(string $sql) 
    {
        $result = $this->query($sql);
        return $this->fetchOne($result);
    }

    /**
     * Cast the column with type
     * @param string $sql
     * @param string $col
     * @param string $type
     * @return string 
     */
    public function addCast(string $sql, string $col, string $type) 
    {
       $cast = "cast(".$col." as ".$type.")";
       return str_replace($col, $cast, $sql);
    }
    
    /**
     * Closes the connection.
     * @return void
     */
    public function closeConnection()
    {
        if ($this->_connection) {
            $this->_connection->close();
        }
    }

    /**
     * Transforms the query to a legal Spanner query.
     * The basic issue is that MySQL will implicitly perform type conversions
     * for literals in queries. For example, if product_id is a column of type bigint,
     * then both of the following queries will return the same result:
     *   select * from quote_item where product_id = 42
     *   select * from quote_item where product_id = '42'
     * However this is not true for Spanner. Spanner requires that
     * literals match the type of the column. So if product_id is
     * a column of type INT64 in Spanner, then the first query above
     * will be fine, but the second will return an "InvalidArgument" error
     * because one side of the operation = has type INT64, but the other
     * has type STRING.
     * To solve this issue, we transform queries by dropping quotes on
     * literals. Note that we only do this if the matching variable has INT type.
     *
     * @param string $sql
     * @return string $sql
     */
    public function sanitizeSql(string $sql)
    {
        if (preg_match_all("/('[^']*')/", $sql, $m)) {
            $matches = array_shift($m);
            for($i = 0; $i < count($matches); $i++) {
                $curr =  $matches[$i];
                $curr = filter_var($curr, FILTER_SANITIZE_NUMBER_INT);
                if (is_numeric($curr)) {
                    $sql = str_replace($matches[$i], $curr, $sql);
                }
            }
        }

        return $sql;
    }

    /**
     * Convert to T and Z iso format
     *
     * @param string $date
     * @return string
     */
    public function convertDate(string $date)
    {
        if ($date) {
            return str_replace('+00:00', '.000Z', gmdate('c', strtotime($date)));
        }
        
        return "";
    }
}
