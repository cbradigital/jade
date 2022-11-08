<?php

$jadeJobId = (!empty($_GET) && !empty($_GET["job"])) ? $_GET["job"] : false;
$jadeVersionId = (!empty($_GET) && !empty($_GET["version_id"])) ? $_GET["version_id"] : false;
$printPDF = (!empty($_GET) && !empty($_GET["print"])) ? strtolower($_GET["print"]) == "pdf" : false;

function jade_analysis_wrap_content_func($content) {
    global $jadeJobId, $jadeVersionId, $printPDF, $wpdb;

    if (!is_page(PAGEID_JADE_ANALYSIS)) return $content;

    if (empty($jadeJobId) && empty($jadeVersionId)) {
        return '<p style="color: red;"><span class="dashicons dashicons-warning"></span> Job Id Missing!</p>';
    }

    // User Id
    $output = "<!-- AUTOMATED FORM WRAP though JADE PLUGIN -->";
    $output.= '<form id="jade-form" method="POST">';
    $output.= '<input type="hidden" id="nonce" name="nonce" value="' . wp_create_nonce('wp_rest') .'">';

    // Print PDF
    $output.= '<input type="hidden" id="printpdf" name="printpdf" value="' . ($printPDF ? 1 : 0) . '">';

    if (!empty($jadeJobId)) {
        $jobUserId = intval($wpdb->get_var("SELECT user_id FROM " . JADE_TABLE_JOBS . " where job_id=" .$jadeJobId));
        if ($jobUserId != 5 && $jobUserId !== get_current_user_id())  {
            return '<p style="color: red;"><span class="dashicons dashicons-warning"></span> Dies ist nicht eine Analyse des eingeloggten Benutzers!</p>';
        }

        $output.= '<input type="hidden" id="job_id" name="job_id" value="' . $jadeJobId .'">';
    }

    if (!empty($jadeVersionId)) {
        $versionUserId = intval($wpdb->get_var("SELECT user_id FROM " . JADE_TABLE_JOBS . " where id=" .$jadeVersionId));

        if ($versionUserId != 5 && $versionUserId !== get_current_user_id())  {
            return '<p style="color: red;"><span class="dashicons dashicons-warning"></span> Dies ist nicht eine Analyse des eingeloggten Benutzers!</p>';
        }

        $output.= '<input type="hidden" id="version_id" name="version_id" value="' . $jadeVersionId .'">';
    }

    $output.= $content;
    $output.= '</form>';

    return $output;
}

add_action('the_content','jade_analysis_wrap_content_func');



function jade_wp_analysis_func() {
    global $jadeJobId, $wpdb;

    if (!is_page(PAGEID_JADE_ANALYSIS)) return;

    // Job Id Exists
    if (empty($jadeJobId) || $jadeJobId === false) return;

    // Check if Job Id exists in DB and it  is the right user
    $jobUserId = intval($wpdb->get_var("SELECT user_id FROM " . JADE_TABLE_JOBS . " where job_id=" .$jadeJobId));
    if ($jobUserId !== get_current_user_id()) return;

    // check for new POST Request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $job_data = array();

        foreach ($_POST as $key => $value) {
            if (startsWith($key, 'jade-')) $job_data[$key] = $value;
        }

        $insert = array(
            'user_id' => get_current_user_id(),
            'job_id'  =>  $jadeJobId,
            'customer_data' => "",
            'job_data' => json_encode($job_data),
            'analysis_data' => "",
        );

        $result = $wpdb->insert(JADE_TABLE_JOBS, $insert);

        if ($result === false) {
            throw new Exception("Insert New JobVariant DB Error, please contact the administrator!");
        }
    }

    // Check for new Update
    checkAndUpdateJob($jadeJobId);

    return;
}

add_action('wp','jade_wp_analysis_func');

function findWords($text, $word) {
    $asterixStart = $word[0] == '*';
    $asterixEnd = $word[strlen($word)-1] == '*';

    $word = substr($word, $asterixStart ? 1 : 0, $asterixEnd ? -1 : strlen($word));

    // for the regex pattern
    $text = " " . $text . " ";

    $pattern = '/\s' . ($asterixStart ? '[^\n \.,]*' : '') . $word . ($asterixEnd ? '[^\n \.,]*' : '') .  '[,\s\.!?]/i';

    $matches = array();
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    //d($matches);
    if ($matches == false || count($matches) <= 0) return array();
    $matches = $matches[0];
    //d(count($matches) . " matches found");

    for ($i = 0; $i < count($matches); $i++) {
        $word = $matches[$i][0];
        $offset = $matches[$i][1];
        d($matches[$i]);

        if (substr($word, -1) == ',') $word = substr($word, 0, -1);
        $word = str_replace(".", "", $word);
        //$word = str_replace(",", "", $word)

        $matches[$i] = array("word" => $word, "offset" => $offset);
    }

    return $matches;
}

