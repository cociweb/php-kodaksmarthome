<?php

require_once 'vendor/autoload.php';

$ksh = new \phpKodakSmarthome\phpKodakSmarthome('LOGINNAME', 'LOGINPASSWORD');

$ksh->getEvents();