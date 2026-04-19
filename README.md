# KharCar — Приложение для голосования за автомобили

Веб-приложение на Laravel 12 для голосования за фотографии аукционных автомобилей.

## Возможности

### Страница голосования
- Выпадающий список всех доступных брендов автомобилей
- После выбора бренда отображаются две разные случайные фотографии этого бренда
- Фотографии не повторяются в цикле голосования (кэширование показанных)
- Пользователь выбирает: «Левая нравится больше» / «Правая нравится больше»
- После голосования сразу показывается новая случайная пара (без перезагрузки страницы)
- Зум фотографий при наведении курсора
- Просмотр фото в полном размере с подробной информацией об автомобиле

### Страница статистики
- Фильтры: бренд автомобиля + диапазон годов (от — до)
- Два режима отображения: карточки (9 шт на странице) и таблица
- Таблица с карточками автомобилей (фото + данные), количеством голосов за каждую
- Общая сумма голосов по всем автомобилям выбранного бренда в указанном диапазоне лет
- Пагинация для обоих режимов отображения
- Адаптивная таблица для мобильных устройств (скрытие второстепенных столбцов)

## Архитектура

### Backend (Laravel 12, PHP 8.4)

**Модели:**
- `Car` — модель автомобиля
- `Vote` — модель голоса

**Сервисный слой (бизнес-логика):**
- `VotingService` — получение пары автомобилей, голосование, проверка дубликатов, кэширование
- `StatisticsService` — фильтрация, пагинация, подсчёт голосов
- `ModelService` — работа со списком моделей

**Репозитории (доступ к данным):**
- `CarRepository` — операции с автомобилями
- `VoteRepository` — операции с голосами
- `ModelRepository` — операции с моделями

**DTO (Spatie Laravel Data):**
- `CarData` — данные автомобиля для передачи в API
- `CarPairData` — пара автомобилей для голосования
- `VoteResultData` — результат голосования
- `StatisticsData` — данные статистики с пагинацией
- `ModelData` — данные модели (бренд + модель + количество)

**Контроллеры (API):**
- `VotingController` — API для голосования (`/api/voting/pair`, `/api/voting/{id}`)
- `CarApiController` — API для автомобилей и статистики (`/api/cars`, `/api/cars/statistics`)
- `ModelController` — API для моделей (`/api/models`)

**Политики:**
- `CarPolicy` — политики доступа к автомобилям
- `VotePolicy` — политики доступа к голосам

**Сидеры:**
- `CarSeeder` — импорт данных из JSON файлов (1497 автомобилей)

### Frontend (Vue 3, TypeScript)

**Типы (TypeScript):**
- `types/index.ts` — базовые интерфейсы (Car, Vote, CarPair, PaginatedResponse)
- `types/records.ts` — Record типы для строгой типизации состояний
- `types/ModelRecord.ts` — тип для модели

**Компоненты:**
- `App.vue` — корневой компонент с навигацией и иконками
- `VotingPage.vue` — страница голосования с фильтром по бренду
- `StatisticsPage.vue` — страница статистики с фильтрами и переключением вида
- `CarCard.vue` — универсальная карточка автомобиля (используется везде)
- `TableView.vue` — таблица автомобилей для режима статистики
- `Pagination.vue` — компонент пагинации (липкий, адаптивный)
- `Alert.vue` — всплывающие уведомления (success, error, warning, info)
- `ImageViewer.vue` — модальное окно для просмотра фото в полном размере

**Хранилища (Pinia):**
- `carStore.ts` — управление состоянием голосования (пара, модели, фильтры)
- `statisticsStore.ts` — управление состоянием статистики (данные, фильтры, пагинация)
- `modelStore.ts` — управление списком моделей

**Маршрутизация:**
- Vue Router с навигационными хуками

### База данных (MariaDB)

**Таблица `cars`**

| Поле | Тип | Описание |
|------|-----|----------|
| id | BIGINT UNSIGNED | Первичный ключ |
| image | VARCHAR(255) | Путь к изображению (уникальный) |
| make | VARCHAR(100) | Бренд автомобиля (BMW, Audi, Ford) |
| model | VARCHAR(100) | Модель автомобиля (X5, A4, Mustang) |
| year | INT | Год выпуска |
| odometer | INT | Пробег |
| units | VARCHAR(10) | Единицы измерения пробега (миль) |
| engine | VARCHAR(50) | Тип двигателя |
| transmission | VARCHAR(50) | Тип КПП |
| color | VARCHAR(50) | Цвет |
| brand | VARCHAR(100) | Бренд (дублирует make для совместимости) |
| winning_bid_amount | DECIMAL(10,2) | Сумма выигрышной ставки |
| winning_bid_location | VARCHAR(100) | Локация торгов |
| votes_count | INT | Количество голосов (денормализовано) |
| created_at | TIMESTAMP | Дата создания |
| updated_at | TIMESTAMP | Дата обновления |

