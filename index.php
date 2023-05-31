<?php
require './shorty.php';
require './config.php';

$shorty = new Shorty($hostname, $connection);

#$shorty->set_chars($chars);
$shorty->set_salt($salt);
$shorty->set_padding($padding);


if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
    header('Content-type: application/json');
    if ( $_SERVER['REQUEST_URI'] == "/list_all" ) {
        $r = $shorty->list_all();
        echo json_encode( array ( "results" => $r ), JSON_PRETTY_PRINT);
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . " 400 - Bad Request ", true, 400);
    }
} else {
    $shorty->run();
}
?>
