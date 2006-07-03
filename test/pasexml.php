<?php
header('content-type: text/plain');

require_once('xmlconfig.class.php');

$dom = new domDocument();
$dom->load('../config.sample/xml/config.xml');
$dom->Xinclude();

$dom2 = new xmlconfig();
$dom2->loadXML($dom->saveXML());

print_r ($dom2->parse());

?>

