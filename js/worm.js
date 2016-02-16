var worm_url = "worm.php";

(function($) {
    $.fn.worm = function(targets) {
        var last_save = false,
            debug = false;

        if (targets === undefined)
            targets = ".worm";

		$(targets).each(function() {
            var worm = $(this).prop("id");
            var data = user_data($(this).data());

            output(worm, data);

            // kuva vormielement

            $("#" + worm).on("click", ".w_value", function() {
                var value_id = "#" + $(this).prop("id");
                var id = value_id.split("_");
                var field_id = id[0] + "_" + id[1] + "_field";
                var element_id = id[0] + "_" + id[1] + "_element";

                $(value_id).hide();

                load_element(worm, data, $(this));

                $(field_id).show();
                $(element_id).focus();
                //$(element_id)[0].setSelectionRange(10000, 10000);
            });

            // salvesta vormielement fookuse kadumise korral

            $("#" + worm).on("focusout", ".w_element", function() {
                save_element(worm, data, $(this), "blur");

                console.log("saved: focusout");
            });

            // salvesta vormielement väärtuse muutumise korral

            $("#" + worm).on("change", ".w_element", function() {
                save_element(worm, data, $(this), "change");

                console.log("saved: change");
            });

            // salvesta vormielement enteri korral (va textarea?) //// ilmselt seda pole siiski vaja kui "on change" on olemas juba

            /*
            $("#" + worm).on("keyup", ".w_element", function(e) {
                if (e.keyCode == 13) {
                    save_element(worm, data, $(this));

                    console.log("saved: enter");
                }
            });
            */
        });

        // kuva vorm

        function output(worm, data) {
            $.ajax({ url: worm_url, data: { worm: { target: worm, data: data } } }).done(function(content) {
                $("#" + worm).html(content);
            });
        }

        // lae element

        function load_element(worm, data, el) {
            var element_id = "#" + $(el).prop("id");
            var id = element_id.split("_");
            var field_id = id[0] + "_" + id[1] + "_field";
            var value_id = id[0] + "_" + id[1] + "_value";

            $.ajax({
                url: worm_url,
                data: {
                    worm: {
                        target: worm,
                        data: data,
                        action: "load",
                        element: element_id
                    }
                }
            }).done(function(result) {
                $(field_id).val(result);
            });
        }

        // salvesta element

        function save_element(worm, data, el, method) {
            var element_id = "#" + $(el).prop("id");
            var id = element_id.split("_");
            var field_id = id[0] + "_" + id[1] + "_field";
            var value_id = id[0] + "_" + id[1] + "_value";

            $(field_id).hide();

            if (last_save && (Date.now() - last_save) < 250) {
                $(value_id).show();

                console.log("not saved: last save was less than 250ms ago");
            }
            else {
                $(value_id).html("").show();

                $.ajax({
                    url: worm_url,
                    data: {
                        worm: {
                            target: worm,
                            data: data,
                            action: "save",
                            method: method,
                            element: element_id,
                            content: $(element_id).val()
                        }
                    }
                }).done(function(result) {
                    $(value_id).html(result);

                    last_save = Date.now();
                });
            }
        }

        // kasutaja andmed

        function user_data(data) {
            var udata = {};

            if (data) {
                $.each(data, function(key, val) {
                    udata[key] = val;
                });
            }

            return udata;
        }

        // korralikum logi formaatimine

        function clog(where, what, block) {
            if (!debug)
                return false;

            var date = new Date();
            var hrs = date.getHours(), min = date.getMinutes(), sec = date.getSeconds();
            var time = "[" + hrs + ":" + (min < 10 ? "0" + min : min) + ":" + (sec < 10 ? "0" + sec : sec) + "] ";
            var sep = "-------------------------";

            if (where === "-") {
                console.log(sep);
            }
            else if (log_block && (what === ";" || block === ";")) {
                log_block = false;

                console.log("\t" + where);
                console.log("}");
            }
            else if (block) {
                log_block = true;

                console.log(time + fixed_len(block).toUpperCase() + ": " + where + " {");
                console.log("\t" + what);
            }
            else {
                if (log_block)
                    console.log("\t" + where);
                else
                    console.log(time + fixed_len(where).toUpperCase() + ": " + what);
            }
        }

        // clog'i kirje joondamise jaoks

        function fixed_len(str, count) {
            var l = str.length;

            if (!count)
                count = 10;

            if (l > count)
                return str.substr(0, count);
            else
                return str + new Array(count + 1 - l).join(" ");
        }
    };

    $().worm();
}(jQuery));
