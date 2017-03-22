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

        // Init
        $profit = 0;
        $commission = 0;
        $swap = 0;
        $balance = self::getBalance($html);

        // Elements position
        $posCommission = 4;
        $posSwap = 2;
        $posType = 2;
        $posProfit = 1;

        $data = [];

        // Generate the resulting data
        foreach ($rows as $tr) {
            $firstTd = $tr->firstChild;

            if (!is_numeric($firstTd->nodeValue)) {
                continue;
            }

            $countChild = $tr->childNodes->length;

            $typeTd = $tr->childNodes[$posType];
            $lastTd = $tr->childNodes[$countChild - $posProfit];
            $lastTdValue = self::clearValue($lastTd->nodeValue);

            if ($typeTd->nodeValue !== 'balance' && is_numeric($lastTdValue)) {
                $commissionTd = $tr->childNodes[$countChild - $posCommission];
                $swapTd = $tr->childNodes[$countChild - $posSwap];

                $curProfit = (float) $lastTdValue;
                $curCommission = abs((float) self::clearValue($commissionTd->nodeValue));
                $curSwap = abs((float) self::clearValue($swapTd->nodeValue));

                $profit += $curProfit;
                $commission += $curCommission;
                $swap += $curSwap;
                $balance += $curProfit - $curCommission - $curSwap;

                $data['label'][] = '"'.$firstTd->nodeValue.'"';
                $data['profit'][] = $profit;
                $data['commission'][] = $commission;
                $data['swap'][] = $swap;
                $data['balance'][] = $balance;
            }
        } // end foreach ($rows as $tr)

        return $data;
    }

    /**
     * @param \phpQueryObject $html
     *
     * @return mixed
     */
    private static function getBalance(\phpQueryObject $html)
    {
        $balance = 0;

        $depositLabel = $html->find('td:contains("Deposit/Withdrawal")');
        foreach ($depositLabel as $label) {
            $childNodes = $label->parentNode->childNodes;
            foreach ($childNodes as $childNode) {
                $clearValue = self::clearValue($childNode->nodeValue);
                if (is_numeric($clearValue)) {
                    $balance = $clearValue;
                    break;
                }
            }
        }

        return $balance;
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
