<?php
namespace ant\payment\widgets;

use Yii;
use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use ant\payment\models\Payment;
use yii\data\ActiveDataProvider;

class InvoiceSummary extends Widget {
	public $isPaid;
	public $viewFile = 'invoice';
	public $model;
	public $header = '<div class="card-header panel-heading">{title}</div>';
	public $template = '<div class="card panel panel-default">{ribbon}{header}<div class="card-body panel-body">{detail}{items}</div></div>';
	public $ribbonTemplate = '<div class="ribbon-wrapper"><div class="ribbon {color}">{status}</div></div>';
	public $title = 'Details';
	public $showSubtotal = true;
	public $itemsRelation = 'billItems';
	public $options = [];
	public $tableOptions = ['class' => 'table'];
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
		'attributes' => [
			'subtotal', 'discountAmount', 'serviceCharges', /*'tax_amount', */
			[
				'attribute' => 'netTotal',
				'label' => 'Total',
				'format' => 'html',
			],
			'paidAmount', 'dueAmount',
		]
	];
	
	public function init()
	{
		if (YII_DEBUG) throw new \Exception('DEPRECATED'); // 2020-05-10
		
        parent::init();
	}

	public function run(){	
		if (is_callable($this->columns)) {
			$this->columns = call_user_func_array($this->columns, [$this->model]);
		}
		//$html = Html::beginTag('div', $this->options);
		
		return strtr($this->template, [
			'{header}' => $this->renderHeader(),
			'{ribbon}' => $this->renderRibbon(),
			'{detail}' => $this->renderDetail(),
			'{items}' => $this->render($this->viewFile, ['model' => $this->model]),
		]);
        //$html = Html::endTag('div');    
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
				if ($model->hasAttribute($value)) {
					return ArrayHelper::getValue($model, $value);
				} else {
					return $value;
				}
            } else {
                return call_user_func($value, $model, $attribute, $index, $this);
            }
        } elseif ($attribute !== null) {
            return ArrayHelper::getValue($model, $attribute);
        }
        return null;
    }
	
	public function renderRibbon() {
		$isPaid = $this->getIsPaid();
		
		return strtr($this->ribbonTemplate, [
			'{status}' => \Yii::t('payment', $this->getPaidStatus()),
			'{color}' => $isPaid ? 'green' : 'red', 
		]);
	}
	
	public function renderHeader() {
		return strtr($this->header, [
			'{title}' => \Yii::t('payment', $this->title),
			'{ribbon}' => $this->renderRibbon(),
		]);
	}
	
	public function renderColumnHeader($model, $column) {
		$column = $this->normalizeColumn($column);
		if ($column['visible']) {
			$options = isset($column['headerOptions']) ? $column['headerOptions'] : [];
			return Html::tag('th', $this->getDataCellLabel($model, $column), $options);
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
	
	protected function getPaidStatus() {
		$isPaid = $this->getIsPaid();
		return Yii::t('payment', $isPaid ? 'Paid' : 'Unpaid');
	}
	
	protected function getIsPaid() {
		return isset($this->isPaid) ? $this->isPaid : $this->model->isPaid;
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
            return $this->formatter->format($this->getDataCellValue($model, $column), $format);
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
	
	public function renderDetail() {
		if ($this->detail !== false) {
			$options = $this->detail;
			$options['model'] =  $this->model;
			return InvoiceDetail::widget($options);
		}
	}
	
	public function renderSummary() {
		if ($this->summary !== false) {
			$options = $this->summary;
			$options['model'] = $this->model;
			$options['itemsRelation'] = $this->itemsRelation;
			$options['colspan'] = $this->calculateColumn() - 1;
			if (isset($this->showSubtotal) && !isset($options['showSubtotal'])) $options['showSubtotal'] = $this->showSubtotal;
			return BillableSummary::widget($options);
		}
	}
	
	protected function getFormatter() {
		return \Yii::$app->formatter;
	}
}


