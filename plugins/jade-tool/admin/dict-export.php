<?php

function jade_dict_export_rest($request) {
    global $wpdb;
    global $jade_sections, $jade_dimensions, $jade_expressions;

    $userId = get_current_user_id();

    if (empty($userId)) return array("error" => "no user id ... once problem? ");

    $fields = array("id", "section_id", "word", "dimension", "expression", "value", "alternative");
    //$fields = array("id", "section_id", "word", "dimension_id", "expression_id", "value", "alternative");

    $rows = $wpdb->get_results( "SELECT " .  implode(", ", $fields) . " FROM " . JADE_TABLE_DICTIONARY . "  ORDER BY id ASC");

    if ($rows === NULL)  return "error / db error";

    $delimiter = ";";

    $f = fopen('php://memory', 'w');

    $idCounter = 1;

    fputcsv($f, $fields, $delimiter);

    foreach ($rows as $row) {
        $data = array();
        foreach ($fields as $field) {
            switch ($field) {
                case 'section_id':
                    $data[] = $jade_sections[$row->$field];
                    break;

                case 'dimension_id':
                    $data[] = $jade_dimensions[$row->$field];
                    break;

                case 'dimension':
                    $data[] = $row->$field;
                    break;

                case 'expression_id':
                    $data[] = $jade_expressions[$row->$field];
                    break;

                case 'expression':
                    $data[] = $row->$field;
                    break;

                case 'id':
                    $data[] = $idCounter;
                    $idCounter++;
                    break;

                default:
                    $data[] = trim($row->$field, " \t\n\r\0\x0B");
                    break;
            }
        }

        fputs($f, implode(';', $data)."\n");
        //fputcsv($f, $data, $delimiter, "");
    }

    $filename = date("Y-m-d H_i") . " dictionary.csv";

    // reset the file pointer to the start of the file
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    // make php send the generated csv lines to the browser
    fpassthru($f);

    // HACK: otherwise there will be a
    // <b>Warning</b>:  Cannot modify header information - headers already sent by ...
    die();

    return null; //$output;
}

add_action( 'rest_api_init', function () {
    //d(wp_create_nonce('wp_rest'));

    register_rest_route( 'jade-tool/v1', '/dict-export/', array(
        'methods' => 'GET',
        'callback' => 'jade_dict_export_rest',
        'permission_callback' => function () { return current_user_can(JADE_BACKEND_CURRENT_USER_CAN); }
    ));
});