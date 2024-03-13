<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => $_ENV['DATABASE_DSN'],
    'username' => $_ENV['MYSQL_USERNAME'],
    'password' => $_ENV['MYSQL_PASSWORD'],
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
