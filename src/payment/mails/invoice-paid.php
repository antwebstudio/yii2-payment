<h2>RECEIPT</h2>

<p>
Attn: <?= $invoice->billedTo->lastname ?><br/>
Date: <?= date('Y-m-d') ?><br/>
Invoice: <?= $invoice->formattedId ?><br/>
</p>

<table style="width: 100%; margin-bottom: 10px; border-color: 1px #cccccc solid; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th style="padding: 3px 5px; text-align: left">Item</th>
                <th style="padding: 3px 5px; text-align: left">Unit Price</th>
                <th style="padding: 3px 5px; text-align: left">Quantity</th>
                <th style="padding: 3px 5px; text-align: left">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoice->billItems as $item): ?>
            <tr>
                <td style="padding: 3px 5px; "><?= $item->name ?><br/><?= $item->description ?></td>
                <td style="padding: 3px 5px; text-align: right"><?= $item->discountedUnitPrice ?></td>
                <td style="padding: 3px 5px; text-align: right"><?= $item->quantity ?></td>
                <td style="padding: 3px 5px; text-align: right"><?= $item->netTotal ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
			<tr>
				<td colspan="3" style="padding: 3px 5px; text-align: right">Subtotal : </td>
				<td style="padding: 3px 5px; text-align: right"><?= Yii::$app->formatter->asCurrency($invoice->getSubtotal()) ?></td>
			</tr>
			<tr>
				<td colspan="3" style="padding: 3px 5px; text-align: right">Discount : </td>
				<td style="padding: 3px 5px; text-align: right"><?= Yii::$app->formatter->asCurrency($invoice->getDiscountAmount()) ?></td>
			</tr>
			<tr>
				<td colspan="3" style="padding: 3px 5px; text-align: right">Tax : </td>
				<td style="padding: 3px 5px; text-align: right"><?= Yii::$app->formatter->asCurrency($invoice->getTaxCharges()) ?></td>
			</tr>
            <tr>
                <td colspan="3" style="padding: 3px 5px; text-align: right">Total : </td>
                <td style="padding: 3px 5px; text-align: right"><b><?=Yii::$app->formatter->asCurrency($invoice->getCalculatedTotalAmount());?></b></td>
            </tr>
        </tfoot>
    </table>

Thank You!