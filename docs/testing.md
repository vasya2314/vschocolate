# Тесты

Тесты написаны на **Pest 5** (поверх PHPUnit 13) с плагином `pest-plugin-laravel`.

## Запуск

```bash
docker exec -w /var/www project_app composer test        # config:clear + php artisan test
docker exec -w /var/www project_app vendor/bin/pest      # напрямую
docker exec -w /var/www project_app vendor/bin/pest --filter "календар"   # по названию
```

## GitHub Actions

`.github/workflows/tests.yml` запускает тесты на каждый pull request, на пуш в `master` и вручную (Run workflow).
- Своя временная **MySQL 8.0** (service container) с базой `lara_docker_testing` — живёт только во время прогона.
- PHP 8.4, `composer install`, `.env` из `.env.example` + `php artisan key:generate`, затем `composer test`.
- Прод-секретов в workflow нет; фронтенд не собирается — тесты вызывают `withoutVite()`.

## База данных

- Тесты работают с **отдельной MySQL-базой `lara_docker_testing`** в контейнере `db` (см. `phpunit.xml`).
  `RefreshDatabase` **пересоздаёт её схему** (удаляет все таблицы), каждый тест идёт в транзакции.
- Базу нужно создать один раз на машине разработчика (на проде — не нужно):

  ```bash
  docker exec project_db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS lara_docker_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
  ```

- Код приложения при этом переносим между MySQL и SQLite (без `FIELD()`, `UNIX_TIMESTAMP()`, `MONTH()`):
  при желании тесты можно перевести на SQLite в памяти, поменяв две строки в `phpunit.xml`.

## Как тесты защищены от рабочих баз

| Защита | Что даёт |
|---|---|
| `force="true"` у `DB_CONNECTION` / `DB_DATABASE` в `phpunit.xml` | значения из `phpunit.xml` важнее `.env`: тесты не возьмут базу из `.env` |
| `Tests\TestCase::setUpTraits()` | до пересоздания схемы проверяет имя базы: не `*_testing` (и не SQLite в памяти) — тесты останавливаются |
| `composer test` начинает с `config:clear` | при закэшированном конфиге (`config:cache`) Laravel игнорирует `phpunit.xml`; сброс кэша это убирает, а если кэш всё же есть — сработает проверка выше |
| Деплой (`.github/workflows/deploy.yml`) | папка `tests/` не попадает в релиз (`--exclude='./tests'`), Pest/PHPUnit на прод не ставятся (`composer install --no-dev`) — на проде тесты запустить нечем |

**Правила:**
- никогда не прописывайте доступ к прод-базе в локальный `.env` и не создавайте `.env.testing` с прод-доступом;
- не запускайте тесты на сервере (там их и нет);
- если тесты появятся в CI (GitHub Actions) — только со своей временной MySQL (service container), без прод-секретов;
- перед деплоем с миграциями делайте резервную копию прод-базы — миграции идут на прод при каждом деплое.

## Что покрыто

| Файл | Что проверяет |
|---|---|
| `Unit/MoneyHelpersTest` | `rubToKop`, `kopToRub`, `kopToRubView`, `kopToRubShort` |
| `Unit/OrderModelTest` | долг заказа, приведение сумм к `int`, названия и цвета статусов |
| `Unit/Rules/*` | правила `EqualsAmount` и `InWhitelist` |
| `Feature/AuthTest` | вход/выход, редиректы гостя, команда создания супер-админа, доступ к админ-разделам |
| `Feature/OrderTest` | список: сортировка, фильтры (статус, товар, даты); создание (в т. ч. с новым клиентом), валидация сумм, редактирование, мягкое удаление, AJAX-смена статуса |
| `Feature/OrderCalendarTest` | сетка месяца и недели, сумма дня без отмен, навигация, заказы удалённых клиентов |
| `Feature/ClientTest` | CRUD клиентов, нормализация телефона, поиск по телефону, статистика карточки |
| `Feature/Dashboard/*` | все блоки главной на заранее известных данных: плашки и изменения к прошлому месяцу, выручка за год, источники, товары, повторные клиенты, дни недели, долги |
| `Feature/TgOrdersNotificationTest` | утреннее сообщение в Telegram (через `Http::fake`), выключенные уведомления, ошибка API пишется в лог, расписание |
| `Feature/SettingsTest` | настройки Telegram, товары и площадки |

## Хелперы

- `$this->actingAsUser()`, `$this->actingAsSuperAdmin()` — в `tests/TestCase.php`.
- `composeBlock('stats', ['from' => ..., 'to' => ...])` — в `tests/Pest.php`: отрисовывает блок главной
  и возвращает данные его view composer.
- `User::factory()->superAdmin()` — состояние фабрики для супер-администратора.
