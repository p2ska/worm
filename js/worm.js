var worm_url = "worm.php";

(function($) {
    $.fn.worm = function(targets) {
        var prefix      = "#worm_",
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
