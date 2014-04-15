<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>14-18 GILBERT ROAD, PRESTON</title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
<link href='http://fonts.googleapis.com/css?family=Maven+Pro:400,500,700' rel='stylesheet' type='text/css'>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>


    <script type="text/javascript" src="js/jquery.js"></script>
    <script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
    <script src="js/bjqs-1.3.js"></script>

    <script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
    <script src="js/bjqs-1.3.js"></script>
     <script type="text/javascript" src="display.js"></script>
     
     <link href="style2.css" rel="stylesheet" type="text/css">
     </head>
<body>
<div class="ribbon">
<img src="images/ribbon.png" alt="Grand Opening">
</div>
<div class="logo"><img src="images/logo.png" width="240px" alt=""></div>
<div class="wrapper">
		<div class="register-button">
		  <a href="index.html#" onclick="$('#register-now-form').slideToggle(); return false;">Register Now</a>
		 </div>
		<div id="register-now-form" class="hidden">
			  <?php include 'form.php'; ?>
		</div>
    <div class="wrap-elements">
	<div class="wrapper-left">
	      <div id="banner-fade">
        <ul class="bjqs">
        <div class="info-wrapper">
<div id="display-suite-inner">
</div>
</div>
<li><img src="images/building.jpg" alt=""></li>
<li><img src="images/building1.jpg" alt=""></li>
<li><img src="images/building2.jpg" alt=""></li>
<li><img src="images/building3.jpg" alt=""></li>
<li><img src="images/building4.jpg" alt=""></li>
</div>
	</div>
	<div class="wrapper-right">
		<div class="top-price">
			<h2>$10,000</h2>
			<p class="white-text">Flight centre holiday voucher</p>
			<p class="grey-text">for 5 first apartment buyers</p>
			<p class="grey-text">on grand opening day</p>
		</div>
		<div class="bottom-price">
			<h2>$5,000</h2>
			<p class="white-text">Flight centre holiday voucher</p>
			<p class="grey-text">for the next 5 apartment</p>
			<p class="grey-text">buyers on grand opening day</p>
		</div>
	</div>
    </div>
	<p style="font-size:26px;">1 BEDROOM APARTMENTS FROM <strong>$295,000</strong><br>
	2 BEDROOM APARTMENTS FROM <strong>$355,000</strong></p>
	<p style="margin:0;padding-top:10px;text-transform:initial">For more information, contact: Felicity Paglia 0401 040 090</p>
</div>
<p style="color:#414042">14-18 GILBERT ROAD, PRESTON</p>
      <script>
        jQuery(document).ready(function($) {

          $('#banner-fade').bjqs({
            height      : 1080,
            width       : 1920,
            responsive  : true
          });

        });
      </script>
<script type="text/javascript" src="display.js"></script>
</body>
</html>