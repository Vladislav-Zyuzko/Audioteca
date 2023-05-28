<?php

main();

/**
 * "main" is a function (procedure) to include scripts that depends on requested URL
 *  
 * @return void
 */
function main(): void {
    switch (true) {
        case ($_SERVER["REQUEST_URI"] === "/") && empty($_SERVER["QUERY_STRING"]) :
            require_once "structural.php";
            break;
        default: echo "HERE IS" . var_dump($_SERVER, $_GET);
    }
}