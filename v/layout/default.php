<?php $this->context->loadHelpers(array('js')); ?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <title>broadcaster</title>
    <?php
        echo $this->js('//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js', true);
        echo $this->js('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js', true);
        echo $this->js('https://maps.googleapis.com/maps/api/js?key=AIzaSyCoLY9_8vU28G_f-KKOywcmhuA8TWRoJ4I&sensor=false', true);
        
        echo $this->css('ui-lightness/jquery-ui-1.10.1.custom.min');
        echo $this->css('thegrid.core');
        echo $this->css('thegrid.account');
        echo $this->css('thegrid.posts');
        echo $this->js('thegrid.core');
        echo $this->js('thegrid.rest');
        echo $this->js('thegrid.mvc');
    ?>
</head>
<body>
    <div id="p1Header">
        <?php echo $this->layoutView('header', $data); ?>
      </div>
      <div id="p1Left">
      </div>
      <div id="p1Content">
        <?php echo $this->layoutView('body', $data); ?>
        <?php echo $this->layoutView('footer', $data); ?>
      </div>
    <?php $this->context->helpers['js']->template(array('post','postItem')); ?>
    <?php echo $this->context->helpers['js']->dump(); ?>
    <script>
    (function(i,s,o,g,r,a,m) {
        i['GoogleAnalyticsObject']=r;
        i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)
        },i[r].l=1*new Date();
        a=s.createElement(o), m=s.getElementsByTagName(o)[0];
        a.async=1;
        a.src=g;
        m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    
      ga('create', 'UA-41790453-1', 'xpq.im');
      ga('send', 'pageview')
    </script>
</body>
</html>
