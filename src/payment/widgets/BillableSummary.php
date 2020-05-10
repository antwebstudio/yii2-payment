<?php
namespace ant\payment\widgets;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

class BillableSummary extends \yii\base\Widget {
	public $attributes;
	public $model;
	public $showSubtotal = true;
	public $itemsRelation;
	public $colspan;
	
	public function run() {
		if (is_callable($this->attributes)) {
			$this->attributes = call_user_func_array($this->attributes, [$this->model]);
		}
		return $this->render('billable-summary', [
			'model' => $this->model,	
		]);
	}
	
	public function renderSummaryRow($model, $attribute) {
		$visible = isset($attribute['visible']) ? $attribute['visible'] : true;
		if (is_callable($visible)) $visible = call_user_func_array($visible, [$model]);
		
		$labelOptions = isset($attribute['labelOptions']) ? $attribute['labelOptions'] : ['colspan' => $this->colspan, 'class' => 'text-right'];
		$options = isset($attribute['options']) ? $attribute['options'] : ['class' => 'text-right'];
		
		$html = '';
		if ($visible) {
			$html = Html::beginTag('tr');
			
			$html .= Html::tag('td', $this->renderSummaryLabelCellContent($model, $attribute), $labelOptions);
			$html .= Html::tag('td', $this->renderSummaryValueCellContent($model, $attribute), $options);
			$html .= Html::endTag('tr');
		}
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
	
	protected function getSummaryCellLabel($model, $attribute) {
		return $this->getDataCellLabel($model, $attribute);
	}
	
	protected function getSummaryCellValue($model, $attribute) {
		return $this->getDataCellValue($model, $attribute);
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
}