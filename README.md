# ServiceDesk

Полностью рабочая PHP/MySQL система ServiceDesk с RBAC, заявками, чатами, БЗ, новостями, брендингом, OIDC SSO и интеграцией 1С.

## Требования

- PHP 8.1+
- MySQL 8+
- Расширения PHP: `pdo_mysql`, `openssl`, `curl`.

## Установка

1. Создайте базу данных и пользователя:

```sql
CREATE DATABASE servicedesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sd_user'@'localhost' IDENTIFIED BY 'strongpassword';
GRANT ALL PRIVILEGES ON servicedesk.* TO 'sd_user'@'localhost';
```

2. Примените схему:

```bash
mysql -u sd_user -p servicedesk < app/Migrations/schema.sql
```

3. Установите переменные окружения (пример для Linux/macOS):

```bash
export APP_BASE_URL="http://localhost:8000"
export DB_DSN="mysql:host=localhost;dbname=servicedesk;charset=utf8mb4"
export DB_USER="sd_user"
export DB_PASSWORD="strongpassword"
```

4. (Опционально) Включите SSO OIDC:

```bash
export OIDC_ENABLED=true
export OIDC_DISCOVERY_URL="https://login.jinr.ru/.well-known/openid-configuration"
export OIDC_CLIENT_ID="<client_id>"
export OIDC_CLIENT_SECRET="<client_secret>"
export OIDC_REDIRECT_URI="http://help.jinr.ru/oidc/callback"
export OIDC_SCOPES="openid profile email"
export OIDC_ISSUER="https://login.jinr.ru"
```

5. Запустите сервер разработки:

```bash
php -S 0.0.0.0:8000 -t public
```

Откройте `http://localhost:8000`.

## Первичный вход

- Создайте первого пользователя через форму регистрации. Для назначения админских прав обновите роль в БД:

```sql
UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
```

## RBAC и кабинеты

- **Администратор**: управление пользователями, брендингом, БЗ, новостями, интеграцией 1С, доступ ко всем заявкам.
- **Техспециалист (agent)**: работа с назначенными заявками, участие в чатах, ограниченное управление пользователями.
- **Пользователь**: создание и ведение собственных заявок, чат, доступ к БЗ.

## SSO (OpenID Connect) для help.jinr.ru

Система реализует стандарт OIDC, совместимый с документацией:
- https://login.jinr.ru/info.html
- https://login.jinr.ru/login/info2.html

### Общее поведение

- Кнопка **«Войти через SSO»** на экране входа запускает OIDC flow.
- После получения `code` выполняется обмен на `id_token`/`access_token`.
- Подпись `id_token` валидируется по JWKS. Атрибуты (ФИО, email, подразделение) маппятся в профиль.
- Если пользователь не найден — создаётся новая запись с ролью `user`.
- Опционально отключается локальный вход через админ-конфиг (переменная окружения `OIDC_ENABLED=false`).

### Настройка (шаги)

1. Зарегистрируйте приложение в SSO (login.jinr.ru). В качестве redirect укажите:
   - `https://help.jinr.ru/oidc/callback`
2. Заполните параметры из кабинета клиента:
   - `OIDC_DISCOVERY_URL`
   - `OIDC_CLIENT_ID`
   - `OIDC_CLIENT_SECRET`
   - `OIDC_ISSUER`
3. Убедитесь, что домен `help.jinr.ru` доступен из SSO и настроен HTTPS.

## Интеграция с 1С (Итилиум/1С:Предприятие)

Интеграция реализована через очередь `sync_queue` и сервис `OneCService`:

- Выгрузка заявок/БЗ/справочников настраивается переменными:

```bash
export ONEC_ENABLED=true
export ONEC_ITILIUM_URL="https://1c.example.com"
export ONEC_ITILIUM_TOKEN="<token>"
export ONEC_SYNC_TICKETS=true
export ONEC_SYNC_DIRECTORIES=true
export ONEC_SYNC_KNOWLEDGE=true
```

- Запуск синхронизации из админ-панели или через cron:

```bash
php -r "require 'vendor/autoload.php'; (new App\\Services\\OneCService(App\\Core\\DB::conn((require 'app/Config/config.php')['db']), (require 'app/Config/config.php')['one_c']))->processQueue();"
```

## Локализация RU/EN

- Переключатель языка доступен на главном экране и применяется динамически.
- Все строки вынесены в `app/Resources/strings.php`.
- Слоган на главной печатается с эффектом машинки и авто-переключением RU/EN.

## API-контракты

В проекте реализованы HTTP маршруты для API:

- `POST /api/auth/sso/callback`
- `GET /api/tickets`, `POST /api/tickets`, `POST /api/tickets/update`
- `GET /api/tickets/messages`, `POST /api/tickets/messages`
- `GET /api/knowledge`, `POST /api/knowledge`, `POST /api/knowledge/update`
- `GET /api/news`, `POST /api/polls/vote`
- `POST /api/sync/1c/export`

## Безопасность

- Пароли хранятся в BCrypt.
- CSRF защита на всех формах.
- Ролевые проверки на UI и сервере.

## Запуск в продакшн

- Настройте Apache/Nginx с корнем `public/`.
- Укажите переменные окружения.
- Настройте HTTPS для домена `help.jinr.ru`.

## Примечание

Система поставляется без демо-данных и моков. Все функции работают с реальными данными БД и настроенными провайдерами.
