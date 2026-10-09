# App Contest Platform

Платформа для организации и управления онлайн-конкурсами приложений. Поддерживает викторины (quiz) и голосование (voting), систему лидербордов, загрузку медиафайлов и административную панель.

> **Разработано с помощью ИИ-агента** — весь код (бэкенд, фронтенд, тесты, инфраструктура) написан с использованием ИИ-инструментов в рамках итеративного процесса разработки.

## 🚀 Стек технологий

| Слой       | Технология                                                                 |
|------------|----------------------------------------------------------------------------|
| **Backend**| PHP 8.5, Laravel 13.7+, PostgreSQL 18                                     |
| **Frontend**| React 19.2+, Inertia.js v3.0, TypeScript 5.7, Vite 8                      |
| **UI**     | Ant Design v6.6.5+, Tailwind CSS 4.0, Radix UI, Lucide React              |
| **Auth**   | Laravel Fortify (passkeys, 2FA, LDAP)                                     |
| **DevOps** | Docker Compose (nginx + php-fpm + postgres), Laravel Sail                 |
| **Testing**| Pest PHP, PHPUnit 13, PHPStan/Larastan Level 13                           |

## 📋 Возможности

- **Конкурсы двух типов**: викторины с вопросами и голосование с подачами
- **Динамические формы**: `project_schema` — JSON-конфигурация полей для каждой подачи
- **Система голосований**: один голос за подачу на пользователя, мгновенные лидерборды
- **Викторины**: одиночный/множественный выбор, планирование показа (`show_from` / `show_until`)
- **Медиа**: загрузка файлов, превью изображений/видео/PDF, полиморфная привязка к подачам
- **Аутентификация**: passkeys (WebAuthn), двухфакторная, LDAP, верификация email
- **Админ-панель**: полный CRUD конкурсов, подач, викторин и медиафайлов

## 🏗 Архитектура

Проект следует паттерну **Thin Controllers + Service Layer**:

```
┌─────────────────────────────────────────────────────┐
│  Controllers (Inertia / Requests)                   │
│  ← валидация, ← передача данных в сервисы           │
├─────────────────────────────────────────────────────┤
│  Services (ContestService)                          │
│  ← бизнес-логика, ← работа с БД                     │
├─────────────────────────────────────────────────────┤
│  DTOs (readonly classes)                            │
│  ← структурированные данные для фронтенда           │
├─────────────────────────────────────────────────────┤
│  Models (Eloquent + Attributes)                     │
│  ← #[Fillable], #[Hidden], HasFactory               │
└─────────────────────────────────────────────────────┘
```

### Модели

| Модель | Описание |
|---|---|
| `Contest` | Контейнер конкурса (тип, статус, даты, схема полей) |
| `ContestEntry` | Подача в голосовании (с автором, полями, голосами) |
| `ContestVote` | Голос пользователя (unique: user + contest + entry) |
| `QuizEntry` | Вопрос викторины (sort_order, планирование) |
| `QuizAnswer` | Ответ пользователя (is_correct, answered_at) |
| `Project` | Подача проекта пользователя с кастомными полями |
| `Media` | Файл (полиморфный: contest / entry) |
| `User` | Пользователь (Fortify + LDAP + passkeys) |

## 🛠 Быстрый старт

### Предварительные требования

- Docker & Docker Compose
- Node.js 20+
- PHP 8.5+

### Установка

```bash
# 1. Клонировать репозиторий
git clone <repo-url>
cd app-contest-platform

# 2. Настроить окружение
cp .env.example .env

# 3. Запустить Docker-контейнеры
docker compose up -d postgres

# 4. Установить PHP-зависимости
docker compose run --rm php-cli composer install

# 5. Настроить базу данных
docker compose run --rm php-cli php artisan key:generate
docker compose run --rm php-cli php artisan migrate

# 6. Установить JS-зависимости и собрать
npm install
npm run build

# 7. Запустить dev-сервер
docker compose up -d nginx php-fpm
npm run dev
```

