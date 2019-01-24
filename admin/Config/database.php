<?php
define ('ROOT_DB_PATH', ROOT.DS.'config'.DS.'database.php');
if (is_file(ROOT_DB_PATH)) {
	require_once(ROOT_DB_PATH);	
}