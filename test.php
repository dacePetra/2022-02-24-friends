<?php
$x=[];
//if(empty($x['name'])){echo "ooo".PHP_EOL;}
$y="da1ce";
//if(!preg_match("/^[a-zA-Z]*$/", $y)){echo "ooo".PHP_EOL;}
$e="aaa@gmailcom";
if(!filter_var($e, FILTER_VALIDATE_EMAIL)){echo "ooo".PHP_EOL;}


