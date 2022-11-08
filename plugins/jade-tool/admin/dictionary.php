<?php

function jade_page_dictionary() {
    global $wpdb;
    global $jade_sections, $jade_dimensions, $jade_expressions;

    $phpFileUploadErrors = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    );

    // Handling Upload
    $new_file_state = "none";
    $new_file_error = false;

    if ($_FILES && $_FILES["new_dictionary"]) {
        try {
            $wpdb->query('START TRANSACTION');

            // Upload Errors
            if ($_FILES['new_dictionary']['error'] != 0) {
                throw new Exception("Upload Error: " . $phpFileUploadErrors[$_FILES['new_dictionary']['error']]);
            }


            $csvMimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

            // File Type Error
            if (!in_array($_FILES['new_dictionary']['type'], $csvMimes)) {
                throw new Exception("File Type is not CSV!");
            }

            // Processing type
            if ($_POST["processing_type"] == "replace") {
                $result = $wpdb->query("TRUNCATE TABLE " . JADE_TABLE_DICTIONARY);
                if ($result === false) {
                    throw new Exception("Emptying Table / DB Error, please contact the administrator!");
                }
            }

            // Process File
            $fp = fopen($_FILES['new_dictionary']['tmp_name'], 'rb');
            $i = -1;
            while (($line = fgets($fp)) !== false) {
                $i++;
                if ($i <= 0) continue;

                $p = explode(";", $line);
                if (count($p) < 7) throw new Exception("Line #" . ($i + 1) . " is invalid - to less columns");

                //Lfd.Nr.;  Abschnitt;  Wort;   Dimension;  Ausprägung; Wert;   Alternative
                //0;        1;          2;      3;          4;          5;      6

                $section_id = $p[1];
                if (is_numeric($section_id)) {
                    $section_id = $section_id + 0;
                } else {
                    $section_id = array_search($section_id, $jade_sections);

                    if ($section_id === false) {
                        throw new Exception("Line #" . ($i + 1) . " Section is not a number or no text match! (" . $p[1] . ")");
                    }
                }

                $dimension = $p[3];
/*
                $dimension_id = $p[3];
                if (is_numeric($dimension_id)) {
                    $dimension_id = $dimension_id + 0;
                } else {
                    $dimension_id = array_search($dimension_id, $jade_dimensions);
                    if ($dimension_id === false) {
                        throw new Exception("Line #" . ($i + 1) . " Dimension is not a number or no text match! (" . $p[3] . ")");
                    }
                }
*/
                $expression = $p[4];
                $jade_expression_found = false;
                foreach ($jade_expressions as $jade_expression) {
                    if (strpos($expression, $jade_expression) !== FALSE) { // Yoshi version
                            $jade_expression_found = true;
                        }
                }

                // If expression is not found, it's neutral
                if ($jade_expression_found === false) { }

                $value = $p[5];
                $value = str_replace(',', '.', $value);
                if (is_numeric($value)) {
                    $value = $value + 0;
                } else {
                   throw new Exception("Line #" . ($i + 1) . " Value not a number! (" . $value . ")");
                }

                $result = $wpdb->insert(JADE_TABLE_DICTIONARY, array(
                    'section_id' => $section_id,
                    'word'  =>   $p[2],
                    'dimension' => $dimension,
                    //'dimension_id' => $dimension_id,
                    'expression' => $expression,
                    //'expression_id' => $expresion_id,
                    'value' => $value,
                    'alternative' => trim($p[6], " \t\n\r\0\x0B")
                ));

                if ($result === false) {
                    throw new Exception("Line #" . ($i + 1) . " DB Error, please contact the administrator!");
                }
            }

            $new_file_state = "imported";

            $wpdb->query('COMMIT');

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');

            $new_file_state = "error";
            $new_file_error = $e->getMessage();
        }
    }

    ?>
    <h1>JADE Dictionarys</h1>
    <?php
        $infos = array();
        $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM " . JADE_TABLE_DICTIONARY);
        $infos["Entries"] = $rowcount;

        jade_form_table($infos);

        if (current_user_can(JADE_BACKEND_CURRENT_USER_CAN)) {
            $wpnonce = wp_create_nonce('wp_rest');
            echo '<a class="button button-primary" href="/wp-json/jade-tool/v1/dict-export?_wpnonce=' . $wpnonce . '" target="_blank">Export Dictionary as CSV</a>';
        }
    ?>


      <h2>Upload a New Dictionary</h2>
      <?php

      switch ($new_file_state) {
          case "imported":
              if ($_POST["processing_type"] == "replace") echo '<p style="color: darkgreen;"><span class="dashicons dashicons-yes-alt"></span> All entries deleted!</p>';
              echo '<p style="color: darkgreen;"><span class="dashicons dashicons-yes-alt"></span> Import successful!</p>';
              echo "<p>File: " . $_FILES['new_dictionary']['name'] . "</p>";
              echo "<p>" . $i . " Lines added</p>";
              break;

        case "error":
            echo '<p style="color: red;"><span class="dashicons dashicons-warning"></span> ' . $new_file_error . '</p>';

          case "none":
          default:

              ?>
              <form method="post" enctype="multipart/form-data">
                  <table class="form-table" role="presentation">
                  <tbody>
                  <tr class="user-rich-editing-wrap">
                  		<th scope="row">Existing Data</th>
                  		<td>
                                <input type="radio" name="processing_type"
                              <?php if (isset($_POST["processing_type"]) && $_POST["processing_type"]=="replace") echo "checked";?>
                              value="replace"><label for="replace">Replace Dictionary with File</label><br>
                              <input type="radio" name="processing_type"
                              <?php if (isset($_POST["processing_type"]) && $_POST["processing_type"]=="add" || !isset($_POST["processing_type"])) echo "checked";?>
                              value="add"><label for="add">Add File to Dictionary</label>
                  		</td>
                  	</tr>
                      <tr class="user-rich-editing-wrap">
                            <th scope="row">File</th>
                            <td>
                                <input type='file' id='new_dictionary' name='new_dictionary'></input>
                            </td>
                        </tr>
                  </tbody>
                  </table>
                  <?php submit_button('Upload') ?>
              </form>

              <h3>File Description</h3>
            <?php
                jade_form_table(array(
                    "File Type" => "CSV",
                    "First Line" => "will be ignored, because it is assumed it is the header",
                    "Separator" => "; <i>(Semicolon)</i>",
                    "Encoding" => "utf8",
                ), 'small');
            ?>

              <h3>Columns</h3>
              <ol>
                  <li>Id <i>(ignored)</i></li>
                  <li>Section Id: <?php echo jade_info_print('sections'); ?> oder exakter Wortlaut</li>
                  <li>Word:<br>
                <ul style="list-style: disc; padding-left: revert;">
                        <li>allowed: characters & numbers and asterix (<i>*</i>) for word start or ending</li>
                        <li><span style="color: red;" class="dashicons dashicons-warning"></span> Everything else will result in unexpected results!</li>
                        <li>Examples: agil, *agil, agil*, *agil*</li>
                        <li>If the word is found more than one time, tha value is only added once to the sum</li>
                    </ul>                  </li>
                  <li><i>(ignored)</i></li>
                  <li>Expression: Has to have one these words in it: <?php echo implode($jade_expressions, ', '); ?></li>
                  <li>Value: a number with or without decimals</li>
                  <li>Alternative</li>
              </ol>

              <?php
              break;
      }
}
