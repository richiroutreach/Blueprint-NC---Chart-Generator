<?php

// Include the main TCPDF library
require_once( 'tcpdf/tcpdf.php' );

// Helper functions
require_once( 'functions.inc' );

// Colors
require_once( 'colors.inc' );

// Generate our colors
$colors = generateColors();

/*
	---------------------------------------------------------------------------
		Variables used in report
	---------------------------------------------------------------------------
*/

//$_POST['org'] = 'NCGV';
//$_POST['desc'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ut gravida libero. Nullam auctor molestie lacinia. Vivamus placerat ornare tellus. Fusce vel commodo justo. Nunc mollis, ante vel suscipit semper, eros purus tristique nisi, non tristique orci ipsum bibendum nisl. Sed sit amet accumsan mi, nec sodales neque. Aliquam et finibus ipsum.';

$countyText = "";
$genderText = "Statewide: Women make up 54% of North Carolina voters";
$ideologyText = "A lower score indicates someone is more likely conservative and a higher score indicates someone is more likely progressive.";
$ageText = "Statewide: Almost 19% of North Carolina voters are 18-29 years old.";
$raceText = "Statewide: 71.89% of North Carolina voters are identified as white, 23.55% Black, and 2.26% Hispanic.";
$propensityText = "A lower score indicates someone is less likely to vote in the current year election, the higher the score the more likely to vote.";



/*
	---------------------------------------------------------------------------
		Load Data
	---------------------------------------------------------------------------
*/

// Load the CSV
$data = read_csv( $_FILES['csv']['tmp_name'] );
//$data = read_csv( 'lh_sample.tsv' );

// Gender Data
$gender = array();

// Race Data
$race = array();

// Age Data
$age = array();

// County Data
$county = array();

// Propensity to vote
$propensity = array();

// Set up the propensity array to have the columns in the order we desire
$propensity['< 20%'] = 0;
$propensity['20% - 39%'] = 0;
$propensity['40% - 59%'] = 0;
$propensity['60% - 79%'] = 0;
$propensity['> 80%'] = 0;

// Ideal
$ideal = array();

// Set up the ideaology array to have the columns in the order we desire
$ideal['< 20%'] = 0;
$ideal['20% - 39%'] = 0;
$ideal['40% - 59%'] = 0;
$ideal['60% - 79%'] = 0;
$ideal['> 80%'] = 0;

// Loop through our data
for($i = 1; $i < count($data) - 1; $i++) {
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
	if($data[$i][3] < 29) {
		$tempProp = '18-29';
	} else if ($data[$i][3] < 51) {
		$tempProp = '30-50';
	} else if ($data[$i][3] < 66) {
		$tempProp = '51-65';
	} else {
		$tempProp = '66+';
	}

	if(array_key_exists($tempProp, $age)) {
		$age[$tempProp] += 1;
	} else {
		$age[$tempProp] = 1;
	}	

	// Counties Bar
	if(array_key_exists($data[$i][5], $county)) {
		$county[$data[$i][5]] += 1;
	} else {
		$county[$data[$i][5]] = 1;
	}

	// Ideology Score
	if($data[$i][7] < 20) {
		$ideal['< 20%']++;
	} else if ($data[$i][7] < 40) {
		$ideal['20% - 39%']++;
	} else if ($data[$i][7] < 60) {
		$ideal['40% - 59%']++;
	} else if ($data[$i][7] < 80) {
		$ideal['60% - 79%']++;
	} else {
		$ideal['> 80%']++;
	}

	// Propensity Score
	if($data[$i][6] < 20) {
		$propensity['< 20%']++;
	} else if ($data[$i][6] < 40) {
		$propensity['20% - 39%']++;
	} else if ($data[$i][6] < 60) {
		$propensity['40% - 59%']++;
	} else if ($data[$i][6] < 80) {
		$propensity['60% - 79%']++;
	} else {
		$propensity['> 80%']++;
	}
}

// Sort ascending to fix issues with empty 
arsort( $gender );
arsort( $race );
arsort( $age );
arsort( $county );

/*
	---------------------------------------------------------------------------
		Set up Custom PDF Header
	---------------------------------------------------------------------------
*/

// Extend the TCPDF class to create custom Header and Footer
class BLUEPRINT extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		$image_file = K_PATH_IMAGES . 'logo.png';

		// Set font
		$this->SetFont('helvetica', 'B', 16);
	
		// Title
		$this->writeHTMLCell(0, 0, -52.5, 6, '<font color="#366699">' . strtoupper($_POST['org']) . "</font> MEMBERSHIP REPORT", 0, 0, 0, false, true, 'L', true);

		// Set image
		$this->Image($image_file, 0, 5, 0, '', 'PNG', '', 'T', false, 300, 'R', false, false, 0, false, false, false);

		// Move to a new line
