<?php
    if(isset($_COOKIE["admin"])) {
        require_once "../common.php";

        $content = array();
        
        $link = connectToDB();

        if (isset($_POST["deletedGenreId"])) {
            deleteGenreCover($link, $_POST["deletedGenreId"]);
            deleteGenre($link, $_POST["deletedGenreId"]);
            $content["SUCCESS_ALERT"] = "&nbsp Жанр " . getGenreTitleByID($link, $_POST["deletedGenreId"]) . " был успешно удален! &nbsp";
        }
        elseif(isset($_POST["add_genre"])) {
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/genres/add_genre.html").file_get_contents("layouts/footer.html");
            echo strtr($template, $content);
        }
        elseif (isset($_POST["fix_genre"])) {
            $old_genre_id = $_POST["fix_genre"];
        
            $sql = "SELECT title as __TITLE__,
                        url_cover as __IMAGE__
                    FROM genres WHERE id_genre = \"$old_genre_id\"";
        
            $rs = mysqli_query($link, $sql) or die(mysqli_error($link));
        
            $title_genre = "";
            $url_cover = "../../..";
        
            foreach($rs as $item) {
                $title_genre = $item["__TITLE__"];
                $url_cover .= $item["__IMAGE__"];
            }
            $content["OLD_TITLE"] = $title_genre;
            $content["IMAGE_SRC"] = $url_cover;
            $content["SHORT_IMAGE_SRC"] = basename($url_cover);
            $content["OLD_GENRE_ID"] = $old_genre_id;
        
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/genres/fix_genre.html").file_get_contents("layouts/footer.html");

            echo strtr($template, $content);
        }
        elseif(isset($_POST["name_genre"])) {
            $image_file_name = saveFileToDirectory("genre_cover", "../images/genres/", $link, 0);

            addNewGenreToDB($link, $image_file_name);

            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = "&nbsp Жанр " . $_POST["name_genre"] . " успешно добавлен! &nbsp";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/genres/genres.html").file_get_contents("layouts/footer.html");
            echo strtr($template, $content);
        }
        elseif (isset($_POST["fix_name_genre"])) {
            $new_title_genre = $_POST["fix_name_genre"];
        
            $old_cover_src = $_POST["old_cover_src"];
            $old_genre_id = $_POST["old_genre_id"];
        
            $fix_cover_src = "/images/genres/" . $old_cover_src;
        
            $old_files_count = basename($old_cover_src, '.' . pathinfo($old_cover_src, PATHINFO_EXTENSION));
            
            if ($_FILES["fix_genre_cover"]["size"] != 0) {
                $target_dir = "../images/genres/";
                unlink($target_dir . $old_cover_src);
                $fix_cover_src = "/images/genres/" . saveFileToDirectory("fix_genre_cover", $target_dir, $link, $old_files_count);
            }
        
            $sql = "UPDATE genres SET 
                    title = \"$new_title_genre\",
                    url_cover = \"$fix_cover_src\"
                    WHERE id_genre = $old_genre_id";
        
            $rs = mysqli_query($link, $sql) or die(mysqli_error($link));
        
            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = " &nbsp Жанр " . $_POST["fix_name_genre"] . " успешно обновлен! &nbsp ";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/genres/genres.html").file_get_contents("layouts/footer.html");
            echo strtr($template, $content);
        }
        else {
            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = "";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/genres/genres.html").file_get_contents("layouts/footer.html");
            echo strtr($template, $content);
        }
    } else {
        $template = file_get_contents("layouts/admin_in_header.html").file_get_contents("layouts/admin_in_warning.html").file_get_contents("layouts/admin_in_footer.html");
        echo $template;
    }

    function connectToDB() {

        $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SET NAMES utf8;";
        $rs = mysqli_query($link, $sql) or die(mysqli_error($link));
    
        return $link;
    }

    function getMainContent($dblink, &$data) {
        $sql = "SELECT
                    id_genre as __ID__,
                    title as __TITLE__,
                    url_cover as __COVER__
                FROM genres";
    
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
                            $item[__TITLE__]
                        </div>
                        <div class=\"admin_track_buttons\">
                            <form action=\"../admin/genres.php\" method=\"post\" class=\"fix_form\">
                                <button id=\"genre_fix_button_$count\" name=\"fix_genre\" style=\"width: 100%;\" value=\"$item[__ID__]\"> 
                                    <img src=\"../../images/icons/fix.png\">
                                </button>
                            </form>
                            <div class=\"delete_form\">
                                <button  style=\"width: 100%;\" id=\"genre_delete_button_$item[__ID__]\" onclick=\"confirmDeleteGenre($item[__ID__])\" value=\"$item[__TITLE__]\">
                                    <img src=\"../../images/icons/delete.png\">
                                </button>
                            </div>
                        </div>
                    </div>";
            $count++;
        };
    
        $data["ЖАНРЫ"] = $o;
    
        return 0;
    }

    function getNextCountGenre($dblink) {
        $sql = "SELECT * FROM genres;";
    
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
            $new_filename = getNextCountGenre($link) . '.' . $fileType;
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

    function addNewGenreToDB($dblink, $image_file_name) {

    
        $image_file_url = "/images/genres/" . $image_file_name;
    
        $title_genre = $_POST["name_genre"];
    
        $sql = "INSERT INTO genres (title, url_cover) VALUES (\"$title_genre\", \"$image_file_url\")";
        
        mysqli_query($dblink, $sql) or die(mysqli_error($dblink));
    }

    function deleteGenre($dblink, $deletedGenreID) {

        $sql = "DELETE FROM genres WHERE id_genre = \"$deletedGenreID\";";
    
        mysqli_query($dblink, $sql) or die(mysqli_error($dblink));
    }
    
    function deleteGenreCover($dblink, $deletedGenreID) {
        $sql = "SELECT url_cover as __COVER__ FROM genres WHERE id_genre = \"$deletedGenreID\"";
    
        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));
    
        $url_cover = "";
    
        foreach ($rs as $item) {
            $url_cover = "../" . $item["__COVER__"];
        }
    
        unlink($url_cover);
    }

    function getGenreTitleByID($dblink, $genreId) {
        $sql = "SELECT title as __TITLE__ FROM genres WHERE id_genre = \"$genreId\"";
    
        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));
    
        $genre_title = "";
    
        foreach ($rs as $item) {
            $genre_title = $item["__TITLE__"];
        }
    
        return $genre_title;
    }

?>