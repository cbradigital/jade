<?php

function jade_forms_yearpicker_func( $atts ) {
	$attr = shortcode_atts( array(
		'name' => '0',
		'class' => ''
	), $atts );

	$name = $attr['name'];
	$class = $attr['class']; 

    $years = range(strftime("%Y", time()), 2040);

    // ---------------------------------------------------------------------------> OB START
    ob_start();
    ?>

    <select name="<?php echo $name; ?>" class="<?php echo $class; ?>"> 
      <?php foreach($years as $year) : ?>
        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
      <?php endforeach; ?>
    </select>

    <?php
    // ---------------------------------------------------------------------------> OB GET CLEAN
    $output = ob_get_clean();


	return $output;
}

add_shortcode( 'jade-forms-yearpicker', 'jade_forms_yearpicker_func' );


function jade_forms_general_func( $atts ) {
    $KEYWORD_DELETED = "XXDELETEDXX";
	$attr = shortcode_atts( array(
		'name' => '0',
		'class' => '',
		'type' => '',
		'placeholder' => 'Bitte wählen'
	), $atts );

	$name = $attr['name'];
	$type = $attr['type'];
	$class = $attr['class'];
	$placeholder = $attr['placeholder'];

    $options = array();
    $i = 1;

    switch ($type) {
        case 'business':
            $options = array(
                "Land- und Forstwirtschaft / Fischerei",
                "Bergbau und Gewinnung von Steinen und Erden",
                "Verarbeitendes Gewerbe / Herstellung von Waren",
                "Energieversorgung",
                "Wasserversorgung / Abwasser- und Abfallentsorgung / Beseitigung von Umweltverschmutzungen",
                "Bau",
                "Handel",
                "Instandhaltung und Reparatur von Kraftfahrzeugen und Gebrauchsgütern",
                "Verkehr und Lagerei",
                "Gastgewerbe / Beherbergung und Gastronomie",
                "Information und Kommunikation",
                "Erbringung von Finanz- und Versicherungsdienstleistungen",
                "Grundstücks- und Wohnungswesen",
                "Erbringung von freiberuflichen, wissenschaftlichen und technischen Dienstleistungen",
                "Erbringung von sonstigen wirtschaftlichen Dienstleistungen",
                "öffentliche Verwaltung, Verteidigung, Sozialversicherung",
                "Erziehung und Unterricht",
                "Gesundheits- und Sozialwesen",
                "Kunst, Unterhaltung und Erholung",
                "Erbringung von sonstigen Dienstleistungen",
                "SONSTIGES: Freitexteingabe");
                break;

        case 'companysize':
            $options = array(
                "Kleinstunternehmen (bis 9 MA)",
                "Kleinunternehmen (10 - 49 MA)",
                "Mittleres Unternehmen (50 - 249 MA)",
                "Großunternehmen (ab 250 MA)");
            break;

        case 'employmentform':
            $options = array(
                "Arbeiter/in",
                "Angestellte/r",
                 "freier Dienstvertrag",
                 "Werkvertrag",
                 "Praktikum/Volontariat"
            );
            break;

        case 'professionalfield':
            $options = array(
                "Führungskräfte",
                "akademische Berufe",
                "TechnikerInnen und gleichrangige nichttechnische Berufe",
                "Bürokräfte und verwandte Berufe",
                "Dienstleistungsberufe und VerkäuferInnen",
                "Fachkräfte in der Landwirtschaft und Fischerei",
                "Handwerks- und verwandte Berufe",
                "Anlagen- und Maschinenbediener/innen und Montageberufe",
                "Hilfsarbeitskräfte",
                "Angehörige der regulären Streitkräfte"
            );
            break;

        case 'qualificationlevel':
            $i = 2;
            $options = array(
            "Pflichtschulabschluss",
            "Lehre, berufsbildende mittlere Schule",
            "AHS-Matura",
            "BHS-/HTL-Matura, universitärer Lehrgang, Meisterschule",
            "Bachelor",
            "Master, Diplomstudium",
            "Doktorat, PhD");
            break;

        case 'corporate-division':
            $i = 2;
            $options = array(
            "Betriebs-/Unternehmensleitung",
            "Beschaffung",
            "Produktion",
            "Marketing",
            "Vertrieb",
            "Kundendienst",
            "Finanzabteilung",
            "Personalwesen",
            "Forschung und Entwicklung",
            "Logistik",
            "Rechnungswesen, Controlling",
            "IT",
            "Verwaltung",
            $KEYWORD_DELETED . "Stabstelle (unter Sonstiges angeben)",
            "Sonstiges");
            break;
        /*
        default:
            $options = array(
                "Valide Werte für type sind",
                "qualificationlevel",
                "business",
                "companysize",
                "employmentform",
                "professionalfield",
                "qualificationlevel"
            );
*/
       } 


    // ---------------------------------------------------------------------------> OB START
    ob_start();
    ?>
    <select name="<?php echo $name; ?>" class="<?php echo $class; ?>">
      <option value="-1"><?php echo $placeholder; ?></option>
      <?php foreach($options as $option) {
            if (substr($option, 0, strlen($KEYWORD_DELETED)) === $KEYWORD_DELETED) {
                $i++;
                continue;
            }
      ?>
        <option value="<?php echo $i; ?>"><?php echo $option; ?></option>
      <?php
            $i++;
        }
      ?>
    </select>

    <?php
    // ---------------------------------------------------------------------------> OB GET CLEAN
    $output = ob_get_clean();

	return $output;
}

add_shortcode( 'jade-forms', 'jade_forms_general_func' );
