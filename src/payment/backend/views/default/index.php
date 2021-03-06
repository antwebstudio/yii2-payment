<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use ant\payment\models\Invoice;

$this->title = 'Invoice';
?>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<div class="panel panel-default">
<div class="panel-body">
<?= \yii\grid\GridView::widget([
    'layout' => "{items}\n{pager}",
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'options' => ['class' => 'table-responsive'],
	'tableOptions' => ['class' => 'table table-striped table-bordered align-middle'],
	'columns' => 
	[
	    [
		    'label' => 'ID',
		    'attribute' => 'formatted_id',
		    'headerOptions' => ['class' => 'min-width'],
	    ],
	    [
		    'label' => 'Total Amount',
		    'attribute' => 'total_amount',
		 	'headerOptions' => ['class' => 'min-width'],
	    ],
	    [
		    'label' => 'Discount Amount',
		    'attribute' => 'discount_amount',
		 	'headerOptions' => ['class' => 'min-width'],
	    ],
	  //   ['label' => 'Service Charge Amount',
	  //   'attribute' => 'service_charges_amount',
	 	// 'headerOptions' => ['class' => 'min-width'],
	  //   ],
	  //   ['label' => 'Tax Amount',
	  //   'attribute' => 'tax_amount',
	 	// 'headerOptions' => ['class' => 'min-width'],
	  //   ],		    
	    [
		    'label' => 'Paid Amount',
		    'attribute' => 'paid_amount',
		 	'headerOptions' => ['class' => 'min-width'],
	    ],    
	    [
		    'label' => 'Issue To',
		    'attribute' => 'contactName',
		  	'headerOptions' => ['class' => 'min-width'],
		 // 	//table contact does not insert contact_name
		    'value' => function($model) {	
				if (isset($model->billedTo)) {
					return $model->billedTo->contactName;
				}
		    }

	    ],		    
	  //   ['label' => 'Issue By',
	  //   'attribute' => 'issue_by',
	 	// 'headerOptions' => ['class' => 'min-width'],
	  //   ],		    
	  //   ['label' => 'Due Date',
	  //   'attribute' => 'due_date',
	 	// 'headerOptions' => ['class' => 'min-width'],
	  //   ],		    
	    [
		    'label' => 'Status',
		    'attribute' => 'status',
		    'headerOptions' => ['class' => 'min-width'],
		  	'filter' => ArrayHelper::getColumn(Invoice::statusOptions(), 'label'),
		    // 'value' => function($model)
		    // {
	    	// 	switch ($model->status) {
	    	// 		case $model::STATUS_ACTIVE:
	    	// 			return 'Status Active';
	    	// 		case $model::STATUS_PAID:
	    	// 			return 'Status Paid';
	    	// 		case $model::STATUS_PAID_MANUALLY:
	    	// 			return 'Status Paid Manually';
	    	// 		default:
	    	// 			return 'unknown';
	    	// 	}
		    // }

		    // format raw will increase performance for multipple records show
	    	'value' => function ($data) {
				return '<span class="label label-default '.$data->getStatusOption('cssClass').'">'.$data->getStatusOption('label', Invoice::STATUS_TEXT_DEFAULT).'</span>';
			},
			 'format' => 'html',
	    ],		   
	    [
		    'label' => 'Remark',
		    'headerOptions' => ['class' => 'min-width'],
		    'attribute' => 'remark',
	    ],		   
	    // ['label' => 'Created At',
	    // 'attribute' => 'created_at',
	    // 'contentOptions' => ['class' => 'text-right text-nowrap'],
	    // ],		    
	    // ['label' => 'Updated At',
	    // 'attribute' => 'updated_at',
	    // 'contentOptions' => ['class' => 'text-right text-nowrap'],
	    // ],
		[
			'class' => 'yii\grid\ActionColumn',
			'headerOptions' => ['class' => 'min-width'],
			'contentOptions' => ['class' => 'text-right text-nowrap'],
			'template'=>' {view}',
			'header' => 'Actions',
		],
	],
]) ?>
</div>
</div>
</div>