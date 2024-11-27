<?php

require_once("utils/db.php");
require_once("utils/request.php");
require_once("utils/builder.php");
require_once("utils/session.php");

Request::allowed_methods(["GET"]);
Session::start();

$db = DB::from_env();
$user = Session::get_user();
$template = Builder::from_template(basename(__FILE__));
$common = Builder::load_common();


if (empty($_GET["id"])) {
    header("Location: categories.php");
    exit();
}

$movie_id = $_GET["id"];
$movie = $db->get_movie($movie_id);

if (empty($movie)) {
    header("Location: categories.php");
    exit();
}

$template->replace_single([
    "nome_film" => $movie["name"],
    "nome_cat" => $movie["category"],
    "description" => $movie["description"],
]);


$reviews = $db->get_reviews($movie_id);
$template->replace_block_name_arr("recensioni", $reviews, function (Builder $sec, array $i) {
    $sec->replace_single([
        "rev_username" => $i["username"],
        "rev_title" => $i["title"],
        "rev_content" => $i["content"],
        "rev_rating" => $i["rating"],
    ]);
});

if (empty($user)) {
    $template->delete_blocks(["crea_recensione"]);
} else {
    $template->replace_var("crea_recensione", $template->get_block("crea_recensione")->replace_single([
        "film_id" => $movie_id,
    ]), VarType::Block);
}

$template->build($user, $common);
$template->delete_secs([]);

$template->show();
