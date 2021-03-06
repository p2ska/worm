<?php

$this->table = "test";
$this->table_type = "fields"; // "json";
//$this->save = "dialog";

$this->fields = [
    "field1" => [ "title" => "text:", "type" => "text", "style" => "width: 400px", "save" => "dialog", "typeahead" => true ],
    "field2" => [ "title" => "textarea:", "type" => "textarea", "style" => "width: 400px; height: 100px", "required" => true ],
    "field3" => [ "title" => "radio:", "type" => "radio", "values" => [ 1 => "esimene", 2 => "teine" ], "save" => "blur" ],
    "field4" => [ "title" => "checkbox:", "type" => "checkbox", "values" => [ 1 => "üks", 2 => "kaks" ], "save" => "blur" ],
    "field5" => [ "title" => "select:", "type" => "select", "values" => [ 0 => "- vali -", 1 => "first", 2 => "second" ], "external-data" => "" ],
    "field6" => [ "title" => "datepicker:", "type" => "date", "save" => "date" ],
    "status" => [ "title" => "confirm:", "type" => "button", "action" => "submit", "values" => [ 0 => "confirm", 1 => "change" ] ],
    "reset"  => [ "title" => "reset:", "type" => "button", "action" => "reset", "value" => "reset" ],
    "cancel" => [ "title" => "cancel:", "type" => "button", "action" => "cancel", "value" => "cancel" ],
];

if ($this->end_it)
    return;

?>
<div class="w_group">
    <div id="[field1:descr]" class="w_descr">[field1:title]</div><div id="[field1:id]" class="w_content">[field1:element]</div>
</div>
<div class="w_group">
    <div id="[field2:descr]" class="w_descr">[field2:title]</div><div id="[field2:id]" class="w_content">[field2:element]</div>
</div>
<div class="w_group">
    <div id="[field3:descr]" class="w_descr">[field3:title]</div><div id="[field3:id]" class="w_content">[field3:element]</div>
</div>
<div class="w_group">
    <div id="[field4:descr]" class="w_descr">[field4:title]</div><div id="[field4:id]" class="w_content">[field4:element]</div>
</div>
<div class="w_group">
    <div id="[field5:descr]" class="w_descr">[field5:title]</div><div id="[field5:id]" class="w_content">[field5:element]</div>
</div>
<div class="w_group">
    <div id="[field6:descr]" class="w_descr">[field6:title]</div><div id="[field6:id]" class="w_content">[field6:element]</div>
</div>
<div class="w_group" style="margin-top: 50px">
    <div id="[status:descr]" class="w_descr">[status:title]</div>
    <div id="[status:id]" class="w_content">[status:button] [reset:button] [cancel:button]</div>
</div>
