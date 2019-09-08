<?php

namespace ant\payment\api\v1\resources;

use yii\helpers\Url;
use yii\web\Linkable;
use yii\web\Link;

class Invoice extends \common\modules\payment\models\Invoice implements Linkable
{
    public function fields()
    {
        return \yii\helpers\ArrayHelper::merge(parent::fields(), [
			'invoiceItems',
		]);
    }

    /*public function extraFields()
    {
        return ['fileAttachments'];
    }*/

    /**
     * Returns a list of links.
     *
     * @return array the links
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['/api/payment/v1/invoice/view', 'id' => $this->id], true)
        ];
    }
	
	/*public function getCreatedBy() {
		return $this->hasOne(User::className(), ['id' => 'created_by']);
	}*/
}
