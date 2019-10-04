<?php
use yii\helpers\Url;

?>
<div class="alert-danger alert">
	Payment Error: <?= $response->getMessage() ?>
</div>

<a class="btn btn-primary" href="<?= Url::to($url) ?>">Back</a>
