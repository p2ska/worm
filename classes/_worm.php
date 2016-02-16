<?php

// [worm]; Andres Päsoke

define("W_ALLOWED",		"/[^\p{L}\p{N}\s\:\.@_-]/u");
define("W_DOTS",		"/\.+/");
define("W_DOT",			".");
define("W_VOID",		"");
define("W_SEP",			"-");
define("W_COLON",       ":");
define("W_SL",			"/");
define("W_TAG_L",       "[");
define("W_TAG_R",       "]");
define("W_AWESOME_L",	"{{");
define("W_AWESOME_R",	"}}");

class WORM {
    // kõik parameetrid (nb! need default'id kirjutatakse üle tabeli kirjeldusfaili ja ka worm.js poolt tulevate väärtustega üle)

    var $content, $db, $translations, $l, $target, $uid, $template, $end_it,
        $database, $host, $username, $password, $charset, $collation, $table,
        $data, $fields,
		$table_type = "fields",	// tabeli tüüp (salvestamine: kõik väljad eraldi või json tüüpi salvestus)
		$save	= "blur",		// vormi default salvestustüüp
        $debug	= true;         // debug reziim

    // initsialiseeri kõik js poolt määratud muutujad

    function worm($data, $source = false, $lang = false) {
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

        if (!$this->init($data, $lang))
            return false;

        // hangi template

        $this->process_template();

        // elemendi salvestamine

        if (isset($data["action"])) {
            switch ($data["action"]) {
                case "load": $this->load_element($data); break;
                case "save": $this->save_element($data); break;
                default: break;
            }
        }
    }

    // init

    function init($data, $lang) {
        // kas vajalik info on ikka olemas

		if (!isset($data["target"]) || !isset($data["data"]))
            return false;

        // data[] muutuja edastamiseks vormi kirjeldusele

        $this->target = $this->safe($data["target"]);
        $this->data = $this->safe($data["data"]);

        // kui pole väliseid tõlkeid juba, siis lae tabeli tõlkefailist;
        // kui translations klassi ka pole, noh siis polegi tõlkeid

        if (!$lang && class_exists("TRANSLATIONS")) {
            $this->translations = new TRANSLATIONS();

            $this->l = $this->translations->import("lang/worm.lang");
        }
        else
            $this->l = $lang;

        // hangi vormi template ja uid

        if (isset($this->data["worm"]) && substr_count($this->data["worm"], W_COLON))
            list($this->template, $this->uid) = explode(W_COLON, $this->data["worm"]);
        else
            return false;

        // kui on salvestamise või lugemise või valideerimisega tegu, siis ei tohi töödelda vormi kirjeldustest edasi

        if (isset($data["action"]) && $data["action"])
            $this->end_it = true;

        // kirjuta default'id JS omadega üle (ja puhasta input)

		foreach ($data as $key => $val)
            $this->{ $key } = $this->safe($val);

        return true;
    }

    // valmista vorm ette

