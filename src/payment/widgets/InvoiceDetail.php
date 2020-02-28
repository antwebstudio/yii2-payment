<?php
namespace ant\payment\widgets;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

class InvoiceDetail extends \yii\base\Widget {
	public $model;
	
	public $labelOptions = ['class' => 'col-md-3 order-detail-label'];
	public $cellOptions = ['class' => 'col-md-9 order-detail-value'];
	public $rowOptions = ['class' => 'd-flex'];
	
	public $attributes = [
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

	public function run(){
        return $this->render('invoice-detail', ['model' => $this->model]);
	}

	public function renderDetailRow($model, $column) {
		$html = Html::beginTag('tr', $this->rowOptions);
		
		$labelOptions = isset($column['labelOptions']) ? $column['labelOptions'] : $this->labelOptions;
		$options = isset($column['options']) ? $column['options'] : $this->cellOptions;
		$html .= Html::tag('td', $this->getDataCellLabel($model, $column), $labelOptions);
		$html .= Html::tag('td', $this->getDataCellValue($model, $column), $options);
		
		$html .= Html::endTag('tr');
		
		return $html;
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
}