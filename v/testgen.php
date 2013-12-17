<?php $this->context->loadHelpers(array('js', 'form')); ?>
<!DOCTYPE html>
<html>
<head>
<title>p1 content generation</title>
<?php
echo $this->js('https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js', true);
echo $this->js('https://maps.googleapis.com/maps/api/js?key=AIzaSyCoLY9_8vU28G_f-KKOywcmhuA8TWRoJ4I&sensor=false', true);
echo $this->js('thegrid.gen');
echo $this->css('http://yui.yahooapis.com/3.8.1/build/cssreset/cssreset-min.css', true);
echo $this->css('thegrid.core');
?>
</head>
<body>
 <table style="width: 100%; height: 100%;">
  <tr>
   <td style="width: 200px; background: #333;" rowspan="2"></td>
   <td style="height: 100%;"><iframe
     style="width: 100%; height: 100%; overflow: hidden;" height="100%"
     width="100%" id="p1frame" frameborder="0"
     src="http://localhost/"></iframe>
   </td>
  </tr>
  <tr>
   <td style="height: 200px;"><?php
   $this->context->helpers['form']
   ->begin('post', 'P1Gen')
   ->input('registration', 'text', array('placeholder'=>'#/min', 'value'=>'10'))
   ->input('post', 'text', array('placeholder'=>'#/min', 'value'=>'20'))
   ->input('reply', 'text', array('placeholder'=>'#/min', 'value'=>'30'))
   ->submit('start')
   ->submit('stop')
   ->end();
   ?>
   </td>
  </tr>
 </table>
</body>
</html>
