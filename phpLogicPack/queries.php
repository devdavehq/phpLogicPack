<?php

include_once './conndb.php';

class Query {

    public static function executeQuery($sql, $params = [], $types = "") {
        // Get the database connection from Dbconn class
        $conn = Dbconn::getConnection();

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        // Bind parameters if they exist
        if (!empty($params)) {
            // Default to string types if none are provided
            if ($types === "") {
                $types = str_repeat("s", count($params)); // Default to string
            }

            // Validate type specifiers count
            if (strlen($types) !== count($params)) {
                throw new Exception("Type specifiers count does not match parameters count.");
            }

            // Prepare parameter binding
            $bind_names = [$types];
            foreach ($params as $param) {
                $bind_names[] = $param;
            }

            // Call bind_param directly with spread operator
            $stmt->bind_param(...$bind_names);
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
                $stmt->close();
                return $data; // Return fetched data for SELECT queries

            case 'INSERT':
                $insert_id = $stmt->insert_id;
                $stmt->close();
                return $insert_id; // Return the insert ID for INSERT queries

            case 'UPDATE':
            case 'DELETE':
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                return $affected_rows; // Return the number of affected rows for UPDATE/DELETE queries

            default:
                $stmt->close();
                throw new Exception("Unsupported query type: " . $queryType);
        }
    }
}
