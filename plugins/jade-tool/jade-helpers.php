<?php

function d($val, $die = false) {
    if (get_current_user_id() != 5) return; 

    $type = gettype($val);

    echo '<pre>';
    switch ($type) {
        case 'object':
        case 'array':
            print_r($val);
            break;

        case 'boolean':
            $val = ($val === true ? "TRUE" : "FALSE");
        default:
            echo "[" . $type . "]\n" . $val;
    }

    echo  '</pre>';
    if ($die) die("Halt on purpose");
}

// Function to check string starting with given substring
function startsWith ($string, $startString) {
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function jade_form_table($data, $size = "standard") {
    switch ($size) {
        case 'small':
            echo '<table class="" role="presentation"><tbody>';
            break;

        default:
            echo '<table class="form-table" role="presentation"><tbody>';
            break;
    }

    foreach ($data as $name => $value) {
    ?>
        <tr class="user-rich-editing-wrap">
        <th scope="row"><?php echo $name; ?></th>
        <td><?php echo $value; ?></td>
        </tr>
    <?php
    }

    echo '</tbody></table>';
}
