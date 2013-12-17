<?php $this->context->loadHelpers(array('js')); ?>
<div id="p1-map"></div>
<?php if(isset($data['lat']) && isset($data['lng'])): ?>	
	<?php $this->context->helpers['js']->append("p1.init({$data['lat']}, {$data['lng']});"); ?>
<?php else: ?>
	<?php $this->context->helpers['js']->append('p1.init();'); ?>
<?php endif; ?>