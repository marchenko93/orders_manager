<?php

namespace app\modules\orders_list;

use Yii;

class Module extends \yii\base\Module
{
    public $layout = '@app/modules/orders_list/views/layouts/main.php';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/orders_list/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/modules/orders_list/messages',
            'fileMap' => [
                'modules/orders_list/list' => 'list.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/orders_list/' . $category, $message, $params, $language);
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
