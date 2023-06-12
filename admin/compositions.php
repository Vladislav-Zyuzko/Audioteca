<?php

require_once "../common.php";

$content = array();

$link = connectToDB();

if (isset($_POST["add_composition"])) {
    getGenresList($link, $content);
    getAuthorsList($link, $content);
    $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/add_composition.html").file_get_contents("layouts/footer.html");
}
elseif (isset($_POST["track_title"])) {

    $image_file_name = saveFileToDirectory("track_cover", "../images/compositions/", $link);
    $track_file_name = saveFileToDirectory("track", "../music/", $link);

    addNewTrackToDB($link, $image_file_name, $track_file_name);

    getMainContent($link, $content);
    $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/compositions.html").file_get_contents("layouts/footer.html");
}
else {
    getMainContent($link, $content);
    $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/compositions.html").file_get_contents("layouts/footer.html");
}

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
        $o .=   "<div class=\"admin_track_container\">
                    <div class=\"admin_track_num\">
                        $count
                    </div>
                    <div class=\"admin_track_cover\">
                        <img src=$item[__COVER__]>
                    </div>
                    <div class=\"admin_track_title\">
                        $item[__NAME__] - $item[__TITLE__]
                    </div>
                    <div class=\"admin_track_buttons\">
                        <button id=\"track_fix_button_$count\">
                            <img src=\"../../images/icons/fix.png\">
                        </button>
                        <button id=\"track_delete_button_$count\">
                            <img src=\"../../images/icons/delete.png\">
                        </button>
                    </div>
                </div>";
        $count++;
    };

    $data["КОМПОЗИЦИИ"] = $o;

    return 0;
}

function getAuthorsList($dblink, &$data) {
    $sql = "SELECT name_author as __NAME__ FROM authors";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $o = "";

    $count = 1;

    foreach ($rs as $item) {
        $o .=   "<option value=\"$item[__NAME__]\">$item[__NAME__]</option>";
        $count++;
    };

    $data["AUTHORS"] = $o;

    return 0;
}

function getGenresList($dblink, &$data) {
    $sql = "SELECT title as __GENRE__ FROM genres";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $o = "";

    $count = 1;

    foreach ($rs as $item) {
        $o .=   "<option value=\"$item[__GENRE__]\">$item[__GENRE__]</option>";
        $count++;
    };

    $data["GENRES"] = $o;

    return 0;
}

function getNextCountTrack($dblink) {
    $sql = "SELECT * FROM compositions;";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $count = 0;

    foreach ($rs as $item) {
        $count += 1;
    };

    return $count + 1;
}

function saveFileToDirectory($fileName, $target_dir, $link) {

    $file_name = $_FILES[$fileName]["name"];
    $file_tmp = $_FILES[$fileName]["tmp_name"];
    $file_type = $_FILES[$fileName]["type"];

    $target_file = $target_dir . basename($file_name);

    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $new_filename = getNextCountTrack($link) . '.' . $fileType;

    $new_target_file = $target_dir . $new_filename;

    // Проверка наличия файла с таким же именем
    if (file_exists($target_file)) {
        echo "Ошибка: файл c таким именем уже существует.";
        exit;
    }

    if (move_uploaded_file($file_tmp, $new_target_file)) {
        echo "Файл был успешно загружен.";
    } else {
        echo "Ошибка при загрузке файла.";
    }

    return $new_filename;
}

function getIDAuthorByName($dblink, $author_name) {
    $sql = "SELECT id_author as ID FROM authors WHERE name_author = \"$author_name\";";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $id_author = 0;

    foreach ($rs as $item) {
        $id_author = $item[ID];
    };

    return $id_author;
}

function getIDGenreByName($dblink, $genre_title) {
    $sql = "SELECT id_genre as ID FROM genres WHERE title = \"$genre_title\";";

    $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

    $id_genre = 0;

    foreach ($rs as $item) {
        $id_genre = $item[ID];
    };

    return $id_genre;
}

function addNewTrackToDB($dblink, $image_file_name, $track_file_name) {
    $id_author = getIDAuthorByName($dblink, $_POST["track_author"]);
    $id_genre = getIDGenreByName($dblink, $_POST["track_genre"]);

    $image_file_url = "/images/compositions/" . $image_file_name;
    $track_file_name = "/music/" . $track_file_name;

    $track_title = $_POST["track_title"];

    $sql = "INSERT INTO compositions (id_author, id_genre, title, url_cover, url_file) VALUES ($id_author, $id_genre, \"$track_title\", \"$image_file_url\",  \"$track_file_name\")";
    
    mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

}


?>