<?php
namespace ant\payment\widgets;

use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use ant\payment\models\Payment;
use yii\data\ActiveDataProvider;

class InvoiceSummary extends Widget{
	public $model;
	public $header = '{title}';
	public $title = 'Details';
	public $showSubtotal = true;
	public $itemsRelation = 'billItems';
	public $detail = [];
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
	
	public function init()
	{
        parent::init();
	}

	public function run(){	

        return $this->render('invoiceSummary', ['model' => $this->model]);
            
	}

	protected function getDataCellLabel($model, $attribute) {
		if (is_array($attribute)) {
			$column = $attribute;
			
			if (isset($column['label'])) {
				return \Yii::t('payment', $column['label']);
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
			'{title}' => \Yii::t('payment', $this->title),
		]);
	}
	
	public function renderColumnHeader($model, $column) {
		$column = $this->normalizeColumn($column);
		if ($column['visible']) {
			$options = isset($column['headerOptions']) ? $column['headerOptions'] : [];
			return Html::tag('th', $this->getSummaryCellLabel($model, $column), $options);
		}
	}
	
	public function renderRow($model) {
		$html = Html::beginTag('tr');
		
		foreach ($this->columns as $column) {
			$column = $this->normalizeColumn($column);
			
			if ($column['visible']) {
				$options = isset($column['options']) ? $column['options'] : ['class' => 'text-right'];
				$html .= Html::tag('td', $this->renderRowValueCellContent($model, $column), $options);
			}
		}
		
		$html .= Html::endTag('tr');
		
		return $html;
	}
	
	protected function normalizeColumn($column) {
		foreach (['visible'] as $name) {
			if (!isset($column[$name])) {
				$column[$name] = true;
			}
		}
		return $column;
	}
	
	protected function renderRowValueCellContent($model, $column) {
		$format = isset($column['format']) ? $column['format'] : 'text';
		
		if (is_array($column)) {
            return $this->formatter->format($this->getSummaryCellValue($model, $column), $format);
		}
		return $model->{$column};
	}
	
	protected function calculateColumn() {
		$count = 0;
		foreach ($this->columns as $column) {
			$column = $this->normalizeColumn($column);
			
			if ($column['visible']) {
				$count++;
			}
		}
		return $count;
	}
	
	public function renderSummaryRow($model, $attribute) {
		$labelOptions = isset($attribute['labelOptions']) ? $attribute['labelOptions'] : ['colspan' => $this->calculateColumn() - 1, 'class' => 'text-right'];
		$options = isset($attribute['options']) ? $attribute['options'] : ['class' => 'text-right'];
		
		$html = Html::beginTag('tr');
		
		$html .= Html::tag('td', $this->renderSummaryLabelCellContent($model, $attribute), $labelOptions);
		$html .= Html::tag('td', $this->renderSummaryValueCellContent($model, $attribute), $options);
		$html .= Html::endTag('tr');
		
		return $html;
	}
	
	public function renderDetail() {
		if ($this->detail !== false) {
			$options = $this->detail;
			$options['model'] =  $this->model;
			return InvoiceDetail::widget($options);
		}
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


