<?php
header('Content-Type: text/html; charset=utf-8');
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'app.php';

App::run("editor","es_MX",APPLICATION_ENV);