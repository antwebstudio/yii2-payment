
					
<?php if ($this->context->showSubtotal): ?>
	<?= $this->context->renderSummaryRow($model, 'subtotal') ?>
<?php endif ?>

<?php foreach ($model->{$this->context->itemsRelation} as $item ): ?>
	<?php if (!$item->includedInSubtotal): ?>
		<?= $this->context->renderSummaryRow($item, ['label' => $item->title, 'attribute' => 'discountedUnitPrice']) ?>
	<?php endif ?>
<?php endforeach ?>

<?php foreach ($this->context->attributes as $attribute): ?>
	<?php if ($attribute != 'subtotal'): ?>
		<?= $this->context->renderSummaryRow($model, $attribute) ?>
	<?php endif ?>
<?php endforeach ?>