Платформа доступна по адресу: **http://localhost:8081**

### Полный скрипт установки

```bash
docker compose run --rm php-cli composer setup
npm install
npm run build
```

## 📁 Структура проекта

```
├── app/
│   ├── DTOs/              # Data Transfer Objects (readonly)
│   ├── Enums/             # ContestType (QUIZ | VOTING)
│   ├── Http/
│   │   ├── Controllers/   # Thin controllers
│   │   ├── Middleware/    # Appearance, Inertia
│   │   └── Requests/      # FormRequest валидация
│   ├── Models/            # Eloquent модели
│   ├── Observers/         # ContestObserver
│   └── Services/          # Бизнес-логика
├── database/
│   ├── migrations/        # 20 миграций
│   ├── factories/         # Фабрики для тестов
│   └── seeders/
├── resources/js/
│   ├── pages/             # Inertia-компоненты
│   │   ├── contests/      # Public + Admin страницы конкурсов
│   │   ├── entries/       # CRUD подач
│   │   ├── quizzes/       # Викторины (builder, take, result)
│   │   ├── media/         # Медиа-менеджер
│   │   ├── settings/      # Профиль, безопасность, внешний вид
│   │   └── auth/          # Логин
│   └── routes/            # Wayfinder route helpers
├── routes/
│   ├── web.php            # Все Inertia-маршруты
│   └── settings.php       # Настройки пользователя
├── tests/
│   ├── Unit/Models/       # Тесты моделей (112 тестов)
│   └── Feature/Auth/      # Тесты аутентификации
├── docker/                # Dockerfile для php-fpm, php-cli, nginx
└── docs/
    ├── specification.md   # Функциональная спецификация
    └── database.md        # ER-диаграмма БД
```

## 🧪 Тестирование

```bash
# Все тесты
docker compose run --rm php-cli php artisan test

# Только модели
docker compose run --rm php-cli php artisan test --filter=Models

# Только аутентификация
docker compose run --rm php-cli php artisan test --filter=Auth

# Статический анализ (PHPStan Level 13)
docker compose run --rm php-cli php artisan types:check

# Проверка стиля (Pint)
docker compose run --rm php-cli php artisan lint:check

# Полный CI
docker compose run --rm php-cli php artisan ci:check
```

## 🔧 Конфигурация

### Переменные окружения (.env)

| Переменная | По умолчанию | Описание |
|---|---|---|
| `DB_CONNECTION` | `sqlite` | Драйвер БД |
| `DB_DATABASE` | `:memory:` | Путь к БД |
| `SESSION_DRIVER` | `database` | Драйвер сессий |
| `QUEUE_CONNECTION` | `database` | Драйвер очередей |
| `CACHE_STORE` | `database` | Драйвер кэша |
| `MAX_UPLOAD_SIZE` | `104857600` | Макс. размер файла (100MB) |

### Docker

| Сервис | Порт | Описание |
|---|---|---|
| `nginx` | 8081 → 80 | Reverse proxy |
| `php-fpm` | — | Laravel-приложение |
| `php-cli` | — | CLI (artisan, тесты) |
| `postgres` | 5433 → 5432 | База данных |

## 📖 Документация

- [Функциональная спецификация](docs/specification.md) — глоссарий, роли, UI-компоненты
- [Схема базы данных](docs/database.md) — ER-диаграмма, описания таблиц
- [Инструкции для ИИ-ассистента](ai-instructions.md) — правила разработки и код-стайл

## 📝 Команды разработки

```bash
# Dev-сервер (Laravel + Vite)
composer run dev

# Сборка для продакшена
npm run build

# TypeScript-чек
npm run types:check

# Автофикс стиля (Pint)
composer run lint:fix

# Автофикс TS
npm run check:fix
```

## 📄 Лицензия

MIT
