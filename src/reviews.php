<?php

require_once("utils/db.php");
require_once("utils/request.php");
require_once("utils/check_template.php");
require_once("generators/reviews.php");

Request::allowed_methods(["GET"]);

$db = DbConnection::from_env();
$template = new ReviewsPage();

$content = $template->get_content();

assert_template_render($content);
echo ($content);