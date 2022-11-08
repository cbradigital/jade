jQuery(function() {
    console.log("JADE START Loaded V2.0");

    var rules = {};
    var messages = {};

    jQuery.validator.addMethod("selectValidator", function(value, element, arg){
        return value != -1;
    }, "Bitte wählen Sie aus der Liste einen Wert aus.");

    jQuery('[name^="jade"]').each(function( index ) {
        var name = jQuery( this ).attr("name");

        if (name == "jade-employer") return;
        if (name == "jade-weoffer") return;

        rules[name] = {
            required: true,
            minlength: 3,
        };
        messages[name] = {
            required: "Bitte füllen Sie das Feld aus.",
            minlength: "Bitte geben Sie mehr Zeichen ein.",
        }

        if (name == "jade-title") {
            rules[name].maxlength = 150;
            messages[name].required = "Bitte geben Sie den Jobtitel mit max. 150 Zeichen an.";
            messages[name].maxlength = "Bitte geben Sie maximal 150 Zeichen ein.";
        }
    });

    jQuery('[name^="stats"]').each(function( index ) {
        //console.log(jQuery(this).prop("tagName"));
    });

/* OPTIONALE FELDER BEI STATISTIK
 * 
    jQuery('[name^="stats"]').each(function( index ) {
        var name = jQuery( this ).attr("name");
        var type = jQuery( this ).attr("type");
        var tag = jQuery(this).prop("tagName");

        if (name == "stats-business-extra") return;
        if (name == "stats-corporate-division-extra") return;
        if (name == "stats-medium-extra") return;

        if (name.startsWith("stats-medium-")) return;

        rules[name] = {
            required: true,
        };
        messages[name] = {
            required: "Bitte füllen Sie das Feld aus.",
        }

        if (tag == "SELECT") {
            rules[name] = {
                selectValidator : "default"
            }
        }
        if (type == "radio") {
            messages[name].required = "Bitte wählen Sie eine Option.";
        }
    });
*/
    const form = jQuery("#jade-form");
    // Userdata
    var nonce = jQuery("#nonce").val();


    var userdata = {};

    jQuery.ajax({
        type: "GET",
        url: "/wp-json/jade-tool/v1/userdata",
        data: {},
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
        },
        success:
            function( data ) {
                console.log("Userdata loaded4");
                console.log(data);
                console.log(data.user_companysize.charCodeAt(26))
                data.user_companysize = data.user_companysize.replace('-', '–');
                //data.user_companysize = data.user_companysize.replace('–', '-');
                userdata = data;

                console.log(data);
                console.log(data.user_companysize.charCodeAt(26))
            }
    });

    jQuery('[name=stats-mycompany]').click(function() {
        if (jQuery(this).val() == "yes") {
            preFillUserData();
        } else {
            jQuery("[name=stats-business]").val(-1);
            jQuery("[name=stats-companysize]").val(-1);
        }
    });

    const preFillUserData = () => {
        var idBranche = jQuery("[name=stats-business] :contains(" + userdata.user_branche + ")").val();
        var idSize = jQuery("[name=stats-companysize] :contains(" + userdata.user_companysize + ")").val();
        console.log("Branche:", idBranche);
        console.log("Size:", idSize);


        if (idBranche) jQuery("[name=stats-business]").val(idBranche);
        if (idSize) jQuery("[name=stats-companysize]").val(idSize);

    }

    // Validator
    const validator = jQuery("#jade-form").validate({
        rules : rules,
        // Specify validation error messages
        messages: messages,

        errorPlacement: function(error, element) {
            //error.attr("style", "color: orange; display: block;");
            if ( element.is(":radio") )  {
                error.appendTo(element.parent().parent());
            } else { // This is the default behavior
                error.insertAfter( element );
            }
        },

        submitHandler: function(form) {
            console.log("Submit Handler Event");
            form.submit();
        }
    });

    gForm = form;
    gValidator = validator;

    jQuery("#jade-submit").click(function(e) {
        console.log("jade-submit / Click Event");
        e.preventDefault();
        const valid = validator.form();
        console.log("Form Valid: " + valid);
        if (valid) {
            form.submit();
        }
    });


});