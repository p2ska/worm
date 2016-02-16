var worm_url = "worm.php";

(function($) {
    $.fn.worm = function(targets) {
        var current_element, value_id, element_id, field_id,
			last_save = false,
            debug = false;

        if (targets === undefined)
            targets = ".worm";

		$(targets).each(function() {
            var worm = $(this).prop("id");
            var data = user_data($(this).data());

            output(worm, data);

            // kuva vormielement

            $("#" + worm).on("click", ".w_value", function() {
				get_id($(this));

                $(value_id).hide();

                load_element(worm, data, $(this));

				$(field_id).show();
                $(element_id).focus();

				if ($(element_id)[0])
                	$(element_id)[0].setSelectionRange(10000, 10000);
            });

            // kustuta elemendi sisu ristile klikkimise korral

            $("#" + worm).on("click", ".w_erase", function() {
				get_id($(this).closest("div"));

				$(element_id).val("").focus();
            });

			// salvesta vormielement dialoogi korral

            $("#" + worm).on("click", ".w_save", function() {
				get_id($(this).closest("div"));

				save_element(worm, data, current_element, "dialog");
            });

            // ära salvesta vormielementi dialoogi korral

            $("#" + worm).on("click", ".w_cancel", function() {
				get_id($(this).closest("div"));

				$(field_id).hide();
				$(value_id).show();
            });

			// salvesta vormielement fookuse kadumise korral

            $("#" + worm).on("focusout", ".w_blur", function() {
                save_element(worm, data, $(this), "blur");
            });

            // salvesta vormielement väärtuse muutumise korral

            $("#" + worm).on("change", ".w_change", function() {
                save_element(worm, data, $(this), "change");
            });
        });

		// hangi id'd

		function get_id(element) {
            var id = element.prop("id").split("-");

			current_element = element;

			value_id = "#" + id[0] + "-" + id[1] + "-value";
            field_id = "#" + id[0] + "-" + id[1] + "-field";
            element_id = "#" + id[0] + "-" + id[1] + "-element";
		}

        // kuva vorm

        function output(worm, data) {
            $.ajax({ url: worm_url, data: { worm: { target: worm, data: data } } }).done(function(content) {
                $("#" + worm).html(content);
            });
        }

        // lae element

        function load_element(worm, data, element) {
			get_id(element);

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
                $(element_id).val(result);
            });
        }

        // salvesta element

        function save_element(worm, data, element, method) {
			get_id(element);

            $(field_id).hide();

            if (last_save && (Date.now() - last_save) < 250) {
                $(value_id).show();

                console.log("not saved: last save was less than 250ms ago");
            }
            else {
                $(value_id).html("").show();

				var content;
				var type = $(element).attr("type");

				if (type == "radio")
					content = $("input[name=" + element_id.substr(1) + "]:checked").val();
				else if (type == "checkbox")
					content = $("input[name='" + element_id.substr(1) + "[]']:checked").map(function() { return this.value }).get();
				else
					content = $(element_id).val();

                $.ajax({
                    url: worm_url,
                    data: {
                        worm: {
                            target: worm,
                            data: data,
                            action: "save",
                            method: method,
                            element: element_id,
                            content: content
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
