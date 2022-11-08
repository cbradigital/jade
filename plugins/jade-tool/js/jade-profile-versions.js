var jade = {};
jade.jobId = false;
jade.rowTemplate = false;
jade.rowParent = false;
jade.pageAnalysis = false;
jade.pageProfile = false;
jade.linkDownload = false;

jQuery(function() {
    console.log("JADE Profile Loaded");

    jade.jobId = jQuery("#job_id").val();
    jade.pageAnalysis = jQuery("#page_analysis").val();
    jade.pageProfile = jQuery("#page_profile").val();
    // Todo: set get the download link
    jade.linkDownload = false;

    var nonce = jQuery("#nonce").val();
    jade.rowTemplate = jQuery("[rowtemplate]").clone();
    jade.rowParent = jQuery("[rowtemplate]").parent();
    jQuery("[rowtemplate]").remove();

    jQuery.ajax({
        type: "GET",
        url: "/wp-json/jade-tool/v1/profile-versions?job_id=" + jade.jobId,
        data: {},
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
        },
        success:
            function( data ) {
                jade.data = data;
                console.log(data);
                jQuery("[jade-title-main] h2").text(data[0]["title"]);

                buildTable(data);
            }
    });
});

let buildTable = function(rows) {
    rows.forEach((row) => {
        let rowHTML = jade.rowTemplate.clone();
        jade.rowParent.children().last().before(rowHTML);

        rowHTML.find("[jade-title] .elementor-text-editor").text(row["title"]);

        let date = moment(row["timestamp"], "YYYY-MM-DD HH:mm:ss").add(1, "hours");
        rowHTML.find("[timestamp] .elementor-text-editor").text(date.format("DD.MM.YYYY HH:mm"));

        let lastversion = jQuery(rowHTML.find("[versions] .elementor-text-editor"));
        lastversion.text("");
        lastversion.append('<a href="' + jade.pageAnalysis + '&version_id=' + row["id"] + '">ansehen</a>');
        lastversion.append('&nbsp;');
        lastversion.append('<a href="' + jade.pageAnalysis + '&version_id=' + row["id"] + '&print=pdf" target="_blank">downloaden</a>');

    });
}
