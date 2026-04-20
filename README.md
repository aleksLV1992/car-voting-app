# KharCar — Приложение для голосования за автомобили

Веб-приложение для голосования за фотографии аукционных автомобилей на Laravel 12 + Vue 3.

## Возможности

### Страница голосования
- Выбор бренда автомобиля из выпадающего списка
- Две случайные фотографии выбранного бренда
- Голосование без перезагрузки страницы (AJAX)
- Зум фотографий при наведении
- Защита от повторного голосования
- Автоматическое обновление пары после голосования

### Страница статистики
- Фильтры по бренду и диапазону годов
- Два режима отображения: карточки и таблица
- Пагинация с выбором количества записей
- Просмотр фотографий в полном размере
- Подсчёт общего количества голосов

## Установка

### Требования

- Docker и Docker Compose
- PHP 8.4+
- Node.js 11+
- Composer
- MariaDB 10.11+

### Развёртывание через Docker (рекомендуется)

```bash
cd car-voting-app

# 1. Настройка окружения
cp .env.example .env

# 2. Запуск контейнеров
docker-compose up -d --build

# 3. Установка зависимостей Laravel
docker-compose exec app composer install

# 4. Генерация ключа приложения
docker-compose exec app php artisan key:generate

# 5. Миграция БД
docker-compose exec app php artisan migrate

# 6. Импорт данных из JSON
docker-compose exec app php artisan db:seed

# 7. Создание ссылки на storage
docker-compose exec app php artisan storage:link

# 8. Копирование изображений (извлечения из JSON файлов)
# Изображения уже находятся в storage/app/public/cars/ после разархивирования тестового задания
# Если изображений нет, скопируйте их из исходных данных:
# docker-compose cp ./path/to/images app:/var/www/html/storage/app/public/cars/
docker-compose exec app bash -c "chmod -R 755 storage/app/public/cars"

# 9. Сборка Vue
docker-compose run --rm node npm install
docker-compose run --rm node npm run build
```

**Доступ:** http://localhost:8081

### Локальная разработка

```bash
cd car-voting-app

# 1. Установка зависимостей
composer install
npm install

# 2. Настройка окружения
cp .env.example .env
# Отредактируйте .env (DB_CONNECTION, DB_HOST, etc.)

# 3. Генерация ключа и миграция
php artisan key:generate
php artisan migrate

# 4. Импорт данных
php artisan db:seed

# 5. Создание ссылки на storage
php artisan storage:link

# 6. Копирование изображений
# Изображения должны быть в storage/app/public/cars/
# Если их нет, скопируйте из исходных данных тестового задания:
# cp /path/to/test_task/images/* storage/app/public/cars/
chmod -R 755 storage/app/public/cars

# 7. Сборка ассетов
npm run build

# 8. Запуск сервера
php artisan serve --port=8081
```

**Доступ:** http://localhost:8081

## Технологии

| Компонент | Версия |
|-----------|--------|
| Laravel | 12.x |
| PHP | 8.4 |
| Vue | 3.5 |
| TypeScript | 5.x |
| Pinia | 2.3 |
| Vue Router | 4.5 |
| Vite | 6.x |
| Tailwind CSS | 3.x |
| MariaDB | 10.11+ |
| Redis | 7.x |
| Docker | 24.x |
| Spatie Laravel Data | 4.x |

## Архитектура

### Backend
- **Models** — Eloquent модели (Car, Vote)
- **Repositories** — работа с БД (CarRepository, VoteRepository)
- **Services** — бизнес-логика (VotingService, StatisticsService, ModelService)
- **Controllers** — API контроллеры
- **DTO** — передача данных (Spatie Laravel Data)
- **Policies** — управление доступом

### Frontend
- **Vue 3** — реактивный фреймворк
- **TypeScript** — типизация
- **Pinia** — управление состоянием
- **Vue Router** — навигация
- **Vite** — сборка
- **Tailwind CSS** — стилизация

## База данных

### Таблица `cars`
- `id` — первичный ключ
- `image` — путь к изображению (уникальный)
- `make` — бренд автомобиля
- `model` — модель автомобиля
- `year` — год выпуска
- `odometer` — пробег
- `engine` — двигатель
- `transmission` — КПП
- `color` — цвет
- `winning_bid_amount` — цена
- `location` — местоположение
- `votes_count` — счётчик голосов
- Индексы: `make`, `model`, `year`

### Таблица `votes`
- `id` — первичный ключ
- `car_id` — внешний ключ к cars
- `voter_id` — идентификатор голосующего (IP)
- Уникальное ограничение: `(car_id, voter_id)` — защита от повторного голосования

## API Endpoints

### Голосование
```bash
GET /api/voting/pair?model=BMW
POST /api/voting/{carId}
```

### Статистика
```bash
GET /api/cars?make=BMW&yearFrom=2020&yearTo=2023&page=1&perPage=10
GET /api/cars/models
GET /api/cars/statistics?make=BMW&yearFrom=2020&yearTo=2023&page=1&perPage=10
```

### Модели
```bash
GET /api/models
GET /api/models/{make}
```

## Тесты

```bash
# Запуск тестов
docker-compose exec app php artisan test

# Покрытие
# 13 Feature тестов (37 утверждений)
# - Голосование
# - Фильтрация
# - API endpoints
```

## Лиценсия

Проект создан для тестового задания.
