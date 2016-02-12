<?php

$this->table = "test";

$this->fields = [
    "f1" => [ "field" => "field1", "title" => "Eesnimi:", "type" => "text", "value" => "initial" ],
    "f2" => [ "field" => "field2", "title" => "Perenimi:", "type" => "text", "required" => true ],
    "f3" => [ "field" => "field3", "title" => "Amet:", "type" => "text" ],
    "f4" => [ "field" => "field4", "title" => "Osakond:", "type" => "text" ],
    "f5" => [ "field" => "field5", "title" => "Telefon:", "type" => "text" ],
];

// id:      [uid] + "_" + [field]
// descr:   [uid] + "_" + [field] + "_descr"

if ($this->partial_parse)
    return;

?>
<div class="w_group">
    <div id="[f1:descr]" class="w_descr">[f1:title]</div><div id="[f1:id]" class="w_content">[f1:value]</div>
</div>
<div class="w_group">
    <div id="[f2:descr]" class="w_descr">[f2:title]</div><div id="[f2:id]" class="w_content">[f2:value]</div>
</div>
<div class="w_group">
    <div id="[f3:descr]" class="w_descr">[f3:title]</div><div id="[f3:id]" class="w_content">[f3:value]</div>
</div>
<div class="w_group">
    <div id="[f4:descr]" class="w_descr">[f4:title]</div><div id="[f4:id]" class="w_content">[f4:value]</div>
</div>
<div class="w_group">
    <div id="[f5:descr]" class="w_descr">[f5:title]</div><div id="[f5:id]" class="w_content">[f5:value]</div>
</div>