**Индексы:**
- PRIMARY KEY (id)
- UNIQUE (image) — уникальность изображений
- INDEX (make, model) — быстрая фильтрация по бренду и модели
- INDEX (year) — быстрая фильтрация по году

**Таблица `votes`**

| Поле | Тип | Описание |
|------|-----|----------|
| id | BIGINT UNSIGNED | Первичный ключ |
| car_id | BIGINT UNSIGNED | Внешний ключ на cars |
| voter_id | VARCHAR(255) | Идентификатор голосующего (IP адрес) |
| created_at | TIMESTAMP | Дата создания |
| updated_at | TIMESTAMP | Дата обновления |

**Ограничения:**
- PRIMARY KEY (id)
- UNIQUE (car_id, voter_id) — защита от повторного голосования за один автомобиль
- FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE

## Установка

### Требования
- Docker и Docker Compose
- PHP 8.4+
- Node.js 18+
- MariaDB 11+

### Развёртывание через Docker

1. Клонировать репозиторий:
```bash
git clone <repository-url>
cd car-voting-app
```

2. Создать файл окружения:
```bash
cp .env.example .env
```

3. Запустить Docker-контейнеры:
```bash
docker-compose up -d --build
```

4. Установить зависимости Laravel:
```bash
docker-compose exec app composer install
```

5. Сгенерировать ключ приложения:
```bash
docker-compose exec app php artisan key:generate
```

6. Выполнить миграции:
```bash
docker-compose exec app php artisan migrate
```

7. Импортировать данные из JSON:
```bash
docker-compose exec app php artisan db:seed
```

8. Собрать frontend:
```bash
docker-compose run --rm node npm install
docker-compose run --rm node npm run build
```

### Локальная разработка

1. Установить зависимости PHP:
```bash
composer install
```

2. Установить зависимости Node.js:
```bash
npm install
```

3. Настроить окружение:
```bash
cp .env.example .env
php artisan key:generate
```

4. Выполнить миграции:
```bash
php artisan migrate
```

5. Импортировать данные:
```bash
php artisan db:seed
```

6. Собрать frontend:
```bash
npm run build
```

7. Запустить сервер:
```bash
php artisan serve --port=8081
```

## Доступ

- **Приложение:** http://localhost:8081
- **API:** http://localhost:8081/api

## API Endpoints

### Голосование

| Метод | Endpoint | Описание | Параметры |
|-------|----------|----------|-----------|
| GET | `/api/voting/pair` | Получить случайную пару автомобилей | `model` (бренд) |
| POST | `/api/voting/{carId}` | Проголосовать за автомобиль | — |

### Автомобили

| Метод | Endpoint | Описание | Параметры |
|-------|----------|----------|-----------|
| GET | `/api/cars` | Список автомобилей с пагинацией | `model`, `yearFrom`, `yearTo`, `page`, `perPage` |
| GET | `/api/cars/models` | Список всех брендов | — |
| GET | `/api/cars/statistics` | Статистика с фильтрами | `model`, `yearFrom`, `yearTo`, `page`, `perPage` |

### Модели

| Метод | Endpoint | Описание | Параметры |
|-------|----------|----------|-----------|
| GET | `/api/models` | Получить все модели (бренд + модель + количество) | — |
| GET | `/api/models/{make}` | Получить модели конкретного бренда | — |

### Примеры запросов

**Получить случайную пару для бренда:**
```bash
GET /api/voting/pair?model=BMW
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "left": { "id": 123, "make": "BMW", "model": "X5", ... },
    "right": { "id": 456, "make": "BMW", "model": "X3", ... }
  }
}
```

**Проголосовать:**
```bash
POST /api/voting/123
```

**Ответ:**
```json
{
  "success": true,
  "votes_count": 15
}
```

**Получить статистику:**
```bash
GET /api/cars/statistics?model=BMW&yearFrom=2010&yearTo=2020&page=1&perPage=10
```

**Ответ:**
```json
{
  "data": [...],
  "total_votes": 150,
  "total": 50,
  "last_page": 5,
  "current_page": 1,
  "per_page": 10
}
```

## Тесты

Запуск тестов:
```bash
docker-compose exec app php artisan test
```

