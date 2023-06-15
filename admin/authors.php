<?php

    if(isset($_COOKIE["admin"])) {

        require_once "../common.php";

        $content = array();
        
        $link = connectToDB();

        if (isset($_POST["deletedAuthorId"])) {
            deleteAuthorCover($link, $_POST["deletedAuthorId"]);
            deleteAuthor($link, $_POST["deletedAuthorId"]);
            $content["SUCCESS_ALERT"] = "&nbsp Исполнитель" . getAuthorNameByID($link, $_POST["deletedAuthorId"]) . " был успешно удален! &nbsp";
        }
        elseif(isset($_POST["add_author"])) {
            getGenresList($link, $content);
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/authors/add_author.html").file_get_contents("layouts/footer.html");
            echo strtr($template, $content);
        }
        elseif (isset($_POST["fix_author"])) {
            $old_author_id = $_POST["fix_author"];
        
            $sql = "SELECT name_author as __NAME__,
                        title as __GENRE__,
                        authors.url_cover as __IMAGE__
                    FROM authors
                    INNER JOIN genres on authors.basic_genre = genres.id_genre
                    WHERE id_author = \"$old_author_id\"";
        
            $rs = mysqli_query($link, $sql) or die(mysqli_error($link));
        
            $name_author = "";
            $author_genre = "";
            $url_cover = "../../..";
        
            foreach($rs as $item) {
                $name_author = $item["__NAME__"];
                $author_genre = $item["__GENRE__"];
                $url_cover .= $item["__IMAGE__"];
            }
            $content["OLD_NAME"] = $name_author;
            $content["IMAGE_SRC"] = $url_cover;
            $content["SHORT_IMAGE_SRC"] = basename($url_cover);
            $content["OLD_AUTHOR_ID"] = $old_author_id;
        
            getGenresListWithLabel($link, $content, $author_genre);
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/authors/fix_author.html").file_get_contents("layouts/footer.html");

            echo strtr($template, $content);
        }
        elseif(isset($_POST["name_author"])) {
            $image_file_name = saveFileToDirectory("author_cover", "../images/authors/", $link, 0);

            addNewAuthorToDB($link, $image_file_name);

            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = "&nbsp Исполнитель " . $_POST["name_author"] . " успешно добавлен! &nbsp";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/authors/authors.html").file_get_contents("layouts/footer.html");
            echo strtr($template, $content);
        }
        elseif (isset($_POST["fix_name_author"])) {
            $new_name_author = $_POST["fix_name_author"];
            $new_author_genre = $_POST["fix_author_genre"];
        
            $new_id_genre = getIDGenreByName($link, $new_author_genre);
        
            $old_cover_src = $_POST["old_cover_src"];
            $old_author_id = $_POST["old_author_id"];
        
            $fix_cover_src = "/images/authors/" . $old_cover_src;
        
            $old_files_count = basename($old_cover_src, '.' . pathinfo($old_cover_src, PATHINFO_EXTENSION));
            
            if ($_FILES["fix_author_cover"]["size"] != 0) {
                $target_dir = "../images/authors/";
                unlink($target_dir . $old_cover_src);
                $fix_cover_src = "/images/authors/" . saveFileToDirectory("fix_author_cover", $target_dir, $link, $old_files_count);
            }
        
            $sql = "UPDATE authors SET 
                    name_author = \"$new_name_author\",
                    basic_genre = \"$new_id_genre\",
                    url_cover = \"$fix_cover_src\"
                    WHERE id_author = $old_author_id";
        
            $rs = mysqli_query($link, $sql) or die(mysqli_error($link));
        
            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = " &nbsp Исполнитель " . $_POST["fix_name_author"] . " успешно обновлен! &nbsp ";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/authors/authors.html").file_get_contents("layouts/footer.html");
            echo strtr($template, $content);
        }
        else {
            getMainContent($link, $content);
            $content["SUCCESS_ALERT"] = "";
            $template = file_get_contents("layouts/layout.html").file_get_contents("layouts/authors/authors.html").file_get_contents("layouts/footer.html");
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
                    id_author as __ID__,
                    name_author as __NAME__,
                    authors.url_cover as __COVER__,
                    title as __GENRE__
                FROM authors
                INNER JOIN genres on authors.basic_genre = genres.id_genre";

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
                        $item[__NAME__] &nbsp <div style=\"font-size: 24px;\">$item[__GENRE__]</div> 
                        </div>
                        <div class=\"admin_track_buttons\">
                            <form action=\"../admin/authors.php\" method=\"post\" class=\"fix_form\">
                                <button id=\"author_fix_button_$count\" name=\"fix_author\" style=\"width: 100%;\" value=\"$item[__ID__]\"> 
                                    <img src=\"../../images/icons/fix.png\">
                                </button>
                            </form>
                            <div class=\"delete_form\">
                                <button  style=\"width: 100%;\" id=\"author_delete_button_$item[__ID__]\" onclick=\"confirmDeleteAuthor($item[__ID__])\" value=\"$item[__NAME__]\">
                                    <img src=\"../../images/icons/delete.png\">
                                </button>
                            </div>
                        </div>
                    </div>";
            $count++;
        };

        $data["ИСПОЛНИТЕЛИ"] = $o;

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

    function getGenresListWithLabel($dblink, &$data, $author_genre) {
        $sql = "SELECT title as __GENRE__ FROM genres";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $o = "";

        $count = 1;

        foreach ($rs as $item) {
            if ($item["__GENRE__"] == $author_genre) {
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

    function getNextCountAuthor($dblink) {
        $sql = "SELECT * FROM authors;";

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
            $new_filename = getNextCountAuthor($link) . '.' . $fileType;
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

    function getIDGenreByName($dblink, $genre_title) {
        $sql = "SELECT id_genre as ID FROM genres WHERE title = \"$genre_title\";";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $id_genre = 0;

        foreach ($rs as $item) {
            $id_genre = $item['ID'];
        };

        return $id_genre;
    }

    function addNewAuthorToDB($dblink, $image_file_name) {

        $id_genre = getIDGenreByName($dblink, $_POST["author_genre"]);

        $image_file_url = "/images/authors/" . $image_file_name;

        $name_author = $_POST["name_author"];

        $sql = "INSERT INTO authors (name_author, basic_genre, url_cover) VALUES (\"$name_author\", $id_genre, \"$image_file_url\")";
        
        mysqli_query($dblink, $sql) or die(mysqli_error($dblink));
    }

    function deleteAuthor($dblink, $deletedAuthorID) {

        $sql = "DELETE FROM authors WHERE id_author = \"$deletedAuthorID\";";

        mysqli_query($dblink, $sql) or die(mysqli_error($dblink));
    }

    function deleteAuthorCover($dblink, $deletedAuthorID) {
        $sql = "SELECT url_cover as __COVER__ FROM authors WHERE id_author = \"$deletedAuthorID\"";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $url_cover = "";

        foreach ($rs as $item) {
            $url_cover = "../" . $item["__COVER__"];
        }

        unlink($url_cover);
    }

    function getAuthorNameByID($dblink, $authorId) {
        $sql = "SELECT name_author as __NAME__ FROM authors WHERE id_author = \"$authorId\"";

        $rs = mysqli_query($dblink, $sql) or die(mysqli_error($dblink));

        $author_name = "";

        foreach ($rs as $item) {
            $author_name = $item["__NAME__"];
        }

        return $author_name;
    }
?>