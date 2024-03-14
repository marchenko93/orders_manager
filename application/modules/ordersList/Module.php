<?php

namespace ordersList;

use Yii;

class Module extends \yii\base\Module
{
    public $layout = '@ordersList/views/layouts/main.php';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/ordersList/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@ordersList/messages',
            'fileMap' => [
                'modules/ordersList/list' => 'list.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/ordersList/' . $category, $message, $params, $language);
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
