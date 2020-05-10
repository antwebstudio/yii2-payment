<?php 
use yii\helpers\Html;
use ant\payment\models\Payment;
use ant\file\models\FileAttachment;
?>
<div class="panel panel-default">
<div class="panel-heading">Payment Details</div>
<div class="panel-body">
    <?= \yii\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        // 'columns' => ['attribute' => 'data'],
        'columns' => [
            [
                'attribute' => 'id',
                'headerOptions' => ['class' => 'min-width'],
            ],
            [
                'attribute' => 'payment_gateway',
                'headerOptions' => ['class' => 'min-width'],
                'content' => function($model)
                {
                    $arr = explode("\\",$model->payment_gateway);
                    foreach ($arr as $key => $value) {
                    }
                    return $arr[$key];
                },
            ],
            [
                'attribute' => 'transaction_id',
                'headerOptions' => ['class' => 'min-width'],
            ],
            [
                'attribute' => 'ref_no',
                'headerOptions' => ['class' => 'min-width'],
            ],        
            /*[
                'attribute' => 'invoice_id',
                'headerOptions' => ['class' => 'min-width'],
                'content' => function($model)
                {  
                    return $model->invoice->formatted_id;
                },
            ],*/
            [
                'attribute' => 'merchant_code',
                'headerOptions' => ['class' => 'min-width'],
            ],
            [
                'attribute' => 'amount',
                'headerOptions' => ['class' => 'min-width'],
                'content' => function($model)
                {
                    // if ($model->status == 0) {
                    //     return 'Not yet paid';
                    // } elseif ($model->status == 1) return 'Paid';
                    return $model->currency . ' ' . $model->amount;
                },
            ],
            [
                'attribute' => 'remark',
                'headerOptions' => ['class' => 'min-width'],
            ],
            [
                'attribute' => 'status',
                'headerOptions' => ['class' => 'min-width'],
                'content' => function($model)
                {
                    // if ($model->status == 0) {
                    //     return 'Not yet paid';
                    // } elseif ($model->status == 1) return 'Paid';
                    return $model->error;
                },
            ],
            [
                'attribute' => 'attachments',
                'format' => 'raw',
                'value' => function ($model) {
                    $html = '';
                    foreach ($model->attachments as $image) {
                        $path = FileAttachment::getFirstUrl($image);
                        $html .= Html::a(Html::img($path, [
                            'height' => '120px',
                        ]), $path);
                    }
                    return $html;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['class' => 'text-right text-nowrap'],
                'headerOptions' => ['class' => 'min-width'],
                'template' => '{approve}',
                'buttons' => [
                    'approve' => function($url, $model) {
                        if ($model->isManual) {
                            if ($model->status == Payment::STATUS_SUCCESS) {
                                return Html::a('Unapprove', ['/payment/backend/payment/unapprove', 'id' => $model->id], ['data-method' => 'post', 'class' => 'btn btn-default']);
                            } else {
                                return Html::a('Approve', ['/payment/backend/payment/approve', 'id' => $model->id], ['data-method' => 'post', 'class' => 'btn btn-default']);
                            }                            
                        }
                    }
                ],
            ],
        ],
    ]); ?>
</div>
</div>