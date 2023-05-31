<?php
/**
 * Shorty: A simple URL shortener.
 *
 * @copyright Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license   MIT, http://www.opensource.org/licenses/mit-license.php
 */
class Shorty {
    /**
     * Default characters to use for shortening.
     *
     * @var string
     */
    private $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Salt for id encoding.
     *
     * @var string
     */
    private $salt = '';

    /**
     * Length of number padding.
     */
    private $padding = 1;

    /**
     * Hostname
     */
    private $hostname = '';

    /**
     * PDO database connection.
     *
     * @var object
     */
    private $connection = null;

    /**
     * Constructor
     *
     * @param string $hostname Hostname
     * @param object $connection Database connection
     */
    public function __construct($hostname, $connection) {
        $this->hostname = $hostname;
        $this->connection = $connection;
    }

    /**
     * Gets the character set for encoding.
     *
     * @return string Set of characters
     */
    public function get_chars() {
        return $this->chars;
    }

    /**
     * Sets the character set for encoding.
     *
     * @param string $chars Set of characters
     */
    public function set_chars($chars) {
        if (!is_string($chars) || empty($chars)) {
            throw new Exception('Invalid input.');
        }
        $this->chars = $chars;
    }

    /**
     * Gets the salt string for encoding.
     *
     * @return string Salt
     */
    public function get_salt() {
        return $this->salt;
    }

    /**
     * Sets the salt string for encoding.
     *
     * @param string $salt Salt string
     */
    public function set_salt($salt) {
        $this->salt = $salt;
    }

    /**
     * Gets the padding length.
     *
     * @return int Padding length
     */
    public function get_padding() {
        return $this->padding;
    }

    /**
     * Sets the padding length.
     *
     * @param int $padding Padding length
     */
    public function set_padding($padding) {
        $this->padding = $padding;
    }

    /**
     * Converts an id to an encoded string.
     *
     * @param int $n Number to encode
     * @return string Encoded string
     */
    public function encode($n) {
        $k = 0;

        if ($this->padding > 0 && !empty($this->salt)) {
            $k = self::get_seed($n, $this->salt, $this->padding);
            $n = (int)($k.$n);
        }

        return self::num_to_alpha($n, $this->chars);
    }

    /**
     * Converts an encoded string into a number.
     *
     * @param string $s String to decode
     * @return int Decoded number
     */
    public function decode($s) {
        $n = self::alpha_to_num($s, $this->chars);

        return (!empty($this->salt)) ? substr($n, $this->padding) : $n;
    }

    /**
     * Gets a number for padding based on a salt.
     *
     * @param int $n Number to pad
     * @param string $salt Salt string
     * @param int $padding Padding length
     * @return int Number for padding
     */
    public static function get_seed($n, $salt, $padding) {
        $hash = md5($n.$salt);
        $dec = hexdec(substr($hash, 0, $padding));
        $num = $dec % pow(10, $padding);
        if ($num == 0) $num = 1;
        $num = str_pad($num, $padding, '0');

        return $num;
    }

    /**
     * Converts a number to an alpha-numeric string.
     *
     * @param int $num Number to convert
     * @param string $s String of characters for conversion
     * @return string Alpha-numeric string
     */
    public static function num_to_alpha($n, $s) {
        $b = strlen($s); // 60
        $m = $n % $b; // 60 % 17 = 9
        error_log ( json_encode (array ("num_to_alpha" => array ('n' => $n, 'm' => $m, 'b' => $b) ) ) );
        if ($n - $m == 0) return substr($s, $n, 1); // 17-9 = 8 

        $a = '';

        $i=0;
        while ($m > 0 || $n > 0) { 
            $a = substr($s, $m, 1).$a;
            $n = ($n - $m) / $b; 
            $m = $n % $b; 
            error_log ( json_encode ( array ("num_to_alpha while [$i]" => array ('n' => $n, 'm' => $m, 'b' => $b) )) );
            $i++;
        }

        return $a;
    }

    /**
     * Converts an alpha numeric string to a number.
     *
     * @param string $a Alpha-numeric string to convert
     * @param string $s String of characters for conversion
     * @return int Converted number
     */
    public static function alpha_to_num($a, $s) {
        $b = strlen($s);
        $l = strlen($a);

        for ($n = 0, $i = 0; $i < $l; $i++) {
            $n += strpos($s, substr($a, $i, 1)) * pow($b, $l - $i - 1);
        }

        return $n;
    }

    /**
     * Looks up a URL in the database by id.
     *
     * @param string $id URL id
     * @return array URL record
     */
    public function fetch($t) {
        if ( !empty($this->salt) ) {
            $salt = ";" . $this->salt;
        } else {
            $salt = "";
        }
        $statement = $this->connection->prepare(
            'SELECT * FROM urls WHERE short = ?'
        );
        $s = md5($t . $salt);
        error_log ( json_encode ( array ( "fetch" => $t, "salt" => $salt, "short" => $s ) )  );
        $statement->execute( array( $s ) );
        if ( $statement->rowCount() == 0 && strlen($t) < 8 ) {
            $id = self::decode($t);
            error_log ( json_encode ( array ( "fetch-else" => $id, "salt" => $salt, "short" => $s ) )  );
            $statement = $this->connection->prepare(
                'SELECT * FROM urls WHERE id = ?'
            );
            $statement->execute(array( $id ) );
        }
        $r = $statement->fetch(PDO::FETCH_ASSOC);
        error_log ( json_encode( array ( "results" => $r ) ) ) ;
        return $r;
    }

    public function list_all() {
    
        $statement = $this->connection->prepare(
            'SELECT * FROM urls order by id'
        );
        $statement->execute();
        $r = $statement->fetchAll(PDO::FETCH_ASSOC);
        # error_log ( json_encode( array ( "results" => $r ) ) ) ;
        return $r;
    }


