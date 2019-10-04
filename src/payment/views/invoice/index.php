<?php
use yii\helpers\Html;

$this->title = 'Invoice';
$this->params['title'] = $this->title;

$this->context->layout = '//member-dashboard';
$dataProvider->pagination->pageSize = 10;
?>

<?= \yii\grid\GridView::widget([
	'dataProvider' => $dataProvider,
	'columns' => [
		[
			'attribute' => 'issue_date',
			'label' => 'Date',
			'format' => 'date',
		],
		[
			'attribute' => 'formattedId',
			'label' => 'Number',
		],
		[
			'attribute' => 'netTotal',
			'label' => 'Total Amount',
		],
		[
			'attribute' => 'statusHtml',
			'format' => 'html',
			'label' => 'Status',
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'template' => '{view}',
			'buttons' => [
				'view' => function($url, $model) {
					return Html::a('View', $model->privateRoute, ['class' => 'btn btn-sm btn-secondary']);
				}
			],
		],
		[
			'visible' => YII_DEBUG,
			'attribute' => 'billed_to',
			'label' => 'Billed To (Debug)',
		],
		[
			'visible' => YII_DEBUG,
			'attribute' => 'issue_to',
			'label' => 'Issue To (Debug)',
		],
		[
			'visible' => YII_DEBUG,
			'attribute' => 'organization_id',
			'label' => 'Organization (Debug)',
		],
	],
]) ?>