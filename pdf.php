<?php

// Include the main TCPDF library
require_once('tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class BLUEPRINT extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		$image_file = K_PATH_IMAGES . 'logo.png';

		// Set font
		$this->SetFont('helvetica', 'B', 16);
		
		// Title
		$this->Cell(0, 10, $_POST['org'] . " MEMBERSHIP REPORT", 0, false, 'L', 0, '', 0, false, 'C', 'C');

		// Set image
		$this->Image($image_file, 0, 5, 0, '', 'PNG', '', 'T', false, 300, 'R', false, false, 0, false, false, false);
	}
}

$genderColors = array(
	'Male' => array(
			0 => 108,
			1 => 160,
			2 => 220,		
		),
	'Female' => array(
			0 => 255,
			1 => 182,
			2 => 193,		
		),
	'Other' => array(
			0 => 128,
			1 => 128,
			2 => 128,		
		),
	);

/*
	---------------------------------------------------------------------------
		Set up PDF
	---------------------------------------------------------------------------
*/

$_POST['org'] = 'NCGV';
$_POST['desc'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ut gravida libero. Nullam auctor molestie lacinia. Vivamus placerat ornare tellus. Fusce vel commodo justo. Nunc mollis, ante vel suscipit semper, eros purus tristique nisi, non tristique orci ipsum bibendum nisl. Sed sit amet accumsan mi, nec sodales neque. Aliquam et finibus ipsum.';

// create new PDF document
$pdf = new BLUEPRINT(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set title
$today = date("F j, Y");  
$pdfTitle = "<h2 class='uppercase'>" . $_POST['org'] . " MEMBERSHIP REPORT!</h2>";

// set document information
$pdf->SetCreator('Richir Outreach');
$pdf->SetAuthor('Richir Outreach');
$pdf->SetTitle($pdfTitle);

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 031', PDF_HEADER_STRING);
//$pdf->SetHeaderData('logo.png', null, $pdfTitle, null, array(65,65,180));
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 14));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(12);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

/*
	---------------------------------------------------------------------------
		Load Data
	---------------------------------------------------------------------------
*/

// Load the CSV
//$data = read_csv( $_FILES['csv']['tmp_name'] );
$data = read_csv( 'blueprint_data.tsv' );

//pre_print_r($data);


// Gender Data
$gender = array();

// Race Data
$race = array();

// Age Data
$age = array();

// County Data
$county = array();

// the number of rows
// Jerimee added this and it might ruin everything
$rowcount = count($data);

// Loop through our data
for($i = 1; $i < $rowcount - 1; $i++) {
	// Convert M and F to 'Male' and 'Female'
	if( $data[$i][2] == 'M' || $data[$i][2] == 'm' ) {
		$genderName = 'Male';
	} else if ( $data[$i][2] == 'F' || $data[$i][2] == 'f' ) {
		$genderName = 'Female';
	} else {
		$genderName = 'Other';
	}

	if(array_key_exists( $genderName, $gender)) {
		$gender[ $genderName ] += 1;
	} else {
		$gender[ $genderName ] = 1;
	}

	// Race Chart
	if(array_key_exists($data[$i][4], $race)) {
		$race[$data[$i][4]] += 1;
	} else {
		$race[$data[$i][4]] = 1;
	}	


	// Age Chart
	if($data[$i][3] < 18) {
		$tempAge = '< 18';
	} else if ($data[$i][3] < 30) {
		$tempAge = '18-29';
	} else if ($data[$i][3] < 50) {
		$tempAge = '30-50';
	} else {
		$tempAge = '51+';
	}

	// Race Chart
	if(array_key_exists($tempAge, $age)) {
		$age[$tempAge] += 1;
	} else {
		$age[$tempAge] = 1;
	}	

	// Counties Bar
	if(array_key_exists($data[$i][5], $county)) {
		$county[$data[$i][5]] += 1;
	} else {
		$county[$data[$i][5]] = 1;
	}	
}

// Sort ascending to fix issues with empty 
asort( $gender );
asort( $race );
asort( $age );
arsort( $county );

//pre_print_r($county);

/*
	---------------------------------------------------------------------------
		Make the PDF
	---------------------------------------------------------------------------
*/

