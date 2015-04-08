<?php

if( isset( $_POST['submit'] ) ) {
	//include("display.php");
	include("pdf.php");
} else {
	include("ask.php");
}
?>