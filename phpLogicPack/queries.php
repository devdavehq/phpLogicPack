<?php

include_once './conndb.php';

class Query {

    /**
     * Executes a SQL query with the given parameters and types.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params The parameters to bind to the query.
     * @param string $types The types of the parameters (e.g., 's' for string, 'i' for integer).
     * @return mixed The result of the query execution, which can vary based on the query type.
     * @throws Exception If the query fails to execute or if type specifiers do not match parameters.
     */
    public static function executeQuery($sql, $params = [], $types = "") {
        // Get the database connection from Dbconn class
        $conn = Dbconn::getConnection();
        $stmt = null;

        // Validate SQL query to prevent SQL injection
        if (!self::isValidSql($sql)) {
            error_log("Invalid SQL query: " . $sql);
            return false; // Return false or handle as needed
        }

        try {
            // Prepare the SQL statement
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }

            // Bind parameters if they exist
            if (!empty($params)) {
                // Validate and set types if none are provided
                if ($types === "") {
                    $types = str_repeat("s", count($params)); // Default to string
                }

                // Validate type specifiers count
                if (strlen($types) !== count($params)) {
                    throw new Exception("Type specifiers count does not match parameters count.");
                }

                // Validate types
                foreach ($params as $key => $param) {
                    if ($types[$key] === 'i' && !is_int($param)) {
                        throw new Exception("Parameter at index $key must be an integer.");
                    } elseif ($types[$key] === 'd' && !is_float($param)) {
                        throw new Exception("Parameter at index $key must be a float.");
                    } elseif ($types[$key] === 's' && !is_string($param)) {
                        throw new Exception("Parameter at index $key must be a string.");
                    }
                }

                // Bind parameters directly
                $stmt->bind_param($types, ...$params);
            }

            // Execute the statement
            $success = $stmt->execute();

            if (!$success) {
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }

            // Determine the type of query
            $queryType = strtoupper(explode(' ', trim($sql))[0]);

            // Handle different types of queries
            switch ($queryType) {
                case 'SELECT':
                    $result = $stmt->get_result();
                    if ($result === false) {
                        throw new Exception("Failed to get result set: " . $stmt->error);
                    }
                    $data = $result->fetch_all(MYSQLI_ASSOC);
                    return $data; // Return fetched data for SELECT queries

                case 'INSERT':
                    return $stmt->insert_id; // Return the insert ID for INSERT queries

                case 'UPDATE':
                case 'DELETE':
                    return $stmt->affected_rows; // Return the number of affected rows for UPDATE/DELETE queries

                default:
                    throw new Exception("Unsupported query type: " . $queryType);
            }
        } catch (Exception $e) {
            // Log the error instead of throwing it directly
            error_log($e->getMessage());
            return false; // Return false or handle as needed
        } finally {
            // Ensure the statement is closed
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    /**
     * Validates the SQL query to prevent SQL injection.
     *
     * @param string $sql The SQL query to validate.
     * @return bool True if the SQL query is valid, false otherwise.
     */
    private static function isValidSql($sql) {
        // Implement a basic validation logic (e.g., whitelisting allowed commands)
        $allowedCommands = ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];
        $queryType = strtoupper(explode(' ', trim($sql))[0]);
        return in_array($queryType, $allowedCommands);
    }
}
