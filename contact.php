<?php $title = 'Contact us - The Gilbert, Preston'; include("includes/header.php"); ?>
	<link rel="stylesheet" href="css/layout.css" type="text/css" media="all" charset="utf-8" />
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.infieldlabel.js" type="text/javascript" charset="utf-8"></script>
	
	<script type="text/javascript" charset="utf-8">
		$(function(){ $("label").inFieldLabels(); });
	</script>
	<div class="container bg7 menu7">
	<div class="wrapper">
		<div class="menu"><?php require($DOCUMENT_ROOT . "includes/menu.php"); ?></div>
		<div class="content"><?php require($DOCUMENT_ROOT . "includes/pages/contact.php"); ?></div>
	</div>
	<div id="clear">asdf</div>
</div>
<div><img src="images/container-shadow.png" alt="" class="shadow"></div>
<div class="footer"><?php require($DOCUMENT_ROOT . "includes/footer.php"); ?>