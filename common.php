<?php

/* * Показывать ошибки для отладки */
error_reporting(E_ALL);
// echo 'PHP version: ' . phpversion();

/**
 * MYSQLI_REPORT_ALL - для ВСЕХ ошибок,  
 * MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT - для всех ошибок, кроме индексов, 
 * MYSQLI_REPORT_OFF - для отключения (default)  
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

define("DB_HOST", "localhost");
/** Valid user to connect  */
define("DB_USER", "Vladislav");
define("DB_PASSWORD", "r9CSvnXthAyYRqD");
//define("DB_PASSWORD",""); // if password is empty 

define("DB_NAME", "AUDIOTECA");
define("SITE", "$_SERVER[REQUEST_SCHEME]://$_SERVER[SERVER_NAME]");

define("APP", 7); /* Articles Per Page - pagination */

//-------------------------------------------------------------------------
/**
 * display user error page instead of server error page
 * 
 * @param type $code HTTP response code (400, 403, 404)
 * @return void 
 */
function raiseError($code) {
    switch ($code) {
        case 404 :
            header("HTTP/1.1 404 Not found");
            // А должно быть так:
            // include "/error.php";
            header("Location: /error.php?$code");
            exit;
    };
    return 0;
}

//-------------------------------------------------------------------------
/** Extras */
function paginator($pageCount, $articlesPerPage, $queryString, $activePage) {

    $lastPage = ceil($pageCount / $articlesPerPage);

    $pagination = range(1, $lastPage);

    foreach ($pagination as &$page) {
        $page != $activePage ? $page = "<a href=\"?q=$queryString&p=$page\">$page</a>" : $page = "<a class=\"active\" href=\"?q=$queryString&p=$page\">$page</a>";
    }
    
    return "<a href=\"?q=$queryString&p=1\" >&laquo;</a>" . implode(" ", $pagination) . "<a href=\"?q=$queryString&p=$lastPage\">&raquo;</a>";
}

/****************************************************************************/
function translit($string) {  

//	$string = preg_replace("/[^\w\d ]/ui","-",$string); // это не работает на хостинге
	$string = preg_replace("/[^а-яё0-9a-z]/ui"," ",$string); // а это работает 
	$string = preg_replace("/\s+/u","-",trim($string));
// используются правила транслита, близкие к Яндексу (доп. http://translit-online.ru/yandex.html)
	$converter =  [  
		'а' => 'a',   'б' => 'b',   'в' => 'v',  
		'г' => 'g',   'д' => 'd',   'е' => 'e',  
		'ё' => 'e',   'ж' => 'zh',  'з' => 'z',  
		'и' => 'i',   'й' => 'y',   'к' => 'k',  
		'л' => 'l',   'м' => 'm',   'н' => 'n',  
		'о' => 'o',   'п' => 'p',   'р' => 'r',  
		'с' => 's',   'т' => 't',   'у' => 'u',  
		'ф' => 'f',   'х' => 'h',   'ц' => 'c',  
		'ч' => 'ch',  'ш' => 'sh',  'щ' => 'shch',  
		'ь' => "",  'ы' => 'y',   'ъ' => "",  
		'э' => 'eh',   'ю' => 'yu',  'я' => 'ya',  
		
		'А' => 'A',   'Б' => 'B',   'В' => 'V',  
		'Г' => 'G',   'Д' => 'D',   'Е' => 'E',  
		'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',  
		'И' => 'I',   'Й' => 'Y',   'К' => 'K',  
		'Л' => 'L',   'М' => 'M',   'Н' => 'N',  
		'О' => 'O',   'П' => 'P',   'Р' => 'R',  
		'С' => 'S',   'Т' => 'T',   'У' => 'U',  
		'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',  
		'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Shch',  
		'Ь' => "",  'Ы' => 'Y',   'Ъ' => "",  
		'Э' => 'Eh',   'Ю' => 'Yu',  'Я' => 'Ya', 
		' ' => '-', 
	];
	    
//	return iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE",strtolower(strtr($string, $converter)));
  return strtolower(strtr($string, $converter));
} 