// Add a page
$pdf->AddPage();

// Display the description text
$pdf->MultiCell(0, 0, $_POST['desc'], '', 'L');

// Gender Pie Chart
pieChart( $pdf, "Gender", 10, 80, 20, $gender, $genderColors );

// Race Pie Chart
pieChart( $pdf, "Race", 105, 80, 20, $race );

// Age Pie Chart
pieChart( $pdf, "Age", 10, 130, 20, $age );

//function barChart( $pdf, $title, $xpos, $ypos, $width, $height, $array, $colors = null ) {
barChart( $pdf, "County", 10, 200, 10, 70, $county, 10 );

//number of records (count)
displayCount( $pdf, $rowcount );

/*
	---------------------------------------------------------------------------
		Output the PDF
	---------------------------------------------------------------------------
*/

//$pdf->Output('output.pdf', 'D');
$pdf->Output('output.pdf', 'I');


/*
	---------------------------------------------------------------------------
		Helper Functionxs
	---------------------------------------------------------------------------
*/

function read_csv($file, $delimiter = "\t") {
	$fh = fopen($file, 'r');

	while(!feof($fh)) {
		$csv[] = fgetcsv($fh, 0, $delimiter);
	}

	fclose($fh);

	return $csv;
}

function displayCount( $pdf, $rowcount ) {
	// WARNING JERIMEE DID THIS
	// ***********************************
	// Set our font
	$pdf->SetFont('helvetica', 'I', 8);
	// Set the text color 
	$pdf->SetTextColor(0, 0, 0);
	// Set x
	$xShowCount = 14;
    // Set y
    $yShowCount = 16;
    // subtract one for header
    $rowcount = $rowcount - 1;
    // msg
    $msgShowCount = "" . $rowcount . " records total - ignoring first row as header.";
	$pdf->Text( $xShowCount, $yShowCount, $msgShowCount );
	// ***********************************
}

