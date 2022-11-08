<?php

function jade_rest_profile_versions($request) {
    global $wpdb;

    $jobId = $request->get_param('job_id');
    $userId = get_current_user_id();

    if (empty($userId)) return array("error" => "no user ids ... nonce problem?");

    $jobs = $wpdb->get_results( "SELECT id, job_data, timestamp FROM " . JADE_TABLE_JOBS . " WHERE job_id = " . $jobId .
        "  AND user_id = " . $userId ." ORDER BY timestamp DESC");

    $result = array();
    foreach ($jobs as $job) {
        $title = jade_get_job_title($job->id);

        $result[] = array(
            "id" => $job->id,
            "title" => $title,
            "timestamp" => $job->timestamp,
        );
    }
    return $result;
}

add_action( 'rest_api_init', function () {
    //d(wp_create_nonce('wp_rest'));

    register_rest_route( 'jade-tool/v1', '/profile-versions/', array(
        'methods' => 'GET',
        'callback' => 'jade_rest_profile_versions',
        'permission_callback' => function () { return get_current_user_id() !== 0; }
    ));
});


function jade_get_job_title_TEMP($id) {
    global $wpdb;

    $job = $wpdb->get_results( "SELECT id, job_data, analysis_data FROM " . JADE_TABLE_JOBS . " WHERE id = " . $id .
            "  AND user_id = " . get_current_user_id());

    if (empty($job)) return false;
    $job = $job[0];

    $jobData = json_decode($job->job_data, true);

    return $jobData["jade-title"];
}