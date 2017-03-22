<?php

namespace app\controllers;

use yii\web\UploadedFile;
use app\models\ReportForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

class SiteController extends Controller
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new ReportForm();
        $chartData = [];

        if (Yii::$app->request->isPost) {
            $model->reportFile = UploadedFile::getInstance($model, 'reportFile');
            $chartData = $this->handleReport($model->reportFile->tempName);
        }

        return $this->render('index', [
            'model'     => $model,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Generate data for line chart.
     *
     * @param string $file File name.
     *
     * @return array Data for chart.
     */
    private function handleReport($file)
    {
        $html = \phpQuery::newDocumentFileHTML($file);
        $rows = $html->find('tr');

        $profit = 0;
        $commission = 0;
        $balance = 0;

        // Commission position in table
        $posCommission = 4;

        $data = [];

        // Generate the resulting data
        foreach ($rows as $tr) {
            $firstTd = $tr->firstChild;

            if (!is_numeric($firstTd->nodeValue)) {
                continue;
            }

            $countChild = $tr->childNodes->length;

            $typeTd = $tr->childNodes[2];
            $lastTd = $tr->childNodes[$countChild - 1];
            $lastTdValue = self::clearValue($lastTd->nodeValue);

            if ($typeTd->nodeValue !== 'balance' && is_numeric($lastTdValue)) {
                $commissionTd = $tr->childNodes[$countChild - $posCommission];

                $curProfit = (float) $lastTdValue;
                $curCommission = abs((float) self::clearValue($commissionTd->nodeValue));

                $profit += $curProfit;
                $commission += $curCommission;
                $balance += $curProfit - $curCommission;

                $data['label'][] = '"'.$firstTd->nodeValue.'"';
                $data['profit'][] = $profit;
                $data['commission'][] = $commission;
                $data['balance'][] = $balance;
            }
        } // end foreach ($rows as $tr)

        return $data;
    }

    /**
     * Return clear value from spaces.
     *
     * @param string $value Value for clear.
     *
     * @return mixed Clear value from spaces.
     */
    private static function clearValue($value)
    {
        return str_replace(' ', '', $value);
    }
}
