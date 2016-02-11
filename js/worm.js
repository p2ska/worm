var worm_url = "worm.php";

(function($) {
    $.fn.worm = function(targets) {
        var prefix      = "#worm_",
            last_save   = false,
            debug       = false,
            settings    = [];

        if (targets === undefined)
            targets = ".worm";

		$(targets).each(function() {
            var worm = $(this).prop("id");

            settings[worm] = {
                target:		$(this).prop("id"),
                class:		$(this).prop("class"),
                data:		user_data($(this).data()),
                url:		worm_url
            }

            // vormi kuvamine

            output(worm);
        });

        // kuva vormielement

        $(document).on("click", ".w_value", function() {
            var value_id = "#" + $(this).prop("id");
            var id = value_id.split("_");
            var field_id = id[0] + "_" + id[1] + "_field";
            var element_id = id[0] + "_" + id[1] + "_element";

            $(value_id).hide();
            $(field_id).show();
            $(element_id).focus();
            $(element_id)[0].setSelectionRange(10000, 10000);
        });

        // salvesta vormielement fookuse kadumise korral

        $(document).on("focusout", ".w_element", function() {
            if (last_save && (ct - last_save) < 250)
                save_element($(this));
        });

        $(document).on("keyup", ".w_element", function(e) {
            if (e.keyCode == 13) {
                save_element($(this));
            }
        });

        function save_element(el) {
            var element_id = "#" + $(el).prop("id");
            var id = element_id.split("_");
            var field_id = id[0] + "_" + id[1] + "_field";
            var value_id = id[0] + "_" + id[1] + "_value";

            $(field_id).hide();
            $(value_id).html("").show();

            $.ajax({ url: worm_url, data: { worm: { save: element_id, content: $(element_id).val() } } }).done(function(result) {
                $(value_id).html(result);
                $(element_id).val("");
                last_save = Date.now();
            });
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

        // kuva vorm

        function output(worm) {
            $.ajax({ url: settings[worm].url, data: { worm: settings[worm] } }).done(function(content) {
                $("#" + settings[worm].target).html(content);

				//settings[ptable].page = $(prefix + settings[ptable].target).data("page");

                /*
                clog(worm, "page       = " + settings[ptable].page, "updated");
                clog("autoupdate = " + settings[ptable].autoupdate, ";");
                */
            });
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
