<?php
namespace common\modules\payment\widgets;

use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\modules\payment\models\Payment;
use yii\data\ActiveDataProvider;

class InvoiceSummary extends Widget{
	public $model;
	public $header = '{title}';
	public $title = 'Details';
	public $showSubtotal = true;
	public $itemsRelation = 'billItems';
	public $details = [
		[
			'attribute' => 'formattedId',
			'label' => 'Reference',
		],
		[
			'attribute' => 'created_at',
			'label' => 'Date',
		],
		[
			'attribute' => 'billedTo.contactName',
			'label' => 'Billing Contact Name',
		],
		[
			'attribute' => 'billedTo.contact_number',
			'label' => 'Billing Contact Number',
		],
		[
			'attribute' => 'billedTo.email',
			'label' => 'Billing Contact Email',
		],
		[
			'attribute' => 'billedTo.addressString',
			'label' => 'Billing Address',
		],
		// Invoice don't have shipTo attribute, only order have.
		/*[
			'attribute' => 'shipTo.contactName',
			'label' => 'Shipping Contact Name',
		],
		[
			'attribute' => 'shipTo.contact_number',
			'label' => 'Shipping Contact Number',
		],
		[
			'attribute' => 'shipTo.email',
			'label' => 'Shipping Contact Email',
		],
		[
			'attribute' => 'shipTo.addressString',
			'label' => 'Shipping Address',
		],*/
	];
	public $columns = [
		[
			'attribute' => 'title', 
			'label' => 'Item',
			'options' => ['class' => 'text-left'],
		],
		[
			'attribute' => 'discountedUnitPrice', 
			'label' => 'Unit Price',
			'headerOptions' => ['class' => 'text-right'],
		],
		[
			'attribute' => 'quantity', 
			'options' => ['class' => 'text-center'],
			'headerOptions' => ['class' => 'text-center'],
		],
		[
			'attribute' => 'netTotal', 
			'label' => 'Amount',
		],
	];
	public $summary = [
		'subtotal', 'discountAmount', 'serviceCharges', /*'tax_amount', */
		[
			'attribute' => 'netTotal',
			'label' => 'Total',
			'format' => 'html',
		],
		'paidAmount', 'dueAmount',
	];

	public function run(){	

        return $this->render('invoiceSummary', ['model' => $this->model]);
            
	}

	protected function getDataCellLabel($model, $attribute) {
		if (is_array($attribute)) {
			$column = $attribute;
			
			if (isset($column['label'])) {
				return $column['label'];
			} else if (isset($column['attribute'])) {
				return $model->getAttributeLabel($column['attribute']);
			}
		} else {
			return $model->getAttributeLabel($attribute);
		}
	}
	
	protected function getDataCellValue($model, $column, $index = null)
    {
		if (is_string($column)) {
			$attribute = $column;
			$value = null;
		} else {
			$attribute = isset($column['attribute']) ? $column['attribute'] : null;
			$value = isset($column['value']) ? $column['value'] : null;
		}
		
        if ($value !== null) {
            if (is_string($value)) {
                return ArrayHelper::getValue($model, $value);
            } else {
                return call_user_func($value, $model, $attribute, $index, $this);
            }
        } elseif ($attribute !== null) {
            return ArrayHelper::getValue($model, $attribute);
        }
        return null;
    }
	
	protected function getSummaryCellLabel($model, $attribute) {
		return $this->getDataCellLabel($model, $attribute);
	}
	
	protected function getSummaryCellValue($model, $attribute) {
		return $this->getDataCellValue($model, $attribute);
	}
	
	public function renderHeader() {
		return strtr($this->header, [
			'{title}' => $this->title,
		]);
	}

	public function renderDetailRow($model, $column) {
		$html = Html::beginTag('tr');
		
		$labelOptions = isset($column['labelOptions']) ? $column['labelOptions'] : ['class' => 'col-md-2 order-detail-label'];
		$options = isset($column['options']) ? $column['options'] : [];
		$html .= Html::tag('td', $this->getDataCellLabel($model, $column), $labelOptions);
		$html .= Html::tag('td', $this->getDataCellValue($model, $column), $options);
		
		$html .= Html::endTag('tr');
		
		return $html;
	}
	
	public function renderColumnHeader($model, $column) {
		$options = isset($column['headerOptions']) ? $column['headerOptions'] : [];
		return Html::tag('th', $this->getSummaryCellLabel($model, $column), $options);
	}
	
	public function renderRow($model) {
		$html = Html::beginTag('tr');
		
		foreach ($this->columns as $column) {
			$options = isset($column['options']) ? $column['options'] : ['class' => 'text-right'];
			$html .= Html::tag('td', $this->renderRowValueCellContent($model, $column), $options);
		}
		
		$html .= Html::endTag('tr');
		
		return $html;
	}
	
	protected function renderRowValueCellContent($model, $column) {
		$format = isset($column['format']) ? $column['format'] : 'text';
		
		if (is_array($column)) {
            return $this->formatter->format($this->getSummaryCellValue($model, $column), $format);
		}
		return $model->{$column};
	}
	
	public function renderSummaryRow($model, $attribute) {
		$labelOptions = isset($attribute['labelOptions']) ? $attribute['labelOptions'] : ['colspan' => 3, 'class' => 'text-right'];
		$options = isset($attribute['options']) ? $attribute['options'] : ['class' => 'text-right'];
		
		$html = Html::beginTag('tr');
		
		$html .= Html::tag('td', $this->renderSummaryLabelCellContent($model, $attribute), $labelOptions);
		$html .= Html::tag('td', $this->renderSummaryValueCellContent($model, $attribute), $options);
		$html .= Html::endTag('tr');
		
		return $html;
	}
	
	protected function renderSummaryLabelCellContent($model, $attribute) {
		$format = isset($attribute['format']) ? $attribute['format'] : 'text';
		
		if (is_array($attribute)) {
		}
		return $this->formatter->format($this->getSummaryCellLabel($model, $attribute), $format);
	}
	
	protected function renderSummaryValueCellContent($model, $attribute) {
		$format = isset($attribute['format']) ? $attribute['format'] : 'text';
		
		if (is_array($attribute)) {
            return $this->formatter->format($this->getSummaryCellValue($model, $attribute), $format);
		}
		return $model->{$attribute};
	}
	
	protected function getFormatter() {
		return \Yii::$app->formatter;
	}
}


