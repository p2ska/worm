<?php

// [worm]; Andres Päsoke

define("W_ALLOWED",		"/[^\p{L}\p{N}\s\:\.@_-]/u");	// millised sümbolid on lubatud sisendina
define("W_DOTS",		"/\.+/");
define("W_ALL",			"*");
define("W_ANY",			"%");
define("W_Q",			"?");
define("W_LN",			"\n");
define("W_SL",			"/");
define("W_DOT",			".");
define("W_VOID",		"");
define("W_NULL",    	"<null>");
define("W_EX",          "-");
define("W_FSS",			"___");
define("W_BR",      	"<br/>");
define("W_2BR",     	"<br/><br/>");
define("W_PREFIX",		"worm_");
define("W_EXACT",		" = ");
define("W_LIKE",		" like ");
define("W_OR",			" || ");
define("W_SELECT",		" select ");
define("W_FROM",		" from ");
define("W_WHERE",		" where ");
define("W_ORDER",		" order by ");
define("W_LIMIT",		" limit ");
define("W_SEP",         ":");
define("W_TAG_L",       "[");
define("W_TAG_R",       "]");
define("W_AWESOME_L",	"{{");
define("W_AWESOME_R",	"}}");

class WORM {
    // kõik parameetrid (nb! need default'id kirjutatakse üle tabeli kirjeldusfaili ja ka worm.js poolt tulevate väärtustega üle)

    var
    $content, $db, $l, $mode, $target, $uid, $template, $template_file, $url, $class, $data, $translations,
    $database, $host, $username, $password, $charset, $collation,
    $table, $query, $fields, $field_count, $where, $values,
    $debug			= true;         // debug reziim

    // initsialiseeri kõik js poolt määratud muutujad

    function worm($init, $source = false, $lang = false) {
        // kas target on ikka olemas

		if (!isset($init["target"]))
            return false;

		// vormi id

        $this->target = $this->safe($init["target"]);

        // kui pole väliseid tõlkeid juba, siis lae tabeli tõlkefailist;
        // kui translations klassi ka pole, noh siis polegi tõlkeid

        if (!$lang && class_exists("TRANSLATIONS")) {
            $this->translations = new TRANSLATIONS();

            $this->l = $this->translations->import("lang/worm.lang");
        }
        else
            $this->l = $lang;

        // data[] muutuja edastamiseks vormi kirjeldusele

        if (isset($init["data"]) && $init["data"])
            $this->data = $this->safe($init["data"]);

        // kirjuta klassi default'id tabelikirjelduse omadega üle

        if (!$this->init())
            return false;

        // mitu välja defineeritud on?

        $this->field_count = count($this->fields);

        // kirjuta default'id JS omadega üle (puhasta input)

		foreach ($init as $key => $val)
            $this->{ $key } = $this->safe($val);

        // kui tabeli kirjelduses on märgitud uus ühendus

        if ($this->host && $this->database && $this->username && $this->password) {
            $this->db = @new W_DATABASE();
            $this->db->connect($this->host, $this->database, $this->username, $this->password, $this->charset, $this->collation);
        }
        elseif (is_resource($source)) { // kui on antud olemasolev mysql resource link, siis tee uus klass ja topi link kohe külge
            $this->db = @new W_DATABASE();
            $this->db->connection = $source;
        }
        elseif (!$source) { // kui üldse midagi ei antud sisendiks
            $this->db = @new W_DATABASE();

            if (!defined("DB_HOST") || !defined("DB_NAME") || !defined("DB_USER") || !defined("DB_PASS"))
                return false;

            $this->db->connect(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET, DB_COLLATION);
        }

        // seadista vorm

        $this->prepare_worm();

        // moodusta tabel

		$this->output_worm();
    }

    // init

    function init() {
        // et vormikirjeldustes oleks veidi mugavam ja lühem keelestringe välja kutsuda

        $l = &$this->l;

        // kas on vajalik info olemas?

        if (!isset($this->data["worm"]))
            return false;

        // hangi vormi template ja uid

        list($this->template, $this->uid) = explode(":", $this->data["worm"]);

        // kas template ja uid on olemas

        if (!$this->template || !$this->uid)
            return false;
        else
            return true;
    }

    // valmista vorm ette

    function prepare_worm() {
        // tabeli kirjelduse template

        $this->template_file = WORMS. W_SL. $this->template. ".php";

        // kas template eksisteerib?

        if (!file_exists($this->template_file))
            return false;

        // lae template sisu stringi

        ob_start();

        require_once $this->template_file;

        // protsessi template

        $this->content .= $this->replace_tags(ob_get_clean());
    }

    // kuva vorm

    function output_worm() {
    }

    // asenda tag'id

    function replace_tags($content) {
        $pos = 0;
        $start = $end = $found = false;
        $tag_l = strlen(W_TAG_L);
        $tag_r = strlen(W_TAG_R);
        $result = false;

        while (1) {
            // leia algustag

            $start = stripos($content, W_TAG_L, $pos);

            // kui algustag'i ei leitud, siis on string läbikäidud ja lõpeta

            if ($start === false)
                break;

            $end = stripos($content, W_TAG_R, $start);

            // hangi tag'ide vahel olev kirjeldus

            $length = $end - $start;
            $repl = substr($content, $start, $length + $tag_r);
            $markup = substr($content, $start + $tag_l, $length - $tag_l);
            $pos = $start + $length + $tag_r;

            // kui ei leitud korrektset välja- ja tüübikirjeldust, siis otsi järgmist

            if (stripos($markup, W_SEP) === false)
                continue;

            list($field, $key) = explode(W_SEP, $markup);

            //$result = str_replace($repl, "haha", $

            //$result .= "[". $start. "-". $end. "] |". $field. "|". $key. "| ". $repl. " {". $markup. "}<br/>";

        }

        return $result;
    }

    // tee JS tulev sisend turvaliseks

    function safe($input, $length = false) {
        if (!is_array($input)) {
            $output = preg_replace(W_DOTS, W_DOT, preg_replace(W_ALLOWED, W_VOID, trim($input)));

            if ($length)
                $output = substr($output, 0, $length);
        }
        else {
            foreach ($input as $key => $val) {
                $output[$key] = preg_replace(W_DOTS, W_DOT, preg_replace(W_ALLOWED, W_VOID, trim($val)));

                if ($length)
                    $output[$key] = substr($output[$key], 0, $length);
            }
        }

        return $output;
    }
}

?>
