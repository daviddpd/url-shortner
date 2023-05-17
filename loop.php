<?php
require './shorty.php';
require './config.php';

$shorty = new Shorty($hostname, $connection);

#$shorty->set_chars($chars);
$shorty->set_salt($salt);
$shorty->set_padding($padding);

$id=100000000;
$id=1;
while (true) {
	$c = $shorty->encode($id);
	echo " $id $c \n";
	$id++;
	usleep(200);
}

?>
