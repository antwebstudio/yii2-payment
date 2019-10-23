<?php
use yii\grid\GridView;
use yii\helpers\Html;
use ant\widgets\PhotoSwipe\PhotoSwipe;
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'transaction_id',
        'amount',
        'paid_at',
        'created_at',
        [
            'attribute' => 'attachments',
            'format' => 'raw',
            'value' => function($model) {
                if (isset($model->attachments[0])) {
                    $url = $model->attachments[0]['base_url'].'/'.$model->attachments[0]['path'];
                    $img = Html::img($url, ['height' => 100]);

                    return Html::tag('div', Html::tag('figure', Html::a($img, $url, ['data-toggle' => 'photo-swipe'])), ['class' => 'my-gallery']); // my-gallery class is needed for the PhotoSwipe widget to work.
                }
            }
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{approve}',
            'buttons' => [
                'approve' => function($url, $model) {
                    if ($model->is_valid) {
                        return Html::a('Unapprove', ['unapprove', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']);
                    } else {
                        return Html::a('Approve', ['approve', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']);
                    }
                }
            ],
        ],
    ],
]) ?>

<?= PhotoSwipe::Widget(['selector' => '[data-toggle=photo-swipe]']) ?>