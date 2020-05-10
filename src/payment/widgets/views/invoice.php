<?php
use yii\helpers\Html;
?>
<?= Html::beginTag('table', $this->context->tableOptions) ?>
	<thead>
		<tr>
			<?php foreach ($this->context->columns as $column): ?>
				<?= $this->context->renderColumnHeader($model, $column) ?>
			<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($model->{$this->context->itemsRelation} as $item ): ?>
			<?php if ($item->includedInSubtotal): ?>
				<?= $this->context->renderRow($item) ?>
			<?php endif ?>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<?= $this->context->renderSummary() ?>
	</tfoot>
<?= Html::endTag('table') ?>