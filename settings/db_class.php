<?php
/**
 * Database Class
 * Handles database connection and prepared statement operations
 */

require_once(__DIR__ . '/db_cred.php');

class db_connection {
    public $db = null;
    public $results = null;

    /**
     * Database connection
     */
    function db_conn() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->db->connect_error) {
            return false;
        }
        
        $this->db->set_charset(DB_CHARSET);
        return $this->db;
    }

    /**
     * Execute write operations (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Array of parameters to bind
     * @param string $types String of parameter types (i, d, s, b)
     * @return bool True on success, false on failure
     */
    function write($sql, $params = [], $types = '') {
        $connection = $this->db_conn();
        
        if (!$connection) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        $stmt = $connection->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Statement preparation failed: " . $connection->error);
        }

        if (!empty($params) && !empty($types)) {
            if (!$stmt->bind_param($types, ...$params)) {
                throw new Exception("Parameter binding failed: " . $stmt->error);
            }
        }

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Query execution failed: " . $error);
        }
        
        // Get insert_id before closing statement (for INSERT queries)
        $insert_id = $connection->insert_id;
        $stmt->close();
        
        // Return insert_id if > 0 (successful INSERT), otherwise return true (UPDATE/DELETE)
        // insert_id will be 0 for non-INSERT queries or failed inserts
        return $insert_id > 0 ? $insert_id : true;
    }

    /**
     * Execute read operations (SELECT)
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Array of parameters to bind
     * @param string $types String of parameter types (i, d, s, b)
     * @return array|false Result set or false on failure
     */
    function read($sql, $params = [], $types = '') {
        $connection = $this->db_conn();
        
        if (!$connection) {
            return false;
        }

        $stmt = $connection->prepare($sql);
        
        if (!$stmt) {
            return false;
        }

        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get last inserted ID
     */
    function get_insert_id() {
        return $this->db->insert_id;
    }
}

?>

