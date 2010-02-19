<?php
include('../library/UUID.php');
$uuid = UUID::mint();
var_dump($uuid->hex);
var_dump($uuid->string);
