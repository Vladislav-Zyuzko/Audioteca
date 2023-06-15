<?php

require_once "common.php";

$content = array();

$link = connectToDB();

if (isset($_POST["select_genre"])) {
    $genreID = $_POST["select_genre"];

    $content["GENRE_NAME"] = getNameGenreByID($link, $genreID);

    getCompositionsByGenreID($link, $content, $genreID);

    $template = file_get_contents("layouts/header.html") . 
    file_get_contents("layouts/compositions_select_genre.html") . 
    file_get_contents("layouts/player.html") . 
    file_get_contents("layouts/footer.html");

    echo strtr($template, $content);



} else {
    getFullGenres($link, $content);

    $template = file_get_contents("layouts/header.html").file_get_contents("layouts/genres.html").file_get_contents("layouts/footer.html");

    echo strtr($template, $content);
}

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

function getCompositionsByGenreID($dblink, &$data, $genreID) {

    $sql = "SELECT
                compositions.title as __TITLE__,
                name_author as __NAME__,
                compositions.url_cover as __COVER__,
                url_file as __FILE__
            FROM compositions
            INNER JOIN authors on compositions.id_author = authors.id_author
            INNER JOIN genres on compositions.id_genre = genres.id_genre
            WHERE genres.id_genre = \"$genreID\"";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $o = "";
    $count = 1;
    foreach ($rs as $item) {
        $o .=  "<div class=\"composition_container\">
                    <div class=\"track\">
                        <div class=\"track_logo\">
                            <img src=$item[__COVER__] id=\"track_logo_$count\">
                        </div>
                        <div class=\"track_title\">
                            <div class=\"track_name\" id=\"track_name_$count\">
                            $item[__TITLE__]
                            </div>
                            <div class=\"track_author\">
                            $item[__NAME__]
                            </div>
                        </div>
                        <div class=\"track_buttons\">
                            <div class=\"track_button_container\"
                                <button class=\"track_button\" onclick=\"return loadSong('$count')\" value=\"$count\" name=\"track_button_$count\">
                                    <img src=\"../images/icons/play.png\" class=\"track_play_icon\">
                                </button>
                            </div>
                            <div class=\"track_button_container\">
                                <a download=\"\" class=\"download_link\" href=$item[__FILE__]>
                                    <img src=\"../images/icons/load.png\" class=\"track_load_icon\">
                                </a>
                            </div>
                        </div>
                        <audio src=$item[__FILE__] id=\"audio_track_$count\"></audio>
                    </div>
                </div>";
        $count++;
    };

    $data["__MAIN_CONTENT__"] = $o;

    return 0;
}

function getNameGenreByID($dblink, $genreID) {
    $sql = "SELECT title as __NAME__ FROM genres WHERE id_genre = \"$genreID\"";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $name_genre = "";
    
    foreach($rs as $item) {
        $name_genre = $item["__NAME__"];
    }

    return $name_genre;
}

?>