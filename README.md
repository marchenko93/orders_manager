1) Скопируйте дампы для создания схемы и данных в директорию /docker/db/dumps и добавить к именам дампов префиксы, чтобы они выполнились в правильном порядке
a_test_db_structure.sql
b_test_db_data.sql

2) Создайте копию файл .env.example и переименуйте ее в .env.
Добавьте эти значения для переменных окружения с пустыми значениями
DATABASE_DSN='mysql:host=db;dbname=orders_manager'
DATABASE_USERNAME='sergey'
DATABASE_PASSWORD='ks2905'

3) Запустите команду docker-compose run -w /usr/src/app/application php-cli composer install
