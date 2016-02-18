<?php

define("WORMS", "c:/xampp/htdocs/worm/worms");

require_once "_db.php";
require_once "_worm.php";

// põhiklassi extension, võimaldamaks päringust saadud väärtusi edasi töödelda

class WORM_EXT extends WORM {
	// koverteeri kuupäevad eesti regioonile vastavaks

	function convert_date($date) {
        $timestamp = strtotime($date);

        // kui ei ole korrektne sisend, siis ära töötle

        if ($timestamp < 1)
            return $date;

		if (strlen($date) <= 10) // kui on lühike formaat, ilma kellaajata
			return date("d.m.Y", $timestamp);
		else
			return date("d.m.Y H:i:s", $timestamp);
	}

    // võta sekundid maha

	function convert_time($time) {
		return substr($time, 0, 5);
	}

	// muuda emailiaadressid ja veebilingid linkideks

	function autolink($string) {
		$string = preg_replace("/(([\w\.-]+))(@)([\w\.]+)\b/i", "<a href=\"mailto:$0\">$0</a>", $string);
		$string = preg_replace('#(http|https|ftp)://([^\s]*)#', '<a href="\\1://\\2" target="_blank">\\1://\\2</a>', $string);

		return $string;
	}

    // lõhu kõige pikemad sõnad

	function break_long($string) {
		return preg_replace("/([^\s]{80})(?=[^\s])/", "$1<br/>", $string);
	}
}

?>