//		$this->ln(12);

		// Set font
//		$this->SetFont('helvetica', 'N', 10);

		// Set the subheader
//		$this->Cell(0, 0, 'Count of the total records: ' , 0, false, 'L', 0, '', 0, false, 'L', 'T');
	}

	// Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);

        // Set font
        $this->SetFont('helvetica', 'I', 8);

        // Page number
        //$this->Cell(0, 10, 'Page '. $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

/*
	---------------------------------------------------------------------------
		Set up PDF
	---------------------------------------------------------------------------
*/

// create new PDF document
$pdf = new BLUEPRINT(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Get Today's Date
$today = date("F j, Y");  

// Set title
$pdfTitle = '<font color=red>' . strtoupper($_POST['org']) . "</font> MEMBERSHIP REPORT";


// set document information
$pdf->SetCreator('Richir Outreach');
$pdf->SetAuthor('Richir Outreach');
$pdf->SetTitle($pdfTitle);

// set default header data
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

//pre_print_r($county);

/*
	---------------------------------------------------------------------------
		Make the PDF
	---------------------------------------------------------------------------
*/

define( 'GRAPH_MARGIN', 5 );

define( 'GRAPH_POSITION_Y', 50 );

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', 'N', 10);

$pdf->Cell(0, 0, 'Count of the total records: ' . count( $data ), 0, false, 'L', 0, '', 0, false, 'L', 'T');

// Set font back to "normal"
$pdf->SetFont('helvetica', 'N', 12);

$pdf->ln();

// Display the description text
$pdf->MultiCell(0, 0, $_POST['desc'], '', 'L');

// Get the width of the page
$pageWidth = floor( $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT);

// Draw the left margin decoration
leftMarginBorder( $pdf, array( 54, 102, 153 ) );

// Gender Pie Chart
pieChart( $pdf, "By Gender", $genderText, ($pageWidth / 3) - 2 * GRAPH_MARGIN, PDF_MARGIN_LEFT + GRAPH_MARGIN, GRAPH_POSITION_Y, 20, $gender, $colors, "top" );

// Divider to the right of the gender char
chartDivider( $pdf, PDF_MARGIN_LEFT + ( $pageWidth / 3 ) - ( GRAPH_MARGIN / 2 ), GRAPH_POSITION_Y + 5, 105, array( 54, 102, 153 ) );

// Race chart
pieChart( $pdf, "By Race", $raceText, ($pageWidth / 3) - 2 * GRAPH_MARGIN, PDF_MARGIN_LEFT + ( $pageWidth / 3 ) + GRAPH_MARGIN, GRAPH_POSITION_Y, 20, $race, $colors, "top" );

// Race divider
chartDivider( $pdf, PDF_MARGIN_LEFT + ( $pageWidth / 3 ) +  ($pageWidth / 3), GRAPH_POSITION_Y + 5, 105, array( 54, 102, 153 ) );

// Age chart
pieChart( $pdf, "By Age", $ageText, ($pageWidth / 3) - 2 * GRAPH_MARGIN, PDF_MARGIN_LEFT + ( ($pageWidth / 3) * 2) + GRAPH_MARGIN, GRAPH_POSITION_Y, 20, $age, $colors, "top" );

// Ideology pie chart
pieChart( $pdf, "By Ideology Score", $ideologyText, ($pageWidth / 2) - 2 * GRAPH_MARGIN, PDF_MARGIN_LEFT + GRAPH_MARGIN, GRAPH_POSITION_Y + 120, 20, $ideal, $colors, "right" );

// Race divider
chartDivider( $pdf, PDF_MARGIN_LEFT + ( $pageWidth / 2 ), GRAPH_POSITION_Y + 125, 60, array( 54, 102, 153 ) );

// Vote Propensity
pieChart( $pdf, "By Vote Propensity", $propensityText, ( $pageWidth / 2 ) - ( 2 * GRAPH_MARGIN ), PDF_MARGIN_LEFT + ( GRAPH_MARGIN * 2 ) + ( $pageWidth / 2 ), GRAPH_POSITION_Y + 120, 20, $propensity, $colors, "right" );

// County
barChart( $pdf, 'By County', $countyText, PDF_MARGIN_LEFT, 245, 10, 32.5, $county, 12, $colors, 5 );

/*
	---------------------------------------------------------------------------
		Output the PDF
	---------------------------------------------------------------------------
*/

if( !isset( $_POST['preview'] ) ) {
	$pdf->Output('output.pdf', 'D');
} else {
	$pdf->Output('output.pdf', 'I');
}

?>