function pre_print_r($array) {
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function getPercent( $index, $array ) {
	$total = 0;

	foreach( $array as $row ) {
		$total += $row;
	}

	return $array[$index] / $total;
}

function pieChart( $pdf, $title, $xpos, $ypos, $radius, $array, $colors = null, $legend = false ) {
	// Set color
	$pdf->SetTextColor( 0, 0, 0 );

	// Set font
	$pdf->SetFont('helvetica', 'B', 24);

	// Set our Title
	if( $legend ) {
		$pdf->Text( $xpos + ($radius * 1.5), $ypos, $title);
	} else {
		$pdf->Text( $xpos, $ypos, $title);
	}

	// Set font for legend
	$pdf->SetFont('helvetica', 'B', 14);

	// Offset for legend
	$yOffset = $ypos + 20;

	// Set the X,Y center and radius of our pie chart
	$ypos = $ypos + $radius;

	// Current Angle
	$angle = 0;

	// Cache the last element in the gender array
	end($array);
	$arrayEnd = key($array);

	// Go through array
	foreach( $array as $key => $row ) {
		// Calculate how big of an arc this element is
		$newAngle = 360 * getPercent( $key, $array );

		if( $colors == null ) {
			$red = mt_rand(0, 255);
			$green = mt_rand(0, 255);
			$blue = mt_rand(0, 255);			
		} else {
			$red = $colors[$key][0];
			$green = $colors[$key][1];
			$blue = $colors[$key][2];
		}

		// Set our fill color
		$pdf->SetFillColor( $red, $green, $blue );

		// If this is the last thing in the array, then it must go to the last part of the array
		if( $key == $arrayEnd ) {
			$pdf->PieSector($xpos + $radius, $ypos + $radius, $radius, $angle, 360, 'FD', false, 0, 2);
		} else {
			$pdf->PieSector($xpos + $radius, $ypos + $radius, $radius, $angle, $angle + $newAngle, 'FD', false, 0, 2);
		}

		// Set our new starting point
		$angle += $newAngle;

		// Set the text color
		$pdf->SetTextColor( $red, $green, $blue );
	
		if( $legend ) {
			// Write Labels
			$pdf->Text( $xpos + ( 2 * $radius ) + 5, $yOffset, $key . ' (' . intval( 100 * getPercent( $key, $array) ) . '%)' );
		}

		$yOffset += 7;
	}
}

function barChart( $pdf, $title, $xpos, $ypos, $width, $height, $array, $maxBars, $colors = null ) {
	// Set color
	$pdf->SetTextColor( 0, 0, 0 );

	// Set font
	$pdf->SetFont('helvetica', 'B', 24);

	// Set our Title
	$pdf->Text( $xpos + ( $width * 3 ), $ypos, $title);

	// Set an offset so our bars grow to the right
	$xOffset = $xpos;

	$yOffset = $ypos;

	$ypos += 13;

	$counter = 0;

	// Go through array
	foreach( $array as $key => $row ) {
		// Get what height this bar should be
		$barHeight = getBarHeight( $key, $array ) * $height;

		// Determine colors randomy if array not passed
		if( $colors == null ) {
			$red = mt_rand(0, 255);
			$green = mt_rand(0, 255);
			$blue = mt_rand(0, 255);			
		} else {
			$red = $colors[$key][0];
			$green = $colors[$key][1];
			$blue = $colors[$key][2];
		}

		// Set our border style (width = 0 to remove border)
		$style = array('width' => 1, 'color' => adjustColorLightenDarken( array( 'r' => $red, 'g' => $green, 'b' => $blue ), 10 ) );
	
		if ($counter < $maxBars ) {
			// Draw our box
			$pdf->rect( $xOffset, $ypos - $barHeight + $height, $width, $height - ($height - $barHeight), 'DF', array( 'all' => $style ),  array( $red, $green, $blue ) );		

			// Offset for the next bar
			$xOffset += $width + 2;

			// Set our font
			$pdf->SetFont('helvetica', 'B', 12);

			// Set the text color
			$pdf->SetTextColor( $red, $green, $blue );
		
			if( $yOffset < ($ypos + 40) ) {
				// Write Labels
				$pdf->Text( $xpos + 120, $yOffset + 10, $key . ' (' . $row . ')' );

				$yOffset += 5;
			}
		}

		$counter++;
	}

}

function getBarHeight( $index, $array ) {
	$max = 0;

	foreach( $array as $row ) {
		if( $row > $max ) {
			$max = $row;
		}
	}

	return $array[$index] / $max;	
}

/**
* See: http://jaspreetchahal.org/how-to-lighten-or-darken-hex-or-rgb-color-in-php-and-javascript/
* @param $color_code
* @param int $percentage_adjuster
* @return array|string
* @author Jaspreet Chahal
*/
function adjustColorLightenDarken($color_code,$percentage_adjuster = 0) {
    $percentage_adjuster = round($percentage_adjuster/100,2);
    if(is_array($color_code)) {
        $r = $color_code["r"] - (round($color_code["r"])*$percentage_adjuster);
        $g = $color_code["g"] - (round($color_code["g"])*$percentage_adjuster);
        $b = $color_code["b"] - (round($color_code["b"])*$percentage_adjuster);
 
        return array("r"=> round(max(0,min(255,$r))),
            "g"=> round(max(0,min(255,$g))),
            "b"=> round(max(0,min(255,$b))));
    }
    else if(preg_match("/#/",$color_code)) {
        $hex = str_replace("#","",$color_code);
        $r = (strlen($hex) == 3)? hexdec(substr($hex,0,1).substr($hex,0,1)):hexdec(substr($hex,0,2));
        $g = (strlen($hex) == 3)? hexdec(substr($hex,1,1).substr($hex,1,1)):hexdec(substr($hex,2,2));
        $b = (strlen($hex) == 3)? hexdec(substr($hex,2,1).substr($hex,2,1)):hexdec(substr($hex,4,2));
        $r = round($r - ($r*$percentage_adjuster));
        $g = round($g - ($g*$percentage_adjuster));
        $b = round($b - ($b*$percentage_adjuster));
 
        return "#".str_pad(dechex( max(0,min(255,$r)) ),2,"0",STR_PAD_LEFT)
            .str_pad(dechex( max(0,min(255,$g)) ),2,"0",STR_PAD_LEFT)
            .str_pad(dechex( max(0,min(255,$b)) ),2,"0",STR_PAD_LEFT);
 
    }
}

?>