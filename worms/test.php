<?php

$this->table = "test";

$this->fields = [
    "field1" => [ "title" => "Eesnimi:", "type" => "text", "value" => "initial", "save" => [ "enter", "blur", "change" ] ],
    "field2" => [ "title" => "Perenimi:", "type" => "text", "required" => true, "save" => [ "enter" ] ],
    "field3" => [ "title" => "Amet:", "type" => "text" ],
    "field4" => [ "title" => "Osakond:", "type" => "text" ],
    "field5" => [ "title" => "Telefon:", "type" => "text" ],
];

// id:      [uid] + "_" + [field]
// descr:   [uid] + "_" + [field] + "_descr"

if ($this->end_it)
    return;

?>
<div class="w_group">
    <div id="[field1:descr]" class="w_descr">[field1:title]</div><div id="[field1:id]" class="w_content">[field1:value]</div>
</div>
<div class="w_group">
    <div id="[field2:descr]" class="w_descr">[field2:title]</div><div id="[field2:id]" class="w_content">[field2:value]</div>
</div>
<div class="w_group">
    <div id="[field3:descr]" class="w_descr">[field3:title]</div><div id="[field3:id]" class="w_content">[field3:value]</div>
</div>
<div class="w_group">
    <div id="[field4:descr]" class="w_descr">[field4:title]</div><div id="[field4:id]" class="w_content">[field4:value]</div>
</div>
<div class="w_group">
    <div id="[field5:descr]" class="w_descr">[field5:title]</div><div id="[field5:id]" class="w_content">[field5:value]</div>
</div>
