<?php
 ?>

<div id="<?= $this->context->id ?>" class="invoice-summary" style="position: relative; ">
    <div class="panel panel-default"> 
        <div class="ribbon-corner ribbon-top-right"><span><?= $model->isPaid ? 'Paid' : 'Unpaid' ?></span></div>
        <div class="panel-heading"><?= $this->context->renderHeader() ?></div>
        <div class="panel-body">
            <table class="table">
                <?php foreach ($this->context->details as $attribute): ?>
                    <?= $this->context->renderDetailRow($model, $attribute) ?>
                <?php endforeach; ?>
            </table>

            <table class="table">
                <thead>
                    <tr>
                        <?php foreach ($this->context->columns as $column): ?>
                            <?= $this->context->renderColumnHeader($model, $column) ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->{$this->context->itemsRelation} as $item ): ?>
						<?php if ($item->includedInSubtotal): ?>
							<?= $this->context->renderRow($item) ?>
						<?php endif ?>
                    <?php endforeach; ?>
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