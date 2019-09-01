<?php
use yii\bootstrap\Alert;

?>

<div class="container">

<?php Alert::begin([
	'closeButton' => false,
    'options' => [
        'class' => 'alert-danger',
    ],
]) ?>

	Payment Error: <?= $response->getMessage() ?>

<?php Alert::end() ?>

<a class="btn btn-primary" href="<?= $url ?>">Back</a>

</div>