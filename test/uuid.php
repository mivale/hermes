<?php
include('lib.uuid.php');
$uuid = UUID::mint();
var_dump($uuid->hex);
