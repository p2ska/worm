<?php

define("TR_PRE",				"");
define("TR_LANGUAGES",			"ee;en");
define("TR_DEFAULT",			"ee");
define("TR_VAR",				"=>");
define("TR_LANG",				"::");
define("TR_SEPARATOR",			";;");

class TRANSLATIONS {
	var $lang, $languages, $lang_pos, $translations;

	function translations() {
		// olemasolevad keeled

		$this->languages = explode(";", TR_LANGUAGES);

		if (isset($_SESSION["lang"]))
			$this->lang = $_SESSION["lang"];
	}

	function import($file, $force_lang = false) {
		$l = new stdClass();

		// override sessiooni/default keel

		if ($force_lang && $this->valid_language($force_lang))
			$this->lang = $force_lang;

		$content = file_get_contents($file);

		$this->lang_pos = array_search($this->lang, $this->languages);		// mitmes vaste tõlgetes on hetkekeele oma?
		$this->translations = explode(TR_SEPARATOR, $content);				// laialilõhutud tõlked, per keel

		foreach ($this->translations as $line) {
			$line = trim($line);

			// kui stringis pole määratud eraldajat, siis ignoori

			if (strpos($line, TR_VAR) == false)
				continue;

			// löö tõlkerida laiali

			$ex = explode(TR_VAR, $line);
			$vx = explode("\n", $ex[0]);

			// viimane string massiivis on muutujanimi (kui esines reavahetusi (seoses keelefaili kommentaaridega jms))

			$var = trim(array_pop($vx));

			// mõlemad keelestringid

			$val = explode(TR_LANG, $ex[1]);

			// pane valitud keelele vastava positsiooniga keelestring objekti ja trimmi

			$l->{ TR_PRE. $var } = trim($val[$this->lang_pos]);
		}

		return $l;
	}

	function valid_language($lang) {
		if (strlen($lang) != 2)
			return false;

		if (in_array($lang, $this->languages))
			return true;
		else
			return false;
	}
}

?>
