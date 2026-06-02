<?php

class Database
{
    private static $instance = null;
    private $connection = null;
    private $serverName = '172.16.0.239';
    private $database = 'SFI_DWH';
    private $username = 'usersfi';
    private $password = 'sfi.100';
    private $maxRetries = 3;
    private $retryDelay = 2;

    private function __construct()
    {
        try {
            $this->connect();
        } catch (Exception $e) {
            error_log('DATABASE CONNECTION ERROR : ' . $e->getMessage());
            throw new Exception('GAGAL TERHUBUNG KE DATABASE');
        }
    }

    private function connect()
    {
        $connectionInfo = [
            'Database' => $this->database,
            'UID' => $this->username,
            'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
            'ReturnDatesAsStrings' => true,
            'LoginTimeout' => 60,
            'ConnectionPooling' => 1,
            'MultipleActiveResultSets' => false,
            'ConnectRetryCount' => 3,
            'ConnectRetryInterval' => 10,
            'TransactionIsolation' => SQLSRV_TXN_READ_COMMITTED,
            'Encrypt' => false,
            'TrustServerCertificate' => true
        ];

        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            $this->connection = @sqlsrv_connect($this->serverName, $connectionInfo);

            if ($this->connection !== false) {
                error_log('DATABASE CONNECTED SUCCESS');
                return;
            }

            $lastError = $this->getLastError();
            error_log('CONNECTION ATTEMPT ' . $attempt . ' FAILED : ' . $lastError);

            if ($attempt < $this->maxRetries) {
                sleep($this->retryDelay);
            }
        }

        throw new Exception('CONNECTION FAILED AFTER ' . $this->maxRetries);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        if (!$this->isConnected()) {
            $this->reconnect();
        }
        return $this->connection;
    }

    private function isConnected()
    {
        if ($this->connection === false || $this->connection === null) {
            return false;
        }

        $sql = 'SELECT 1';
        $stmt = @sqlsrv_query($this->connection, $sql, [], ['QueryTimeout' => 5]);

        if ($stmt === false) {
            return false;
        }

        sqlsrv_free_stmt($stmt);
        return true;
    }

    private function reconnect()
    {
        error_log('RECONNECTING DATABASE');
        $this->closeConnection();
        $this->connect();
    }

    public function query($sql, $params = [])
    {
        if (!$this->isConnected()) {
            $this->reconnect();
        }

        $options = ['QueryTimeout' => 30];
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            if (empty($params)) {
                $stmt = @sqlsrv_query($this->connection, $sql, [], $options);
            } else {
                $stmt = @sqlsrv_query($this->connection, $sql, $params, $options);
            }

            if ($stmt !== false) {
                return $stmt;
            }

            $lastError = $this->getLastError();
            error_log("QUERY ATTEMPT {$attempt} FAILED : {$lastError}");

            if ($this->isConnectionError($lastError)) {
                error_log('CONNECTION ERROR, RECONNECTING');
                $this->reconnect();
            }

            if ($attempt < $this->maxRetries) {
                sleep(1);
            }
        }

        error_log("QUERY FAILED AFTER {$this->maxRetries} ATTEMPTS: {$lastError} | SQL: {$sql}");
        throw new Exception('ERROR RUNNING QUERY');
    }

    private function isConnectionError($error)
    {
        $connectionErrors = [
            'COMMUNICATION FAILURE',
            'CONNECTION RESET BY PEER',
            'CONNECTION TIMED OUT',
            'LOST CONNECTION',
            'SERVER UNAVAILABLE',
            'BROKEN PIPE ERROR',
            'CONNECTION LOST'
        ];

        foreach ($connectionErrors as $pattern) {
            if (stripos($error, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    public function fetchAll($stmt)
    {
        if ($stmt === false) {
            return [];
        }

        $results = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }

        sqlsrv_free_stmt($stmt);
        return $results;
    }

    public function fetchOne($stmt)
    {
        if ($stmt === false) {
            return null;
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        return $row;
    }

    private function getLastError()
    {
        $errors = sqlsrv_errors(SQLSRV_ERR_ALL);

        if ($errors === null) {
            return 'DATABASE ERROR';
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = sprintf(
                'SQLSTATE: %s, CODE: %s, MESSAGE: %s',
                $error['SQLSTATE'] ?? 'N/A',
                $error['code'] ?? 'N/A',
                $error['message'] ?? 'N/A'
            );
        }

        return implode(' | ', $errorMessages);
    }

    private function closeConnection()
    {
        if ($this->connection !== null && $this->connection !== false) {
            @sqlsrv_close($this->connection);
            $this->connection = null;
        }
    }

    public function __destruct()
    {
        $this->closeConnection();
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception('CANNOT UNSERIALIZE SINGLETON');
    }
}
