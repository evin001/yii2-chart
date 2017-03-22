<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
$this->title = \Yii::t('app', 'Report generation');
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'reportFile')->fileInput()->label(\Yii::t('app', 'Report file')) ?>
    <button class="btn btn-default" type="submit"><?= \Yii::t('app', 'Submit') ?></button>

<?php ActiveForm::end() ?>

<?php if ($chartData) { ?>
    <h1><?= \Yii::t('app', 'Balance sheet') ?></h1>
    <canvas id="lineChart" height="500" width="500"></canvas>

    <?=Html::jsFile('@web/js/Chart.min.js')?>
    <script>
        var ctx = document.getElementById("lineChart");

        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?=implode(', ', $chartData['label'])?>],
                datasets: [
                    {
                        label: "<?= \Yii::t('app', 'Profit') ?>",
                        fill: false,
                        backgroundColor: "rgba(75, 192, 192, 0.4)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        data: [<?=implode(', ', $chartData['profit'])?>],
                        radius: 2
                    },
                    {
                        label: "<?= \Yii::t('app', 'Commission') ?>",
                        fill: false,
                        backgroundColor: "rgba(255, 99, 132, 0.4)",
                        borderColor: "rgba(255, 99, 132, 1)",
                        data: [<?=implode(', ', $chartData['commission'])?>],
                        radius: 2
                    },
                    {
                        label: "<?= \Yii::t('app', 'Balance') ?>",
                        fill: false,
                        backgroundColor: "rgba(153, 102, 255, 0.4)",
                        borderColor: "rgba(153, 102, 255, 1)",
                        data: [<?=implode(', ', $chartData['balance'])?>],
                        radius: 2
                    },
                    {
                        label: "<?= \Yii::t('app', 'Swap') ?>",
                        fill: false,
                        backgroundColor: "rgba(54, 162, 235, 0.4)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        data: [<?=implode(', ', $chartData['swap'])?>],
                        radius: 2
                    }
                ]
            },
            options: {}
        });
    </script>
<?php } elseif ($isPost) { ?>
    <h2><?= \Yii::t('app', 'Error') ?></h2>
    <div class="alert alert-danger"><?= \Yii::t('app', 'Data not found') ?></div>
<?php } ?>