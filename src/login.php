<?php

require_once("utils/db.php");
require_once("utils/request.php");
require_once("utils/builder.php");
require_once("utils/session.php");

Request::allowed_methods(["GET", "POST"]);
Session::start();

if (Session::is_logged()) {
    Request::redirect("index.php");
}

$login_error = null;
if (Request::is_post()) {
    $username = $_POST["username"] ?? null;
    $password = $_POST["password"] ?? null;

    if (empty($username) || empty($password)) {
        $login_error = "Inserisci username e password";
    } else if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $login_error = "Username o password errate";
    } else if (strlen($password) < 4) {
        $login_error = "Username o password errate";
    } else {

        try {

            $db = DB::from_env();
            // fetch user
            $user = $db->get_user_with_password($_POST["username"], $_POST["password"]);
            $db->close();

            if (!empty($user)) {
                // Redirect to home
                Session::set_user($user);
                Request::redirect("index.php");
            } else {
                // show error
                $login_error = "Username o password errate";
            }
        } catch (mysqli_sql_exception $e) {
            $login_error = "Errore interno del <span lang=\"en\">server</span>";
        }
    }

    if (!empty($login_error)) {
        $_SESSION["login_error"] = $login_error;
    }
    Request::redirect("login.php");
}

if (Request::is_get()) {
    $login_error = $_SESSION["login_error"] ?? null;
    unset($_SESSION["login_error"]);

    $template = Builder::from_template(basename(__FILE__));
    $common = Builder::load_common();

    $template->build(null, $common);
    $template->delete_secs([]);

    if (!empty($login_error)) {
        $template->replace_var("login_error", $template->get_block("login_error")->replace_var("login_error", $login_error), VarType::Block);
    } else {
        $template->delete_var("login_error", VarType::Block);
    }

    $template->show();
}
