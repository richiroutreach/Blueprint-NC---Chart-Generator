<?php

/*

*/
function pieChart( $pdf, $title, $description, $barWidth, $xpos, $ypos, $radius, $array, $colors, $legend = false) {
	// Set Title Color
	$pdf->SetTextColor( 0, 0, 0 );
	
	// Set font
	$pdf->SetFont('helvetica', 'N', 16);
	
	// Change the current X & Y to where we are putting the chart
	$pdf->SetXY( $xpos, $ypos);

	// Graph layout changes slightly if we have a legend
	if( $legend ) {
		// Add the text and center the title
		$pdf->Cell( $barWidth / 2, 0, $title, 0, 0, 'C');

	} else {
		// Add the text and center the title
		$pdf->Cell( $barWidth, 0, $title, 0, 0, 'C');

		// Center the graph in our "cell"
		$xpos = $xpos + ($barWidth / 2) - $radius;
	}

	// Offset for legend
	$yOffset = $ypos + ( $radius / 2 );

	// Set the X,Y center and radius of our pie chart
	$ypos = $ypos + ( $radius / 1.5 );

	// Set Color for Descriptive Text
	$pdf->SetTextColor( 0, 0, 0 );
	
	// Set font
	$pdf->SetFont('helvetica', 'I', 8);
	
	// Change the current X & Y to where we are putting the chart
	$pdf->SetXY( $xpos - 7.5, $ypos + ( $radius * 2 ) + 7.5 );

	// Add the description text
	$pdf->MultiCell( $barWidth, 0, $description, 0, '' );

	// Current Angle
	$angle = 0;

	// Cache the last element in the gender array
	end($array);
	$arrayEnd = key($array);

	// Counter for colors array
	$counter = 0;

	// Go through array
	foreach( $array as $key => $row ) {
		// Calculate how big of an arc this element is
		$newAngle = 360 * getPercent( $key, $array );

		// Generate our colors
		$red = $colors[ $counter ][ 0 ];
		$green = $colors[ $counter ][ 1 ];
		$blue = $colors[ $counter ][ 2 ];			

		// Set our fill color
		$pdf->SetFillColor( $red, $green, $blue );

		// If this is the last thing in the array, then it must go to the last part of the array
		if( $key == $arrayEnd ) {
			$pdf->PieSector($xpos + $radius, $ypos + $radius, $radius, $angle, 360, 'F', false, 0, 2);
		} else {
			$pdf->PieSector($xpos + $radius, $ypos + $radius, $radius, $angle, $angle + $newAngle, 'F', false, 0, 2);
		}

		// Set our new starting point
		$angle += $newAngle;

		if( $legend ) {
			// Set the text color
			$pdf->SetTextColor( $red, $green, $blue );			

			// Write Labels
			$pdf->Text( $xpos + ($barWidth / 2) + 5, $yOffset, $key . ' (' . intval( 100 * getPercent( $key, $array) ) . '%)' );

			// Update our label offset
			$yOffset += 5;
		}

		// Incriment our counter for colors
		$counter++;
	}
}

function barChart( $pdf, $title, $description, $xpos, $ypos, $barWidth, $height, $array, $maxBars, $colors = null, $legender = false ) {
	// Set color
	$pdf->SetTextColor( 0, 0, 0 );

	// Change the current X & Y to where we are putting the chart
	$pdf->SetXY( $xpos, $ypos);
	
	// Set font
	$pdf->SetFont('helvetica', 'N', 16);

	// Output the header	
	$pdf->Cell( 0, 0, $title, 0, 0, 'C');

	// Set font
	$pdf->SetFont('helvetica', 'I', 8);

	// Change the current X & Y to where we are putting the chart
	$pdf->SetXY( $xpos, $ypos + $height + 15 );

	// Add the description text
	$pdf->MultiCell( 0, 0, $description, 0, '' );


	// Set an offset so our bars grow to the right
	$xOffset = $xpos;
	$yOffset = $ypos;
	$ypos += 13;
	$counter = 0;

	// Go through array
	foreach( $array as $key => $row ) {
		// Get what height this bar should be
		$barHeight = getBarHeight( $key, $array ) * $height;
	
		if ($counter < $maxBars ) {
			// Generate our colors
			$red = $colors[ $counter ][ 0 ];
			$green = $colors[ $counter ][ 1 ];
			$blue = $colors[ $counter ][ 2 ];	
		
			// Set our border style (width = 0 to remove border)
			$style = array('width' => 0.5, 'color' => adjustColorLightenDarken( array( 'r' => $red, 'g' => $green, 'b' => $blue ), 10 ) );

			// Draw our box
			$pdf->rect( $xOffset, $ypos - $barHeight + $height, $barWidth, $height - ($height - $barHeight), 'DF', array( 'all' => $style ),  array( $red, $green, $blue ) );		

			// Offset for the next bar
			$xOffset += $barWidth + 0.5;
		}
	
		$counter++;
	}
}

function getPercent( $index, $array ) {
	$total = 0;
	foreach( $array as $row ) {
		$total += $row;
	}
	return $array[$index] / $total;
}

function read_csv($file, $delimiter = "\t") {
	$fh = fopen($file, 'r');
	while(!feof($fh)) {
		$csv[] = fgetcsv($fh, 0, $delimiter);
	}
	fclose($fh);
	return $csv;
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

function pre_print_r($array) {
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function leftMarginBorder( $pdf, $color ) {
	$pdf->Line( ( PDF_MARGIN_LEFT / 4 ), 0, ( PDF_MARGIN_LEFT / 2 ), $pdf->getPageHeight(), array('width' => 1.5, 'color' => $color ) );
	$pdf->Line( ( PDF_MARGIN_LEFT / 4 ) + 2, 0, ( PDF_MARGIN_LEFT / 2 ) + 2, $pdf->getPageHeight(), array('width' => 1, 'color' => $color ) );
}


function chartDivider( $pdf, $xpos, $ypos, $height, $color ) {
	$pdf->Line( $xpos, $ypos, $xpos, $ypos + $height, array('width' => 0.25, 'color' => $color ) );
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