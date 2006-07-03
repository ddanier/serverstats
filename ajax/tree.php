<?php

$xslt = new XSLTProcessor();
$xml = new domDocument();
$xml -> load('tree.xml');

$xsl = new domDocument();
$xsl -> load('tree.xsl');

$xslt -> importStylesheet($xsl);

echo $xslt -> transformToXml($xml);

?>
