<?php

function jade_rest_job($request) {
    global $wpdb;
    global $jade_expressions_fields, $jade_sections_fields;

    $jobId = $request->get_param('id');
    $versionId = $request->get_param('version_id');

    $userId = get_current_user_id();

    if (empty($userId)) return array("error" => "no user id ... once problem?");

    if (!empty($jobId)) {
        $job = $wpdb->get_results( "SELECT job_data, analysis_data FROM " . JADE_TABLE_JOBS . " WHERE job_id = " . $jobId .
            "  AND user_id = " . $userId ." ORDER BY timestamp DESC LIMIT 0, 1");
    }
    if (!empty($versionId)) {
        $job = $wpdb->get_results( "SELECT job_data, analysis_data FROM " . JADE_TABLE_JOBS . " WHERE id = " . $versionId .
            "  AND user_id = " . $userId ." ORDER BY timestamp DESC LIMIT 0, 1");
    }

    if ($job === NULL) return array("error" => "db error");
    if (count($job) === 0) return array("error" => "no db result");
    if (count($job) >= 1) $job = $job[0];

    return array(
        "job_data" => json_decode($job->job_data, true),
        "analysis_data" => json_decode($job->analysis_data, true),
        "expression_fields" => $jade_expressions_fields,
        "section_fields" => $jade_sections_fields
    );
}

add_action( 'rest_api_init', function () {
    //d(wp_create_nonce('wp_rest'));

    register_rest_route( 'jade-tool/v1', '/job/', array(
        'methods' => 'GET',
        'callback' => 'jade_rest_job',
        'permission_callback' => function () { return get_current_user_id() !== 0; }
    ));
});