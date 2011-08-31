<?php

require_once('Peachy.php');

$conf = new pfYamlConfiguration('example.php');
pfContext::createInstance($conf)->dispatch();