**Покрываемая функциональность (13 тестов, 35 утверждений):**
- Получение случайной пары автомобилей
- Получение пары с фильтром по бренду
- Обработка случая с недостаточным количеством автомобилей
- Голосование за автомобиль
- Защита от повторного голосования с одного IP
- Увеличение счётчика голосов
- Получение списка брендов
- Получение статистики с фильтрами
- API endpoints (vote, pair, cars, statistics, models)

## Структура проекта

```
car-voting-app/
├── app/
│   ├── Data/                   # DTO классы (Spatie Laravel Data)
│   │   ├── CarData.php
│   │   ├── CarPairData.php
│   │   ├── ModelData.php
│   │   ├── StatisticsData.php
│   │   └── VoteResultData.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Api/
│   │       │   ├── CarApiController.php
│   │       │   ├── ModelController.php
│   │       │   └── VotingController.php
│   │       └── CarController.php
│   ├── Models/
│   │   ├── Car.php
│   │   └── Vote.php
│   ├── Policies/
│   │   ├── CarPolicy.php
│   │   └── VotePolicy.php
│   ├── Repositories/
│   │   ├── CarRepository.php
│   │   ├── ModelRepository.php
│   │   ├── ModelRepositoryInterface.php
│   │   └── VoteRepository.php
│   └── Services/
│       ├── ModelService.php
│       ├── StatisticsService.php
│       └── VotingService.php
├── database/
│   ├── data/                   # JSON файлы с данными
│   ├── migrations/             # Миграции БД
│   └── seeders/                # Сидеры
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── Alert.vue
│   │   │   ├── CarCard.vue
│   │   │   ├── ImageViewer.vue
│   │   │   ├── Pagination.vue
│   │   │   └── TableView.vue
│   │   ├── stores/
│   │   │   ├── carStore.ts
│   │   │   ├── modelStore.ts
│   │   │   └── statisticsStore.ts
│   │   ├── types/
│   │   │   ├── index.ts
│   │   │   ├── ModelRecord.ts
│   │   │   └── records.ts
│   │   ├── views/
│   │   │   ├── StatisticsPage.vue
│   │   │   └── VotingPage.vue
│   │   ├── app.ts
│   │   └── bootstrap.ts
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   └── Feature/
│       └── VotingTest.php
├── local/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── local.ini
│   └── nginx/
│       └── default.conf
├── docker-compose.yml
├── README.md
└── ...
```

## Решение проблем

### Ошибка «Unable to locate file in Vite manifest»
```bash
docker-compose run --rm node npm run build
```

### Ошибка «Please provide a valid cache path»
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
```

### Нет данных в таблице
```bash
docker-compose exec app php artisan db:seed
```

### Изображения не отображаются
```bash
docker-compose exec app php artisan storage:link
```

### Фильтр по бренду не работает
Проверить в консоли браузера, что параметр `model` передаётся в запросе:
```
GET /api/voting/pair?model=BMW
```

## Технологии

**Backend:**
- Laravel 12.56.0
- PHP 8.4.20
- MariaDB 11+
- Redis

**Frontend:**
- Vue 3.5.x
- TypeScript 5.x
- Pinia 2.3.x
- Vue Router 4.5.x
- Vite 6.x
- Tailwind CSS
- Axios

**Инфраструктура:**
- Docker
- Docker Compose
- Nginx

**Библиотеки:**
- Spatie Laravel Data (DTO)
- PHPUnit 12.x (тесты)

## Соответствие ТЗ

| Требование | Реализация |
|------------|------------|
| Выпадающий список моделей | ✅ Выпадающий список брендов на странице голосования |
| Две случайные фотографии | ✅ API `/api/voting/pair` возвращает пару автомобилей |
| Без повторений в цикле | ✅ Кэширование показанных ID (Redis, 1 день) |
| Выбор левой/правой | ✅ Кнопки голосования с иконками thumbs-up |
| Без перезагрузки | ✅ AJAX через Pinia store, автоматическая загрузка новой пары |
| Зум фото | ✅ CSS transform scale(1.08) при наведении |
| Фильтры статистики | ✅ Бренд + диапазон годов |
| Таблица с голосами | ✅ Два режима: карточки (9 шт) и таблица (10-100 шт) |
| Общая сумма голосов | ✅ Отображается над таблицей/карточками |
| Laravel (последняя) | ✅ Laravel 12.56.0 |
| Миграции + сидеры | ✅ CarSeeder импортирует 1497 автомобилей из JSON |
| Чистый код | ✅ Repository Pattern, Service Layer, DTO, Policies |
| Адаптивная верстка | ✅ Tailwind CSS, мобильная адаптация таблицы |
| Docker Compose | ✅ PHP 8.4, Nginx, MariaDB, Redis, Node.js |
| README | ✅ Полная документация с API, БД, инструкциями |

## Лицензия

MIT
