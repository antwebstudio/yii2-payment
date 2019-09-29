<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
?>

<?php $form = ActiveForm::begin() ?>
    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'bank')->radioList([
        'cimb' => 'CIMB Bank',
        'maybank' => 'Maybank',
        'rhb' => 'RHB',
    ]) ?>

    <?= $form->field($model, 'amount')->label('Topup Amount') ?>

    <?= $form->field($model, 'date')->widget(DatePicker::className(), [
        //'options' => ['placeholder' => $model->getModel('event')->attributeLabels()['start_date']],
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'pluginOptions' =>
        [
            'autoclose' => true,
            'todayHighlight' => true,
            'endDate' => "0d",
            'format' => 'yyyy-mm-dd'
        ]
    ])->label('Date Bank In') ?>

    <?= $form->field($model, 'time')->widget(TimePicker::className(), [
        //'options' => ['placeholder' => $model->getModel('event')->attributeLabels()['start_time']],
        'pluginOptions' => [
            'showSeconds' => false,
            'minuteStep' => 15,
            'secondStep' => 5,
            'defaultTime' => false,
        ]
    ])->label('Time Bank In') ?>

    <?= $form->field($model, 'reference') ?>
    
    <?= $form->field($model, 'attachment')->widget(
        \trntv\filekit\widget\Upload::className(),
        [
            'url' => ['/file/file-storage-item/upload'],
            'sortable' => true,
            'maxFileSize' => 10000000, // 10 MiB
            'maxNumberOfFiles' => 1,
            'acceptFileTypes' => new \yii\web\JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),
        ]);
    ?>    

    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end() ?>