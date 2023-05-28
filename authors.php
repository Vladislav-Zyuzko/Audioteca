<?php

require_once "common.php";

$content = array();

$link = connectToDB();

getAuthors($link, $content);

$template = file_get_contents("layouts/header.html").file_get_contents("layouts/authors.html").file_get_contents("layouts/footer.html");

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

function getAuthors($dblink, &$data) {

    $sql = "SELECT 
                name_author AS __NAME__, 
                title AS __GENRE__,
                authors.url_cover AS __COVER__
            FROM authors
            INNER JOIN genres on authors.basic_genre = genres.id_genre";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $o = "";
    foreach ($rs as $item) {
        $o .=   "<div class=\"authors_author\">
                    <div class=\"authors_author_cover\">
                        <img src=$item[__COVER__]>
                    </div>
                    <div class=\"authors_author_name\">
                        $item[__NAME__]
                    </div>
                    <div class=\"authors_author_genre\">
                        $item[__GENRE__]
                    </div>
                </div>";
    };
    $data["__AUTHORS_LIST__"] = $o;

    return 0;
}

?>