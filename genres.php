<?php

require_once "common.php";

$content = array();

$link = connectToDB();

getFullGenres($link, $content);

$template = file_get_contents("layouts/header.html").file_get_contents("layouts/genres.html").file_get_contents("layouts/footer.html");

echo strtr($template, $content);

/**
 * Create new connection with default values described in common.php
 * 
 * @return $link valid DB link
 */
function connectToDB() {

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SET NAMES utf8;";
    $rs = mysqli_query($link, $sql) or die(mysqli_error($link));

    return $link;
}

function getFullGenres($dblink, &$data) {

    $sql = "SELECT 
                title AS __GENRE__,
                url_cover AS __COVER__
            FROM genres";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $o = "";
    foreach ($rs as $item) {
        $o .=   "<div class=\"genres_full_list_genre\">
                    <div class=\"genres_full_list_genre_cover\">
                        <img src=$item[__COVER__]>
                    </div>
                    <div class=\"genres_full_list_genre_title\">
                        $item[__GENRE__]
                    </div>
                </div>";
    };
    $data["__FULL_GENRES_LIST__"] = $o;

    return 0;
}

?>