    function process_template() {
        // tabeli kirjelduse template

        $template_file = WORMS. W_SL. $this->template. ".php";

        // kas template eksisteerib?

        if (!file_exists($template_file))
            return false;

        // et vormikirjeldustes oleks veidi mugavam ja lühem keelestringe välja kutsuda

        $l = &$this->l;

        // lae template sisu

        ob_start();

        require_once $template_file;

        // kui pole template kuvamisega tegu (väärtuse salvestamine/lugemine/valideerimine), siis ära parsi edasi esimesest php' blokist

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

            if (stripos($markup, W_COLON) === false) {
                $pos = $start + $length + $tag_r;

                continue;
            }

            list($field, $key) = explode(W_COLON, $markup);

            // asendusväärtus

            $value = W_VOID;

            // kas korrektselt kirjeldatud vormielement eksisteerib?

            if ($field && $key && isset($this->fields[$field])) {
                $id = $this->uid. W_SEP. $field;
                // lisa välja id

                if ($key == "id") {
                    $value = $id;
                } // väljakirjelduse id
                elseif ($key == "descr") {
                    $value = $id. W_SEP. "descr";
                } // kui on väärtuse printimisega tegu, siis vaata kas on juba tabelis väärtus olemas
                elseif ($key == "element") {
                    // kuva väärtus ja lisa vormielement

                    $value = "<div id=\"". $id. W_SEP. "value\" class=\"w_value\">". $this->format_value($field). "</div>";
                    $value.= "<div id=\"". $id. W_SEP. "field\" class=\"w_field\">". $this->element($field). "</div>";
                } // teiste väljamuutujate asendamine
                elseif (isset($this->fields[$field][$key])) {
                    $value = $this->fields[$field][$key];

                    // kui on kohustuslik väli, siis kuva '*'

                    if ($key == "title" && isset($this->fields[$field]["required"]) && $this->fields[$field]["required"])
                        $value .= "<span class=\"w_required\">*</span>";
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

	// formaadi väärtus (kuvamiseks)

	function format_value($field) {
		$value = str_replace("\n", "<br/>", $this->get_value($field));

		if ($this->fields[$field]["type"] == "radio") {
			if ($value) {
				if (isset($this->fields[$field]["values"][$value]) && $this->fields[$field]["values"][$value])
					$value = $this->fields[$field]["values"][$value];
			}
		}
		elseif ($this->fields[$field]["type"] == "checkbox") {
			if ($value) {
				$vals = [];

				foreach (explode(W_COLON. W_COLON, $value) as $val) {
					if (isset($this->fields[$field]["values"][$val]) && $this->fields[$field]["values"][$val])
						$vals[] = $this->fields[$field]["values"][$val];
				}

				$value = implode(" ", $vals);
			}
		}

		if ($value)
			$value = "<u>". $value. "</u>";
		else
			$value = "<u class=\"w_empty\">". $this->l->txt_empty. "</u>"; // ". $this->l->empty. "

		return $value;
	}

    // vormielemendid

    function element($field) {
        $el = $style = $class = $dialog = $empty = $save = W_VOID;
        $id = $this->uid. W_SEP. $field. W_SEP. "element";

		// vormielemendi salvestustüüp

		if (isset($this->fields[$field]["save"]) && $this->fields[$field]["save"]) {
			if (in_array($this->fields[$field]["save"], [ "dialog", "blur", "change" ]))
				$save = $this->fields[$field]["save"];
			else
				$save = $this->save;
		}
		else {
			$save = $this->save;
		}

		// dialoogi puhul lisa valikud

		if ($save == "dialog") {
			$dialog = " <span class=\"fa fa-check-circle w_save\"></span>";
			$dialog.= " <span class=\"fa fa-times-circle w_cancel\"></span>";
		}

		$class = " class=\"w_". $save;

		// kui on määratud kirjelduses lisaklasse

		if (isset($this->fields[$field]["class"]) && $this->fields[$field]["class"])
			$class .= " ". $this->fields[$field]["class"];

		$class .= "\"";

		// kui on lisatud stiil

		if (isset($this->fields[$field]["style"]) && $this->fields[$field]["style"])
			$style = " style=\"". $this->fields[$field]["style"]. "\"";

		switch ($this->fields[$field]["type"]) {
			case "text":
				$el = "<input id=\"". $id. "\" type=\"text\"". $class. $style. " value=\"\">";

				break;

			case "textarea":
				$el = "<textarea id=\"". $id. "\"". $class. $style. "></textarea>";

				break;

			case "radio":
				foreach ($this->fields[$field]["values"] as $value => $descr)
					$el .= "<input id=\"". $id. W_SEP. $value. "\" name=\"". $id. "\" type=\"radio\" ". $class. $style. " value=\"". $value. "\"> ". $descr;

				break;

			case "checkbox":
				foreach ($this->fields[$field]["values"] as $value => $descr)
					$el .= "<input id=\"". $id. W_SEP. $value. "\" name=\"". $id. "[]\" type=\"checkbox\" ". $class. $style. " value=\"". $value. "\"> ". $descr. " ";

				break;

			default:

				break;
        }

		// kui on vormielemendi kustutamine lubatud

		if ($save != "blur" && ($this->fields[$field]["type"] == "text" || $this->fields[$field]["type"] == "textarea"))
			$empty = "<span class=\"fa fa-times-circle w_erase\"></span>";

		$el .= $empty. $dialog;

        return $el;
    }

    // lae väärtus

    function load_element($element) {
        list($uid, $field) = explode(W_SEP, substr($element["element"], 1));

        // tagasta väärtus

		$this->content = $this->get_value($field);
    }

    // salvesta element

    function save_element($element) {
        list($uid, $field) = explode(W_SEP, substr($element["element"], 1));

		if (isset($element["content"])) {
			// kontrolli, kas selle elemendi puhul on soovitud meetodiga salvestamine lubatud

        	if (!isset($this->fields[$field]["save"]) || $element["method"] == $this->fields[$field]["save"]) {
				// kui on massiiv (checkboxide väärtused)

				if (is_array($element["content"]))
					$element["content"] = implode(W_COLON. W_COLON, $element["content"]);

            	$this->set_value($field, $element["content"]);
			}
		}

        // tagasta väärtus

        $this->content = $this->format_value($field);
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
