<?php

require_once("php/db.php");
require_once("php/template.php");

$db = new DbConnection();
$template = new Template();

$content = $template->build_home_page();

echo ($content);
