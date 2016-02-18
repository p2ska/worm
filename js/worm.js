var worm_url = "worm.php";

(function($) {
    $.fn.worm = function(targets) {
        var element_obj, element_type, value_id, element_id, field_id,
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

				if ($(element_id)[0] && element_type != "select")
                	$(element_id)[0].setSelectionRange(10000, 10000);
            });

            // kustuta elemendi sisu ristile klikkimise korral

            $("#" + worm).on("click", ".w_erase", function() {
				get_id($(this).closest("div"));

				$(element_id).val("").focus();
            });

			// salvesta datepickeri puhul

            $("#" + worm).on("change", ".w_date", function() {
				get_id($(this));

                save_element(worm, data, element_obj, "date");
            });

            // salvesta vormielement dialoogi korral

            $("#" + worm).on("click", ".w_save", function() {
				get_id($(this).closest("div"));

				save_element(worm, data, element_obj, "dialog");
            });

            // ära salvesta vormielementi dialoogi korral

            $("#" + worm).on("click", ".w_cancel", function() {
				get_id($(this).closest("div"));

				$(field_id).hide();
				$(value_id).show();
            });

            // nupuloogika

            $("#" + worm).on("click", ".w_button", function() {
                get_id($(this));
                check_worm(worm, data, field_id);
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

        // hangi id'd ja elemendi tüüp

		function get_id(element) {
            var id = element.prop("id").split("-");

			element_obj = element;
            element_type = $("#" + id[0] + "-" + id[1]).data("type");

            value_id = "#" + id[0] + "-" + id[1] + "-value";
            field_id = "#" + id[0] + "-" + id[1] + "-field";
            element_id = "#" + id[0] + "-" + id[1] + "-element";
		}

        // kuva vorm

        function output(worm, data) {
            $.ajax({
                url: worm_url,
                data: {
                    worm: {
                        target: worm,
                        data: data
                    }
                }
            }).done(function(result) {
                $("#" + worm).html(result);

                $(".w_date").datepicker({
                    startDate:          "01.01.2013",
                    endDate:            "31.12.2020",
                    language:           "et",
                    autoclose:          true,
                    keyboardNavigation: false,
                    calendarWeeks:      false,
                    todayHighlight:     true,
                    weekStart:          1,
                    format:             "dd.mm.yyyy"
                });
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
                var results = jQuery.parseJSON(result);

                // väärtusta väljad

                $.each(results, function(el, value) {
                    if (element_type == "checkbox" || element_type == "radio")
                        $(el).prop("checked", true);
                    else if (element_type == "select")
                        $(el).prop("selected", true);
                    else
                        $(el).val(value);
                });
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

				var value;

				if (element_type == "radio")
					value = $("input[name=" + element_id.substr(1) + "]:checked").val();
				else if (element_type == "checkbox")
					value = $("input[name='" + element_id.substr(1) + "[]']:checked").map(function() { return this.value }).get();
				else
					value = $(element_id).val();

                console.log(element_id + ":" + value);

                $.ajax({
                    url: worm_url,
                    data: {
                        worm: {
                            target: worm,
                            data: data,
                            action: "save",
                            method: method,
                            element: element_id,
                            value: value
                        }
                    }
                }).done(function(result) {
                    $(value_id).html(result);

                    last_save = Date.now();
                });
            }
        }

        // vormi valideerimine/reset/sulgemine

        function check_worm(worm, data, method) {
            $.ajax({
                url: worm_url,
                data: {
                    worm: {
                        target: worm,
                        data: data,
                        action: "validate",
                        method: method,
                    }
                }
            }).done(function(result) {
                alert(result);
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
