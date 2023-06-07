<?php

require_once "../common.php";

$content = array();

$link = connectToDB();

$template = file_get_contents("layouts/admin.html");

echo strtr($template, $content);

function connectToDB() {

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SET NAMES utf8;";
    $rs = mysqli_query($link, $sql) or die(mysqli_error($link));

    return $link;
}

?>