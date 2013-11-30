<?php
function print_header($page){
	$header = '
			<html>

			<head>
			<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
			<meta name="description" content="description"/>
			<meta name="keywords" content="keywords"/> 
			<meta name="author" content="author"/> 
			<link rel="stylesheet" type="text/css" href="css/style.css" media="screen"/>
			<title>Visual Valgrind</title>
			</head>

			<body>

			<div class="container">

				<div class="header">
					
					<div class="title">
						<h1><a href="index.php">Visual Valgrind</a></h1>
					</div>

				</div>

				<div class="main">
					
					<div class="content">';
	echo $header;
}
function print_footer($page){
	$footer = '
					</div><!--content-->
				<div class="clearer"><span></span></div>

			</div>

		</div>
		</body>

		</html>';
	echo $footer;
}
?>
