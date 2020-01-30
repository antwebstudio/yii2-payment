
<table class="table">
	<?php foreach ($this->context->attributes as $attribute): ?>
		<?= $this->context->renderDetailRow($model, $attribute) ?>
	<?php endforeach ?>
</table>