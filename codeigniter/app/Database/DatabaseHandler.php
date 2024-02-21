<?php

namespace App\Database;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Database;

class DatabaseHandler
{
    /* Attributes */
    private static ?DatabaseHandler $instance = null;
    private ?\CodeIgniter\Database\BaseConnection $db = null;

    /* Constructors */
    /**
     * Create a databaseHandler
     * @post A databaseHandler is created
     * @post A connection with the database is made
     */
    public function __construct()
    {
        // Singleton pattern
        if (self::$instance != null)
            return;
        self::$instance = $this;

        // Create db connection
        $this->db = db_connect();
    }

    /**
     * Close the database connection
     */
    function __destruct()
    {
        // Closing the db connection
        if ($this->db != null)
            $this->db->close();
    }

    /* Methods */
    /**
     * send a query that requires no additional data
     * @param $query string the query to send
     * @param bool $expectingData true if the query is expecting data
     * @return array|null the result of the query
     */
    public function completeQuery(string $query, bool $expectingData = true): ?array
    {
        try {
            $processedQuery = $this->db->query($query);
        }
        catch(DatabaseException $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                throw $e;
            else
                throw new DatabaseException("Cannot connect to the database. Please try again later.");
        }

        if ($expectingData) return $processedQuery->getResult();
        return null;
    }

    /**
     * Send a query that requires additional data
     * @param $query string the query to send
     * @param $data array the data to send with the query
     * @param bool $expectingData true if the query is expecting data
     * @return array|null the result of the query
     */
    public function query(string $query, array $data, bool $expectingData = true): ?array
    {

        try {
            $processedQuery = $this->db->query($query, $data);
        }
        catch(DatabaseException $e) {
            if (getenv('CI_ENVIRONMENT') == "development")
                throw $e;
            else
                throw new DatabaseException("Cannot connect to the database. Please try again later.");
        }

        if ($expectingData)
            return $processedQuery->getResult();

        return null;
    }

    /**
     * Refresh the database connection
     * @return void
     */
    public function refreshConnection()
    {
        $this->db->reconnect();
    }

    /**
     * Get the instance of the databaseHandler (singleton)
     * @return DatabaseHandler the instance of the databaseHandler
     */
    public static function getInstance(): DatabaseHandler
    {
        // Create a new dbHandler if it didn't already exist
        if (self::$instance == null)
            new DatabaseHandler();

        return self::$instance;
    }
}