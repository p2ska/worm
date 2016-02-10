<?php

if (!isset($_GET["worm"]))
    return false;

require_once "c:/xampp/security/worm/_connector.php";
require_once "classes/_translations.php";
require_once "classes/_worm_ext.php";

$worm = new WORM_EXT($_GET["worm"]);

echo $worm->content;

// we are done here.

function dump($this, $die = false) {
    echo "<pre>";
    print_r($this);
    echo "</pre>";

    if ($die)
        die();
}

?>
