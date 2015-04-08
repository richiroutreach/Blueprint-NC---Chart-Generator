<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Upload File</title>

		<style>
			p { font-size:0.9em; }
			form div { margin-bottom: 1rem; }
			textarea { width: 100%; }
		</style>
	</head>

	<body>
		<h1 style="color:#766;font-size:1.4em;font-weight: normal;">Would you like a report?</h1>
		<p style="max-width:800px;">Upload a file below and you will receive a report. The file should be tab-separated values (TSV). After you press the "Upload TSV" button, your browser should prompt you to download a file named <strong>output.pdf</strong>. You might want to rename it after you download it.</p>
		<div style="width:500px; margin-right:auto; margin-left:auto; border:1px solid #EEE; padding:60px; background-color:#FEF8F8;">
			<form action="index.php" method="post" enctype="multipart/form-data">
				<div>
					<label for="org">Organization Name:</label>
					<input type="text" name="org" id="org" />
				</div>

				<div>
					<label for="desc">Description Text:</label>
					<textarea rows="10" name="desc" id="desc"></textarea>
				</div>

				<div>
					<label for="csv">Select TSV to upload:</label>
					<input type="file" name="csv" id="csv">
				</div>

				<input type="submit" value="Generate PDF" name="submit">			
			</form>	
		</div>
		<footer>
			<p>For questions, problems, or comments please email mizelle(AT)richiroutreach.com. Or call 919-395-1794.</p>
		</footer>	
	</body>
</html>