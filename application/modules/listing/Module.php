<?php

namespace app\modules\listing;

use Yii;

class Module extends \yii\base\Module
{
    public $layout = '@app/modules/listing/views/layouts/main.php';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/listing/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/modules/listing/messages',
            'fileMap' => [
                'modules/listing/list' => 'list.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/listing/' . $category, $message, $params, $language);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $lang = Yii::$app->request->get('lang');
        if ($lang) {
            Yii::$app->language = $lang;
        }

        return true;
    }
}