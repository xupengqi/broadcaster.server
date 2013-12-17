<?php $this->context->loadHelpers(array('js')); ?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<title>p1</title>
	<?php
		echo $this->js('https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js', true);
		echo $this->js('https://maps.googleapis.com/maps/api/js?key=AIzaSyCoLY9_8vU28G_f-KKOywcmhuA8TWRoJ4I&sensor=false', true);
		
		echo $this->css('thegrid.core');
		echo $this->js('thegrid.find');
	?>
	<?php JSHelper::$buffer.= 'p1.init();'; ?>
</head>
<body>
	<div id="p1-map"></div>
	<div style="position: absolute; bottom: 0; left: 0;">
		<input type="text" value="10000" id="meters" />
	</div>
</body>
</html>
