<?php

define("JADE_TABLE_DICTIONARY", $wpdb->prefix . "jade_dictionary");
define("JADE_TABLE_JOBS", $wpdb->prefix . "jade_jobs");

$jade_sections = array(
    0 => 'Job title',
    1 => 'Arbeitgeber',
    2 => 'Tätigkeit',
    3 => 'Anforderungen',
    4 => 'Benefits',
);

$jade_sections_fields = array(
    0 => 'jade-title',
    1 => 'jade-employer',
    2 => 'jade-jobdescription',
    3 => 'jade-requirementprofil',
    4 => 'jade-weoffer',
);

$jade_dimensions = array(
    0 => 'Alter',
    1 => 'Geschlecht'
);

$jade_expressions = array(
    0 => 'jünger',
    1 => 'älter',
    2 => 'männlich',
    3 => 'weiblich',
);

$jade_expressions_fields = array(
    0 => 'younger',
    1 => 'elder',
    2 => 'male',
    3 => 'female',
);

$jade_export_fields_stats = array(
    "stats-mycompany",
    "stats-business",
    "stats-business-extra",
    "stats-companysize",
    "stats-location",
    "stats-employmentform",
    "stats-employmentduration",
    "stats-professionalfield",
    "stats-corporate-division",
    "stats-corporate-division-extra",
    "stats-qualificationlevel",
    "stats-leadershipposition",
    "stats-medium-print",
    "stats-medium-ams",
    "stats-medium-online",
    "stats-medium-companywebsite",
    "stats-medium-intern",
    "stats-medium-extra",
//    "stats-publication",
    "stats-year",
    "stats-responsebility",
);


$jade_csv_header_translation = array(
"id" => "id",
"job_id" => "job_id",
"timestamp" => "timestamp",
"stats-mycompany" => "mycompany",
"stats-business" => "business",
"stats-business-extra" => "business_else",
"stats-companysize" => "company_size",
"stats-location" => "location",
"stats-employmentform" => "employment_type",
"stats-employmentduration" => "fixedterm",
"stats-professionalfield" => "profession",
"stats-corporate-division" => "corp_division",
"stats-corporate-division-extra" => "corp_division_else",
"stats-qualificationlevel" => "edu_level",
"stats-leadershipposition" => "lead_position",
"stats-medium-print" => "medium_print",
"stats-medium-ams" => "medium_ams",
"stats-medium-online" => "medium_jobportal",
"stats-medium-companywebsite" => "medium_corpweb",
"stats-medium-intern" => "medium_internal",
"stats-medium-extra" => "medium_else",
"stats-year" => "year",
"stats-responsebility" => "author",
"jade-title" => "title_text",
"jade-employer" => "employer_text",
"jade-jobdescription" => "jobdescription_text",
"jade-requirementprofil" => "candidateprofile_text",
"jade-weoffer" => "offer_text",
"jade-overall-values-younger" => "overall_younger",
"jade-overall-values-elder" => "overall_elder",
"jade-overall-values-male" => "overall_male",
"jade-overall-values-female" => "overall_female",
"jade-title-values-younger" => "title_younger",
"jade-title-values-elder" => "title_elder",
"jade-title-values-male" => "title_male",
"jade-title-values-female" => "title_female",
"jade-title-analysis" => "title_analysis",
"jade-employer-values-younger" => "employer_younger",
"jade-employer-values-elder" => "employer_elder",
"jade-employer-values-male" => "employer_male",
"jade-employer-values-female" => "employer_female",
"jade-employer-analysis" => "employer_analysis",
"jade-jobdescription-values-younger" => "jobdescription_younger",
"jade-jobdescription-values-elder" => "jobdescription_elder",
"jade-jobdescription-values-male" => "jobdescription_male",
"jade-jobdescription-values-female" => "jobdescription_female",
"jade-jobdescription-analysis" => "jobdescription_analysis",
"jade-requirementprofil-values-younger" => "candidateprofile_younger",
"jade-requirementprofil-values-elder" => "candidateprofile_elder",
"jade-requirementprofil-values-male" => "candidateprofile_male",
"jade-requirementprofil-values-female" => "candidateprofile_female",
"jade-requirementprofil-analysis" => "candidateprofile_analysis",
"jade-weoffer-values-younger" => "offer_younger",
"jade-weoffer-values-elder" => "offer_elder",
"jade-weoffer-values-male" => "offer_male",
"jade-weoffer-values-female" => "offer_female",
"jade-weoffer-analysis" => "offer_analysis"
);

function csv_field_translate($name) {
    global $jade_csv_header_translation;

    return array_key_exists($name, $jade_csv_header_translation) ? $jade_csv_header_translation[$name] : $name;
}

// Print the Const Arrays as "key=value,..."
function jade_info_print($key) {
    global $jade_sections, $jade_dimensions, $jade_expressions;

    if ($key == 'sections') $data = $jade_sections;
    if ($key == 'dimensions') $data = $jade_dimensions;
    if ($key == 'expressions') $data = $jade_expressions;

    return implode(', ', array_map(
                   function ($v, $k) {
                       return $k.'='.$v;
                   },
                   $data,
                   array_keys($data)
               ));
}

?>
