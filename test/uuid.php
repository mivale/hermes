<?php
include('../library/lib.uuid.php');
$uuid = UUID::mint();
var_dump($uuid->string);
