<?php

require_once "common.php";

$content = array();

$link = connectToDB();

getMainContent($link, $content);

$template = file_get_contents("layouts/header.html").file_get_contents("layouts/compositions.html").file_get_contents("layouts/player.html").file_get_contents("layouts/footer.html");

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

function getMainContent($dblink, &$data) {

    $sql = "SELECT
                title as __TITLE__,
                name_author as __NAME__,
                compositions.url_cover as __COVER__,
                url_file as __FILE__
            FROM compositions
            INNER JOIN authors on compositions.id_author = authors.id_author";

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

?>