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
define("W_US",          "_");
define("W_TAG_L",       "[");
define("W_TAG_R",       "]");
define("W_AWESOME_L",	"{{");
define("W_AWESOME_R",	"}}");

class WORM {
    // kõik parameetrid (nb! need default'id kirjutatakse üle tabeli kirjeldusfaili ja ka worm.js poolt tulevate väärtustega üle)

    var
    $content, $db, $translations, $l, $target, $uid, $template, $template_file,
    $database, $host, $username, $password, $charset, $collation, $table,
    $data, $fields,
    $debug			= true;         // debug reziim

    // initsialiseeri kõik js poolt määratud muutujad

    function worm($init, $source = false, $lang = false) {
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

        // elemendi salvestamine

        if (isset($init["save"]))
            return $this->save_element($init);

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

        // kirjuta default'id JS omadega üle (puhasta input)

		foreach ($init as $key => $val)
            $this->{ $key } = $this->safe($val);

        // seadista vorm

        $this->prepare_worm();
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

    // asenda tag'id

    function replace_tags($content) {
        $pos = 0;
        $start = $end = $found = false;
        $tag_l = strlen(W_TAG_L);
        $tag_r = strlen(W_TAG_R);

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

            // kui ei leitud korrektset välja- ja tüübikirjeldust, siis otsi järgmist

            if (stripos($markup, W_SEP) === false) {
                $pos = $start + $length + $tag_r;

                continue;
            }

            list($field, $key) = explode(W_SEP, $markup);

            // asendusväärtus

            $value = W_VOID;

            // kas korrektselt kirjeldatud vormielement eksisteerib?

            if ($field && $key && isset($this->fields[$field]) && isset($this->fields[$field]["field"])) {
                $id = $this->uid. W_US. $this->fields[$field]["field"];
                // lisa välja id

                if ($key == "id") {
                    $value = $id;
                } // väljakirjelduse id
                elseif ($key == "descr") {
                    $value = $id. W_US. "descr";
                } // kui on väärtuse printimisega tegu, siis vaata kas on juba tabelis väärtus olemas
                elseif ($key == "value") {
                    $value = $this->get_value($this->fields[$field]["field"]);

                    // kui tulemust tabelist ei leitud, siis lisa kirjelduses olev default (kui on seatud)

                    if ($value === false && isset($this->fields[$field]["value"]))
                        $value = "<u>". $this->fields[$field]["value"]. "</u>";
                    elseif ($value)
                        $value = "<u>". $value. "</u>";
                    else
                        $value = "<u class=\"w_empty\">[tühi]</u>"; // ". $this->l->empty. "

                    // paiguta väärtus div'i sisse ja lisa vormielement

                    $value = "<div id=\"". $id. W_US. "value\" class=\"w_value\">". $value. "</div>";
                    $value.= "<div id=\"". $id. W_US. "field\" class=\"w_field\">". $this->element($this->fields[$field]). "</div>";
                } // tavalise väljamuutuja asendamine
                elseif (isset($this->fields[$field][$key])) {
                    $value = $this->fields[$field][$key];
                }
            }

            // asenda markup ja liiguta lugemispositsiooni

            $content = str_replace($repl, $value, $content);
            $pos = $start + strlen($value) + $tag_r;
        }

        return $content;
    }

    // hangi välja väärtus baasist

    function get_value($field) {
        if (!$this->db->query("select ". $field. " as value from ". $this->table. " where uid = ?", [ $this->uid ])) {
            return false;
        }
        else {
            $obj = $this->db->get_obj();

            return $obj->value;
        }
    }

    function set_value($field, $value) {
        if (!$this->db->query("select ". $field. " as value from ". $this->table. " where uid = ?", [ $this->uid ])) {
            $this->db->query("insert into ". $this->table. " set uid = ?, ". $field. " = ?", [ $this->uid, $value ] );
        }
        else {
            $this->db->query("update ". $this->table. " set uid = ?, ". $field. " = ?", [ $this->uid, $value ] );
        }
    }

    // vormielemendid

    function element($field) {
        $el = W_VOID;

        $id = $this->uid. W_US. $field["field"]. W_US. "element";
        $value = $this->get_value($field["field"]);

        if ($field["type"] == "text") {
            $el = "<input id=\"". $id. "\" type=\"text\" value=\"". $value. "\" class=\"w_element\">";
        }

        return $el;
    }

    // salvesta element

    function save_element($element) {
        list($uid, $field) = explode("_", substr($element["save"], 1));

        $this->uid = $uid;
        $this->table = "test";

        $this->set_value($field, $element["content"]);

        echo "<u>". $this->get_value($field). "</u>";
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
