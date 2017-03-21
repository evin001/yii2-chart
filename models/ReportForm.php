<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;
use Yii;

class ReportForm extends Model
{
    /**
     * Report file.
     *
     * @var UploadedFile
     */
    public $reportFile;

    public function rules()
    {
        return [
            [['reportFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'html, htm'],
        ];
    }
}