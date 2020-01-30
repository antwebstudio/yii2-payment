<?php
use ant\payment\widgets\InvoiceDetail;
 ?>

<div id="<?= $this->context->id ?>" class="invoice-summary" style="position: relative; ">
    <div class="card panel panel-default"> 
        <div class="ribbon-wrapper"><div class="ribbon <?= $model->isPaid ? 'green' : 'red' ?>"><?= $model->isPaid ? 'Paid' : 'Unpaid' ?></div></div>
        <div class="card-heading panel-heading"><?= $this->context->renderHeader() ?></div>
        <div class="card-body panel-body">
		
			<?= $this->context->renderDetail() ?>

            <table class="table">
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
					<?php if ($this->context->showSubtotal): ?>
						<?= $this->context->renderSummaryRow($model, 'subtotal') ?>
					<?php endif ?>
					
                    <?php foreach ($model->{$this->context->itemsRelation} as $item ): ?>
						<?php if (!$item->includedInSubtotal): ?>
							<?= $this->context->renderSummaryRow($item, ['label' => $item->title, 'attribute' => 'discountedUnitPrice']) ?>
						<?php endif ?>
                    <?php endforeach ?>
					
                    <?php foreach ($this->context->summary as $attribute): ?>
						<?php if ($attribute != 'subtotal'): ?>
							<?= $this->context->renderSummaryRow($model, $attribute) ?>
						<?php endif ?>
                    <?php endforeach ?>
                </tfoot>
            </table>
        </div>
    </div>
</div>