var jade = {};
jade.rowTemplate = false;
jade.rowParent = false;
jade.pageAnalysis = false;
jade.pageProfileVersions = false;
jade.linkDownload = false;

jQuery(function() {
    console.log("JADE Profile Loaded");

    jade.pageAnalysis = jQuery("#page_analysis").val();
    jade.pageProfileVersions = jQuery("#page_profile_versions").val();
    // Todo: set get the download link
    jade.linkDownload = false;

    var nonce = jQuery("#nonce").val();
    jade.rowTemplate = jQuery("[rowtemplate]").clone();
    jade.rowParent = jQuery("[rowtemplate]").parent();
    jQuery("[rowtemplate]").remove();

    jQuery.ajax({
        type: "GET",
        url: "/wp-json/jade-tool/v1/profile",
        data: {},
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
        },
        success:
            function( data ) {
            jade.data = data;
                console.log(data);
                buildTable(data);
            }
    });
});

let buildTable = function(rows) {
    rows.forEach((row) => {
        let rowHTML = jade.rowTemplate.clone();
        jade.rowParent.append(rowHTML);

        rowHTML.find("[job-title] .elementor-text-editor").text(row["title"]);

        let date = moment(row["timestamp"], "YYYY-MM-DD HH:mm:ss").add(1, "hours");

        rowHTML.find("[date] .elementor-text-editor").text(date.format("DD.MM.YYYY HH:mm"));

        let lastversion = jQuery(rowHTML.find("[lastversion] .elementor-text-editor"));
        lastversion.text("");
        lastversion.append('<a href="' + jade.pageAnalysis + '&job=' + row["job_id"] + '">bearbeiten</a>');
        lastversion.append('<br>');
        lastversion.append('<a href="' + jade.pageAnalysis + '&job=' + row["job_id"] + '&print=pdf" target="_blank">downloaden</a>');

        let versions = jQuery(rowHTML.find("[versions] .elementor-text-editor"));
        versions.text("");
        versions.append('<a href="' + jade.pageProfileVersions + '&job=' + row["job_id"] + '">alle Versionen anzeigen</a>');

    });
}
