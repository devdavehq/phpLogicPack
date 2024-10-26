<?php

class Dbconn {

    private static $conn = null;
    private static $host = 'localhost';
    private static $user = 'root';
    private static $password = '';
    private static $dbname = 'unimart';

    // Get the static connection
    public static function getConnection() {
        if (self::$conn === null) {
            try {
                self::$conn = new mysqli(self::$host, self::$user, self::$password, self::$dbname);

                if (self::$conn->connect_error) {
                    throw new Exception('Connection Failed: ' . self::$conn->connect_error);
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return self::$conn;
    }

    // Close the static connection
    public static function closeConnection() {
        if (self::$conn !== null) {
            self::$conn->close();
            self::$conn = null;
        }
    }
}




 
// Include the Router logic
// include_once './phpLogicPack/router.php';

// Define a route for the GET request to /users/:id
 // Router::get('/users/:id', function ($data, $params) {
  //  return json_encode(['data' => $data, 'params' => $params]);
//});

// Handle the request
// Router::handleRequest(); 
