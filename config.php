<?php
// Hostname for your URL shortener
$hostname = 'https://open.corp.care2.com';

// PDO connection to the database
// $connection = new PDO('mysql:dbname=shorty;host=localhost', 'user', 'password');
$connection = new PDO(
    'mysql:host=heradb.corp.care2.com;dbname=shorty',
    'shorty',
    'XyoPUCEyMzvHUaARe8hQPbdK2DyuA3Vz',
    array(
        PDO::MYSQL_ATTR_SSL_CA    =>'/etc/ssl/certs/corp/ca-bundle-chain.pem'
    )
);


// Choose your character set (default)
// $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
// $chars = 'cMKPZNHC6miQESgGoT4V9B1e2sWjInp5zyl7UFkvuaLtRDJfwbhXd3x8rqAY';

// The following are shuffled strings of the default character set.
// You can uncomment one of the lines below to use a pre-generated set,
// or you can generate your own using the PHP str_shuffle function.
// Using shuffled characters will ensure your generated URLs are unique
// to your installation and are harder to guess.

// $chars = 'XPzSI6v5DqLuBtVWQARy2mfwkC14F8HUTOG0aJiYpNrl9Zxgbd3Khsno7jMeEc';
// $chars = 'PAC3mfIazxgF1lVK4wJ2WEHY0dcb87TrsZeBpL9vNUMGktROijnSoq5DX6yQhu';
// $chars = 'zFr7ALOJnGRxtKSs0oQT5NeZjdI1iX8DM2lHaCVyg4mUPp63BkEubc9qWfhwYv';
// $chars = 'u7oIws3pVWZMQjA4XhNtyvglkEer1C2J5YdT6zLiFm0ObPc8S9KaDHqRBnfUGx';
// $chars = 'gZ6hdO59XTJmUP31YMG7FvQyqjlKkf8zwitx0AcupDVs2RWCIBaNreob4nLHES';

// If you want your generated URLs to even harder to guess, you can set
// the salt value below to any non empty value. This is especially useful for
// encoding consecutive numbers.
$salt = 'PLyHpLqk7HL8r3iN';

// The padding length to use when the salt value is configured above.
// The default value is 3.
$padding = 4;
?>
