<?php

function jade_job_export_rest($request) {
    $outputInHTML = false;

    global $wpdb;
    global $jade_expressions_fields, $jade_sections_fields;

    global $jade_export_fields_stats;

    $userId = get_current_user_id();

    if (empty($userId)) return array("error" => "no user id ... once problem?");

    $fields = array("id", "job_id", "timestamp", "customer_data", "job_data", "analysis_data");

    $jobs = $wpdb->get_results( "SELECT " .  implode(", ", $fields) . " FROM " . JADE_TABLE_JOBS . "  ORDER BY job_id ASC, timestamp ASC");

    if ($jobs === NULL)  return "error / db error";

    //  0 --> 2  / Standardfelder: id, job_id, timestamp
    $csvFields = array("id", "job_id", "timestamp");

    //  3 --> 19 / Statistik $jade_export_fields_stats
    foreach ($jade_export_fields_stats as $f) $csvFields[] = $f;

    // 20 --> 25 / Jade Input $jade_sections_fields aus job_data
    foreach ($jade_sections_fields as $f) $csvFields[] = $f;

    // 26 --> 26 / Jade Output overall
    foreach ($jade_expressions_fields as $exprf) {
        $csvFields[] = "jade-overall-values-" . $exprf;
    }

    // 27 --> 36 / $jade_export_fields_jade aus analysis_data mit expr-values (dict) und analysis (list)
    foreach ($jade_sections_fields as $f) {
        foreach ($jade_expressions_fields as $exprf) {
            $csvFields[] = $f . "-values-" . $exprf;
        }
        $csvFields[] = $f . "-analysis";
    }

    // Translate the headers
    foreach ($csvFields as $i => $value) {
        $csvFields[$i] = csv_field_translate($value);
    }

    // d($csvFields);

    $filestream = fopen('php://memory', 'w');
    $delimiter = ";";

    if ($outputInHTML) {
        header('Content-Type: text/html');
        echo '<!doctype html>';
        echo '<html lang="en"><head><meta charset="utf-8"></head><body>';
        echo '<table style="border-collapse: collapse; border: solid 1px grey;"><tr><th>' . implode("</th><th>", $csvFields) . "</th></tr>";
    } else {
        fputcsv($filestream, $csvFields, $delimiter);
    }

    $last_stats_data = false;

    foreach ($jobs as $job) {

        $data = array();
        //  0 --> 2  / Standardfelder: id, job_id, timestamp
        $data[] = $job->id;
        $data[] = $job->job_id;
        $data[] = $job->timestamp;

        //  3 --> 19 / Statistik $jade_export_fields_stats
        $stats_data = json_decode($job->customer_data);
        if ($stats_data == null) {
            $stats_data = $last_stats_data == false ? array() : $last_stats_data;
        }
        $last_stats_data = $stats_data;

        foreach ($jade_export_fields_stats as $f) {
            $v = array_key_exists($f, $stats_data) ? $stats_data->$f : "";
            if (gettype($v) == "array") $v = json_encode($v);

            if ($f == "stats-mycompany") $v = ($v == "yes" ? 1 : 0);
            if (strpos($f, "stats-medium") !== false && $f != "stats-medium-extra") $v = ($v == "1" ? 1 : 0);

            $data[] = $v;
        }

        // 20 --> 25 / Jade Input $jade_sections_fields aus job_data
        $job_data = json_decode($job->job_data);
        if ($job_data == null) $job_data = array();
        foreach ($jade_sections_fields as $f) {
            $value = "";
            if (array_key_exists($f, $job_data)) {
                // Strip cr
                $value = str_replace (array("\r\n", "\n", "\r"), ' ', $job_data->$f);
            }
            $data[] =  $value;
        }
        // Jade Analysis
        $analysis_data = json_decode($job->analysis_data);
        if ($analysis_data == null) $analysis_data = array();

        // 26 --> 26 / Jade Output overall
        $overall = array_key_exists("overall", $analysis_data) ? $analysis_data->overall : array();
        foreach ($jade_expressions_fields as $exprf) {
            $data[] = array_key_exists($exprf, $overall) ? $overall->$exprf : "";
        }

        // 27 --> 36 / $jade_export_fields_jade aus analysis_data mit expr-values (dict) und analysis (list)
        foreach ($jade_sections_fields as $f) {
            $analysis_data_section = array_key_exists($f, $analysis_data) ? $analysis_data->$f : array();
            //d($analysis_data_section);
            $key = "expr-values";
            $exprValues = array_key_exists($key, $analysis_data_section) ? $analysis_data_section->$key : array();
            foreach ($jade_expressions_fields as $exprf) {
                $data[] = array_key_exists($exprf, $exprValues) ? $exprValues->$exprf : "";
            }

            $data[] = array_key_exists("analysis", $analysis_data_section) ? json_encode($analysis_data_section->analysis) : "";
        }

        // Convert Strings
        foreach ($data as $i => $value) {
            if (!empty($value) && !is_numeric($value)) {
                $data[$i] = '"' . str_replace('"', '""', $value) . '"';
            }
        }

        // d($data);
      if ($outputInHTML) {
        echo '<tr style="border: 1px solid black;"><td style="border: solid 1px grey;">' . implode('</td><td style="border: solid 1px grey;">', $data) . "</td></tr>";
      } else {
        fputs($filestream, implode(';', $data)."\n");
      }

        //print_r($data);
    }

    if ($outputInHTML) {
        echo "</table>";
        d("stopped", true);
    }

    //d("true", true);

    $filename = date("Y-m-d H_i") . " jobs.csv";

    // reset the file pointer to the start of the file
    fseek($filestream, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    // make php send the generated csv lines to the browser
    fpassthru($filestream);

    // HACK: otherwise there will be a
    // <b>Warning</b>:  Cannot modify header information - headers already sent by ...
    die();

    return null; //$output;
}

add_action( 'rest_api_init', function () {
    //d(wp_create_nonce('wp_rest'));

    register_rest_route( 'jade-tool/v1', '/job-export/', array(
        'methods' => 'GET',
        'callback' => 'jade_job_export_rest',
        'permission_callback' => function () { return current_user_can(JADE_BACKEND_CURRENT_USER_CAN); }
    ));
});