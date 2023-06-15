<?php

    if(isset($_COOKIE["admin"])) {

        require_once "../common.php";

        $content = array();

        $link = connectToDB();

        if (isset($_POST["deletedTrackId"])) {
            deleteTrackFiles($link, $_POST["deletedTrackId"]);
            deleteTrack($link, $_POST["deletedTrackId"]);
            $content["SUCCESS_ALERT"] = " Трек был успешно удален! ";
        }
        elseif (isset($_POST["add_composition"])) {
            getGenresList($link, $content);
            getAuthorsList($link, $content);
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/add_composition.html").file_get_contents("layouts/footer.html");
        }
        elseif (isset($_POST["fix_composition"])) {
            $old_track_id = $_POST["fix_composition"];

            $sql = "SELECT compositions.title as __TITLE__,
                        name_author as __NAME__,
                        genres.title as __GENRE__,
                        compositions.url_cover as __IMAGE__,
                        compositions.url_file as __FILE__
                    FROM compositions 
                    INNER JOIN authors on compositions.id_author = authors.id_author 
                    INNER JOIN genres on compositions.id_genre = genres.id_genre
                    WHERE id_composition = \"$old_track_id\"";

            $rs = mysqli_query($link, $sql) or die(mysqli_error($link));

            $track_title = "";
            $name_author = "";
            $track_genre = "";
            $url_cover = "../../..";
            $url_file = "";

            foreach($rs as $item) {
                $track_title = $item["__TITLE__"];
                $name_author = $item["__NAME__"];
                $track_genre = $item["__GENRE__"];
                $url_cover .= $item["__IMAGE__"];
                $url_file = $item["__FILE__"];
            }
            $content["OLD_TITLE"] = $track_title;
            $content["IMAGE_SRC"] = $url_cover;
            $content["SHORT_IMAGE_SRC"] = basename($url_cover);
            $content["FILE_SRC"] = basename($url_file);
            $content["OLD_TRACK_ID"] = $old_track_id;

            getGenresListWithLabel($link, $content, $track_genre);
            getAuthorsListWithLabel($link, $content, $name_author);
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/fix_composition.html").file_get_contents("layouts/footer.html");
        }
        elseif (isset($_POST["track_title"])) {

            $image_file_name = saveFileToDirectory("track_cover", "../images/compositions/", $link, 0);
            $track_file_name = saveFileToDirectory("track", "../music/", $link, 0);

            addNewTrackToDB($link, $image_file_name, $track_file_name);

            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = " &nbsp Трек " . $_POST["track_title"] . " успешно добавлен! &nbsp ";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/compositions.html").file_get_contents("layouts/footer.html");
        }
        elseif (isset($_POST["fix_track_title"])) {
            $new_track_title = $_POST["fix_track_title"];
            $new_track_author = $_POST["fix_track_author"];
            $new_track_genre = $_POST["fix_track_genre"];

            $new_id_author = getIDAuthorByName($link, $new_track_author);
            $new_id_genre = getIDGenreByName($link, $new_track_genre);

            $old_cover_src = $_POST["old_cover_src"];
            $old_file_src = $_POST["old_file_src"];
            $old_track_id = $_POST["old_track_id"];

            $fix_cover_src = "/images/compositions/" . $old_cover_src;
            $fix_file_src = "/music/" . $old_file_src;

            $old_files_count = basename($old_cover_src, '.' . pathinfo($old_cover_src, PATHINFO_EXTENSION));
            
            if ($_FILES["fix_track_cover"]["size"] != 0) {
                $target_dir = "../images/compositions/";
                unlink($target_dir . $old_cover_src);
                $fix_cover_src = "/images/compositions/" . saveFileToDirectory("fix_track_cover", $target_dir, $link, $old_files_count);
            }
            if ($_FILES["fix_track_file"]["size"] != 0) {
                $target_dir = "../music/";
                unlink($target_dir . $old_file_src);
                $fix_file_src = "/music/" . saveFileToDirectory("fix_track_file", $target_dir, $link, $old_files_count);
            }

            $sql = "UPDATE compositions SET 
                    title = \"$new_track_title\",
                    id_author = \"$new_id_author\",
                    id_genre = \"$new_id_genre\",
                    url_cover = \"$fix_cover_src\",
                    url_file = \"$fix_file_src\"
                    WHERE id_composition = $old_track_id";

            $rs = mysqli_query($link, $sql) or die(mysqli_error($link));

            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = " &nbsp Трек " . $_POST["fix_track_title"] . " успешно обновлен! &nbsp ";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/compositions.html").file_get_contents("layouts/footer.html");
            
            print_r($old_files_count);
        }
        else {
            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = "";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/compositions/compositions.html").file_get_contents("layouts/footer.html");
        }

        echo strtr($template, $content);
    } else {
        $template = file_get_contents("layouts/admin_in_header.html").file_get_contents("layouts/admin_in_warning.html").file_get_contents("layouts/admin_in_footer.html");
        echo $template;
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

    function getMainContent($dblink, &$data) {

        $sql = "SELECT
                    id_composition as __ID__,
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
            $o .=   "<div class=\"admin_track_container\" id=\"admin_track_container_$item[__ID__]\">
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
                            <form action=\"../admin/compositions.php\" method=\"post\" class=\"fix_form\">
                                <button id=\"track_fix_button_$count\" name=\"fix_composition\" style=\"width: 100%;\" value=\"$item[__ID__]\"> 
                                    <img src=\"../../images/icons/fix.png\">
                                </button>
                            </form>
                            <div class=\"delete_form\">
                                <button  style=\"width: 100%;\" id=\"track_delete_button_$item[__ID__]\" onclick=\"confirmDeleteTrack($item[__ID__])\" value=\"$item[__TITLE__]\">
                                    <img src=\"../../images/icons/delete.png\">
                                </button>
                            </div>
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

    function getAuthorsListWithLabel($dblink, &$data, $name_author) {
        $sql = "SELECT name_author as __NAME__ FROM authors";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $o = "";

        $count = 1;

        foreach ($rs as $item) {
            if ($item["__NAME__"] == $name_author) {
                $o .=   "<option value=\"$item[__NAME__]\" selected>$item[__NAME__]</option>";
            }
            else {
                $o .=   "<option value=\"$item[__NAME__]\">$item[__NAME__]</option>";
            }
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

    function getGenresListWithLabel($dblink, &$data, $track_genre) {
        $sql = "SELECT title as __GENRE__ FROM genres";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $o = "";

        $count = 1;

        foreach ($rs as $item) {
            if ($item["__GENRE__"] == $track_genre) {
                $o .=   "<option value=\"$item[__GENRE__]\" selected>$item[__GENRE__]</option>";
            }
            else {
                $o .=   "<option value=\"$item[__GENRE__]\">$item[__GENRE__]</option>";
            }
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

    function saveFileToDirectory($fileName, $target_dir, $link, $oldcount) {

        $file_name = $_FILES[$fileName]["name"];
        $file_tmp = $_FILES[$fileName]["tmp_name"];
        $file_type = $_FILES[$fileName]["type"];

        $target_file = $target_dir . basename($file_name);

        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($oldcount != 0) {
            $new_filename = $oldcount . '.' . $fileType;
        } else {
            $new_filename = getNextCountTrack($link) . '.' . $fileType;
        }

        $new_target_file = $target_dir . $new_filename;

        // Проверка наличия файла с таким же именем
        if (file_exists($new_target_file)) {
            echo "Ошибка: файл c таким именем уже существует.";
            exit;
        }

        if (move_uploaded_file($file_tmp, $new_target_file)) {

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
            $id_author = $item['ID'];
        };

        return $id_author;
    }

    function getIDGenreByName($dblink, $genre_title) {
        $sql = "SELECT id_genre as ID FROM genres WHERE title = \"$genre_title\";";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $id_genre = 0;

        foreach ($rs as $item) {
            $id_genre = $item['ID'];
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

    function deleteTrack($dblink, $deletedTrackID) {
        $sql = "DELETE FROM compositions WHERE id_composition = \"$deletedTrackID\";";
        
        mysqli_query($dblink, $sql) or die(mysqli_error($dblink));
    }

    function deleteTrackFiles($dblink, $deletedTrackID) {
        $sql = "SELECT url_cover as __COVER__, url_file as __FILE__ FROM compositions WHERE id_composition = \"$deletedTrackID\"";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $url_cover = "";
        $url_file = "";

        foreach ($rs as $item) {
            $url_cover = "../" . $item["__COVER__"];
            $url_file = "../" . $item["__FILE__"];
        }

        unlink($url_cover);
        unlink($url_file);
    }

?>