<?php
	// copyright year
	$year = date('Y');
?>
<html>
	<head>
		<title>Congratulations! You have successfully installed WebSmart's server side components.</title>
		<style type="text/css"> 
			body {
				font: 12px Verdana, Arial, Helvetica, sans-serif;
				margin-left: 10px;
				margin-top: 10px;
				margin-right: 10px;
			}
			p, blockquote {
				font: 12px Verdana, Arial, Helvetica, sans-serif;
				line-height: 18px;
			}

			a:link {color: #097aba; text-decoration: none;}
			a:visited {color: #097aba; text-decoration: none;}
			a:hover {color: #383838; text-decoration:none;}
			a:active {color: #097aba; text-decoration: none;}
			#Header {border-top: 4px solid #CB0000;}
			#Footer {
				text-align: center; 
				padding-bottom: 10px; 
				border-bottom: 4px solid #097aba; 
				font-family: Verdana, arial, helvetica, sans-serif; 
				line-height: 10px; 				
				font-size: 10px; 
				margin-top: 10px
			}
		</style>
	</head>
	<body>
		<div id="Header"></div>

		<p>Congratulations! You have successfully installed and configured WebSmart PHP's server side components.</p>
		<p>You can now continue with the installation of the software.</p>
		<p>If this page doesn't appear to have loaded correctly, or you have any other questions or concerns, please call BCD Technical Support at 250-655-1766.</p>
		
		<div id="Footer">
			<img src="/websmart/tool/images/PWSani.gif"><br>
			WebSmart is a registered trademark of ESDI.<br>
			The software and all accompanying documentation is the property and Copyright of ESDI (2000-<? echo $year;?>)
		</div>
	</body>
</html>