// Check if last job entry has analysis and calc if necessary
function checkAndUpdateJob($jobId) {
    global $jadeJobId, $wpdb;
    global $jade_sections, $jade_sections_fields, $jade_expressions, $jade_expressions_fields;

    // Check if there needs to be an update
    $job = $wpdb->get_results( "SELECT id, job_data, analysis_data FROM " . JADE_TABLE_JOBS . " WHERE job_id = " . $jobId .
            "  AND user_id = " . get_current_user_id() ." ORDER BY timestamp DESC LIMIT 0, 1");

    if (empty($job)) return;
    $job = $job[0];

    if (!empty($job->analysis_data)) return;

    $jobData = json_decode($job->job_data, true);

    $debugthis = false;

    $sections = array();

    // -------------------------------------------------------------> EVERY SECTION - START
    // Sections: pro $jade_sections_fields
    foreach ($jade_sections_fields as $sectionId => $sectionField) {
        $section = array(
            "id" => $sectionId,
            "field" => $sectionField,
            "expr-values" => array()
        );

        // create expr-values
        foreach ($jade_expressions_fields as $exprId => $exprField) {
            $section["expr-values"][$exprField] = 0;
        }

        $text = $jobData[$sectionField];
        if ($debugthis) d("<b>Section:</b> " . $sectionField);
        if ($debugthis) d($text);

        $analysis = array();

        // -------------------------------------------------------------> EVERY WORD - START
        $results = $wpdb->get_results( "SELECT word, value, expression, alternative FROM " . JADE_TABLE_DICTIONARY
                . " WHERE section_id = " . $sectionId . " ORDER BY id");

        foreach ($results as $result) {
            $matches = findWords($text, $result->word);
            if (count($matches) <= 0) continue;

            if ($debugthis) d($result);

            for ($i = 0; $i < count($matches); $i++) {
                $match = $matches[$i];
                $matchWord = $match["word"];
                $matchOffset = $match["offset"];

                $analysis[] = array(
                    "word" => trim($matchWord),
                    "offset" => $matchOffset,
                    "expression" => trim($result->expression),
                    "alternative" => addslashes(trim($result->alternative))
                );

                // Add value to the right expression
                $expressionId = -1;
                foreach ($jade_expressions_fields as $exprId => $exprField) {
                    $exprFieldGerman = $jade_expressions[$exprId];
                    if (strpos($result->expression, $exprFieldGerman) !== false) {
                          $section["expr-values"][$exprField] += $result->value;
                            if ($debugthis) d("Added " . $result->value . " to " . $exprField);
                    }
                }
            }
        }

        if ($debugthis) d($analysis);
        // Kann ausgebaut werden auf zweistufiges Sortieren, falls notwendig
        array_multisort(array_column($analysis, 'offset'), SORT_ASC, $analysis);
        if ($debugthis) d($analysis);

        // set the analysis for all expressions
        $section["analysis"] = $analysis;

        $sections[$sectionField] = $section;
        if ($debugthis) d($section["expr-values"]);
        if ($debugthis) d($analysis);

    }
    // -------------------------------------------------------------> EVERY SECTION - END

    // 1x Overall

    $overallExprValue = array();

    foreach ($jade_expressions_fields as $exprId => $exprField) {
        $overallExprValue[$exprField] = 0;

        foreach ($sections as $sectionField => $section) {
            $overallExprValue[$exprField] += $section["expr-values"][$exprField];
        }
    }

    $sections['overall'] = $overallExprValue;

    if (version_compare(phpversion(), '7.1', '>=')) {
        ini_set( 'precision', 10);
        ini_set( 'serialize_precision', 10);
    }

    $data = array('analysis_data' => json_encode($sections));
    $where = array('id' => $job->id);
    $success = $wpdb->update(JADE_TABLE_JOBS, $data, $where);

    if ($success === false) throw new Exception('Analysis and Jobs / DB Update Error / Contact Administrator');
}
