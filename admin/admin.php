<?php

// проверяем наличие куки, если она установлена, то пользователь уже вошел в систему
if(isset($_COOKIE["admin"])) {
    require "compositions.php";
} else {
  // проверяем наличие данных в полях логина и пароля после отправки формы
  if(isset($_POST["login"])) {
    $login = $_POST["login"];
    $password = $_POST["password"];
    // вместо имени пользователя и пароля ниже нужно использовать свои значения
    if($login == "admin" && $password == "password") {
      // устанавливаем куки на 24 часа и перенаправляем на страницу администрирования
      setcookie("admin", $login, time() + 86400);
      require "compositions.php";
    }else {
        $template = file_get_contents("layouts/admin_in_header.html").file_get_contents("layouts/admin_in_failed.html").file_get_contents("layouts/admin_in_footer.html");
        echo $template;
    }
  } else {
    $template = file_get_contents("layouts/admin_in_header.html").file_get_contents("layouts/admin_in_footer.html");
    echo $template;
  }
}

?>