    /**
     * Attempts to locate a URL in the database.
     *
     * @param string $url URL
     * @return array URL record
     */
    public function find($url) {
        $md5 = md5($url);
        $statement = $this->connection->prepare(
            'SELECT * FROM urls WHERE md5url = ?'
        );
        $statement->execute(array($md5));

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Stores a URL in the database.
     *
     * @param string $url URL to store
     * @return int Insert id
     */
    public function store($url, $vanity = NULL) {
        $datetime = date('Y-m-d H:i:s');
        $md5url = md5($url);

        $statement = $this->connection->prepare(
            'INSERT INTO urls (url, created, md5url, vanity) VALUES (?,?,?,?)'
        );
        $statement->execute(array($url, $datetime, $md5url, $vanity));
        $id = $this->connection->lastInsertId();
        
        return $this->store_md5vanity($id, $url, $vanity);
        
    }
    public function edit($id, $url, $vanity = NULL) {
        $datetime = date('Y-m-d H:i:s');
        $md5url = md5($url);

        $statement = $this->connection->prepare(
            'UPDATE urls set `url` = ?, `vanity` = ? where `id` = ? '
        );
        $statement->execute(array($url, $datetime, $md5url));
        return $this->store_md5vanity($id, $url, $vanity);
    }

    public function store_md5vanity($id, $url, $vanity = NULL) {
        # This is just a salted MD5 for the encoded ID or the Vanity URL.
        # Fetch is looking up on this value ... because 
        # incoming shorted vanity URLs won't decode to an ID.
        
        if ( !empty($this->salt) ) {
            $salt = ";" . $this->salt;
        } else {
            $salt = "";
        }
        if ( is_null($vanity) ) {
            $v = $this->encode($id);
            $short = md5($v . $salt);
        } else  {
            $v = preg_replace ( '/[\s\\\^\.\$\|\(\)\[\]*\+\?\{\}\,]+/', '', $vanity);
            $short = md5($v . $salt);
        }

        $statement = $this->connection->prepare(
            'UPDATE urls set `short` = ? where `id` = ? '
        );
        $statement->execute(array($short, $v, $id));
        return $id;
    }

    /**
     * Updates statistics for a URL.
     *
     * @param int $id URL id
     */
    public function update($id) {
        $datetime = date('Y-m-d H:i:s');

        $statement = $this->connection->prepare(
            'UPDATE urls SET hits = hits + 1, accessed = ? WHERE id = ?'
        );
        $statement->execute(array($datetime, $id));
    }
    

    /**
     * Sends a redirect to a URL.
     *
     * @param string $url URL
     */
    public function redirect($url) {
        header("Location: $url", true, 301);
        exit();
    }

    /**
     * Sends a 404 response.
     */
    public function not_found() {
        
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        exit('<h1>404 Not Found</h1>');
    }

    /**
     * Sends an error message.
     *
     * @param string $message Error message
     */
    public function error($message, $code = 400) {
        if ( $code ==    500 ) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        } else if ( $code == 400 ) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        }
        exit("<h1>$message</h1>");
    }

    /**
     * Starts the program.
     */
    public function run() {
        if (isset($_GET['q'])) {
            $q = str_replace('/', '', $_GET['q']);
        }
        $url = '';
        if (isset($_GET['url'])) {
          $url = urldecode($_GET['url']);
        }
        if (isset($_GET['v'])) {
          $vanity = urldecode($_GET['v']);
        } else {
            $vanity = NULL;
        }

        $format = '';
        if (isset($_GET['format'])) {
          $format = strtolower($_GET['format']);
        }

        // If adding a new URL
        if (!empty($url)) {

            if (preg_match('/^http[s]?\:\/\/[\w]+/', $url)) {
                $result = $this->find($url);
                // Not found, so save it
                if (empty($result)) {

                    $id = $this->store($url, $vanity);
                    $result = $this->find($url);
                    $url = array ();                
                    $url[0] = $this->hostname.'/'.$this->encode($id);
                    if ( ! is_null ($result['vanity']) ) {
                        $url[1] = $this->hostname.'/'.$result['vanity'];
                    }
                }
                else {
                    $url = array ();
                    $url[0] = $this->hostname.'/'.$this->encode($result['id']);
                    if ( ! is_null ($result['vanity']) ) {
                        $url[1] = $this->hostname.'/'.$result['vanity'];
                    } else {
                        $url[1] = $url[0];
                    }
                }

                // Display the shortened url
                switch ($format) {
                    case 'text':
                        exit(implode ("\n", $url) );

                    case 'json':
                        header('Content-Type: application/json');
                        exit(json_encode($url));

                    case 'xml':
                        header('Content-Type: application/xml');
                        exit(implode("\n", array(
                            '<?xml version="1.0"?'.'>',
                            '<response>',
                            '  <url>'.htmlentities($url[0]).'</url>',
                            '  <url>'.htmlentities($url[1]).'</url>',
                            '</response>'
                        )));

                    default:
                        $msg="";
                        foreach ( $url as $u ) {
                            $msg .= '<a href="'.$u.'">'.$u.'</a>' . "<br/>\n";
                        }
                        exit($msg);
                }
            }
            else {
                $this->error('Bad input.');
            }
        }
        // Lookup by id
        else {
            if (empty($q)) {
              $this->not_found();
              return;
            }

            if (preg_match('/^([a-zA-Z0-9]+)$/', $q, $matches)) {
                $result = $this->fetch($matches[1]);
                $id = $result['id'];
                if (!empty($result)) {
                    $this->update($id);

                    $this->redirect($result['url']);
                }
                else {
                    $this->not_found();
                }
            }
        }
    }
}
