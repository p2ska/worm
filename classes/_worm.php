<?php

// [worm]; Andres Päsoke

define("P_ALLOWED",		"/[^\p{L}\p{N}\s\.@_-]/u");	// millised sümbolid on lubatud sisendina
define("P_DOTS",		"/\.+/");
define("P_ALL",			"*");
define("P_ANY",			"%");
define("P_Q",			"?");
define("P_LN",			"\n");
define("P_SL",			"/");
define("P_DOT",			".");
define("P_VOID",		"");
define("P_NULL",    	"<null>");
define("P_EX",          "-");
define("P_FSS",			"___");
define("P_BR",      	"<br/>");
define("P_2BR",     	"<br/><br/>");
define("P_PREFIX",		"worm_");
define("P_EXACT",		" = ");
define("P_LIKE",		" like ");
define("P_OR",			" || ");
define("P_SELECT",		" select ");
define("P_FROM",		" from ");
define("P_WHERE",		" where ");
define("P_ORDER",		" order by ");
define("P_LIMIT",		" limit ");
define("P_FIELD_L",		"[");
define("P_FIELD_R",		"]");
define("P_EXTERNAL_L",	"{%");
define("P_EXTERNAL_R",	"%}");
define("P_AWESOME_L",	"{{");
define("P_AWESOME_R",	"}}");

class WORM {
    // kõik parameetrid (nb! need default'id kirjutatakse üle tabeli kirjeldusfaili ja ka worm.js poolt tulevate väärtustega üle)

    var
    $content, $db, $l, $mode, $target, $template, $url, $class, $data, $translations, $field_count,
    $database, $host, $username, $password, $charset, $collation, $table, $query, $fields, $where, $values,
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

        // pane väljade default'id paika

        //$this->field_defaults();

        // kirjuta default'id JS omadega üle (puhasta input)

		foreach ($init as $key => $val)
            $this->{ $key } = $this->safe($val);

        // kui tabeli kirjelduses on märgitud uus ühendus

        if ($this->host && $this->database && $this->username && $this->password) {
            $this->db = @new P_DATABASE();
            $this->db->connect($this->host, $this->database, $this->username, $this->password, $this->charset, $this->collation);
        }
        elseif (is_resource($source)) { // kui on antud olemasolev mysql resource link, siis tee uus klass ja topi link kohe külge
            $this->db = @new P_DATABASE();
            $this->db->connection = $source;
        }
        elseif (!$source) { // kui üldse midagi ei antud sisendiks
            $this->db = @new P_DATABASE();

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
        // et tabelikirjelduse failid oleks veidi mugavam ja lühem keelestringe välja kutsuda

        $l = &$this->l;

        // juhul kui on kasutaja poolt lisatud "data-worm", siis see override'b "id" template sihtmärgina

        if (isset($this->data["worm"]) && $this->data["worm"])
            $template = $this->data["worm"];
        else
            $template = $this->target;

        // tabeli kirjelduse template

        $this->template = WORMS. P_SL. $template. ".php";

        // lae vormi info

        if (file_exists($this->template)) {
            require_once($this->template);

            return true;
        }
        else {
            // väga halb, et tabeli kirjeldust ei leidnud

            return false;
        }
    }

    // valmista vorm ette

    function prepare_worm() {
    }

    // kuva vorm

    function output_worm() {
        $this->content .= "tere";
    }

    // tee JS tulev sisend turvaliseks

    function safe($input, $length = false) {
        if (!is_array($input)) {
            $output = preg_replace(P_DOTS, P_DOT, preg_replace(P_ALLOWED, P_VOID, trim($input)));

            if ($length)
                $output = substr($output, 0, $length);
        }
        else {
            foreach ($input as $key => $val) {
                $output[$key] = preg_replace(P_DOTS, P_DOT, preg_replace(P_ALLOWED, P_VOID, trim($val)));

                if ($length)
                    $output[$key] = substr($output[$key], 0, $length);
            }
        }

        return $output;
    }
}

?>
