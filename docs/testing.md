# Тесты

Тесты написаны на **Pest 5** (поверх PHPUnit 13) с плагином `pest-plugin-laravel`.

## Запуск

```bash
docker exec -w /var/www project_app composer test        # config:clear + php artisan test
docker exec -w /var/www project_app vendor/bin/pest      # напрямую
docker exec -w /var/www project_app vendor/bin/pest --filter "календар"   # по названию
```

## База данных

- Тесты работают с **отдельной MySQL-базой `lara_docker_testing`** в контейнере `db` (см. `phpunit.xml`).
  SQLite не подходит: сортировка списка заказов использует MySQL-функции `FIELD()` и `UNIX_TIMESTAMP()`.
- Перед первым запуском базу нужно создать один раз:

  ```bash
  docker exec project_db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS lara_docker_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
  ```

- Feature-тесты используют `RefreshDatabase`: схема тестовой базы пересоздаётся, каждый тест идёт в транзакции.
- **Защита рабочей базы:** `Tests\TestCase::setUpTraits()` останавливает тесты, если имя базы не заканчивается на `_testing`
  (например, если закэширован конфиг: тогда выполните `php artisan config:clear`).

## Что покрыто

| Файл | Что проверяет |
|---|---|
| `Unit/MoneyHelpersTest` | `rubToKop`, `kopToRub`, `kopToRubView`, `kopToRubShort` |
| `Unit/OrderModelTest` | долг заказа, приведение сумм к `int`, названия и цвета статусов |
| `Unit/Rules/*` | правила `EqualsAmount` и `InWhitelist` |
| `Feature/AuthTest` | вход/выход, редиректы гостя, команда создания супер-админа, доступ к админ-разделам |
| `Feature/OrderTest` | список и фильтры (статус, товар, даты), создание (в т. ч. с новым клиентом), валидация сумм, редактирование, мягкое удаление, AJAX-смена статуса |
| `Feature/OrderCalendarTest` | сетка месяца и недели, сумма дня без отмен, навигация, заказы удалённых клиентов |
| `Feature/ClientTest` | CRUD клиентов, нормализация телефона, поиск по телефону, статистика карточки |
| `Feature/Dashboard/*` | все блоки главной на заранее известных данных: плашки и изменения к прошлому месяцу, выручка за год, источники, товары, повторные клиенты, дни недели, долги |
| `Feature/TgOrdersNotificationTest` | утреннее сообщение в Telegram (через `Http::fake`), выключенные уведомления, ошибка API, расписание |
| `Feature/SettingsTest` | настройки Telegram, товары и площадки |

## Хелперы

- `$this->actingAsUser()`, `$this->actingAsSuperAdmin()` — в `tests/TestCase.php`.
- `composeBlock('stats', ['from' => ..., 'to' => ...])` — в `tests/Pest.php`: отрисовывает блок главной
  и возвращает данные его view composer.
- `User::factory()->superAdmin()` — состояние фабрики для супер-администратора.
