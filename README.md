# Grade Evaluation System - Capstone Project

A comprehensive Laravel-based grade evaluation and student dropout risk assessment system for educational institutions.

## Features

- **Student Management**: Add, edit, and manage student records
- **Grade Recording**: Two-step workflow for Quiz, Exam, Activity, Project, and Recitation grades
- **Attendance Tracking**: Monitor student attendance
- **Student Performance Reports**: Generate detailed performance reports with weighted scores
- **Dropout Risk Assessment**: Evaluate student dropout risk based on academic performance and observed behaviors
- **Grade Weight Settings**: Customize grade category weights (Quiz, Exam, Activity, Project, Recitation, Attendance)
- **Multi-User Roles**: Admin, Teacher, and Counselor access levels
- **Login Security**: 3-attempt limit with 5-minute lockout for failed login attempts

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.1
- **Composer** - [Download Composer](https://getcomposer.org/download/)
- **SQLite** (default database) or MySQL/PostgreSQL
- **Node.js & npm** - [Download Node.js](https://nodejs.org/)
- **Git** - [Download Git](https://git-scm.com/downloads)

## Installation

Follow these steps to set up the project after cloning:

### 1. Clone the Repository

```bash
git clone https://github.com/micmicja/grede-eval-system-capstone-main.git
cd grede-eval-system-capstone-main
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the `.env.example` file to create your `.env` file:

```bash
# Windows (PowerShell)
copy .env.example .env

# macOS/Linux
cp .env.example .env
```

**Or create `.env` file directly with this content:**

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

**Note:** After creating the `.env` file, make sure to run `php artisan key:generate` to set the APP_KEY.

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

The project uses SQLite by default. Create the database file:

```bash
# Windows (PowerShell)
New-Item -ItemType File -Path database\database.sqlite

# macOS/Linux
touch database/database.sqlite
```

**Alternative**: If you prefer MySQL or PostgreSQL, update the `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 7. Run Database Migrations

```bash
php artisan migrate
```

This will create all necessary tables including:
- users
- students
- quiz_exam_activity (with total_score and quiz_name fields)
- attendance
- teacher_settings
- student_observations
- evaluations
- and more...

### 8. Seed the Database (Optional)

Create a default admin user:

```bash
php artisan db:seed --class=UserSeeder
```

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123456`

**Security Note:** The login system has a 3-attempt limit. After 3 failed login attempts, the account will be locked for 5 minutes.

### 9. Build Frontend Assets

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 10. Start the Development Server

```bash
php artisan serve
```

The application will be available at: **http://127.0.0.1:8000**

## Usage

### First-Time Login

1. Navigate to `http://127.0.0.1:8000`
2. Login with the admin credentials (see step 8 above)
3. Create teacher and student accounts from the admin dashboard

### Recording Grades

The system uses a two-step workflow:

1. **Step 1**: Create the grade record (Quiz name, total items, date)
2. **Step 2**: Add scores for all students

This applies to: Quiz, Exam, Activity, Project, and Recitation

### Grade Weight Settings

Navigate to **Settings** to configure grade category weights:
- Quiz (default: 25%)
- Exam (default: 25%)
- Activity (default: 25%)
- Project (default: 15%)
- Recitation (default: 5%)
- Attendance (default: 10%)

**Total must equal 100%**

### Dropout Risk Assessment

The observation feature evaluates student dropout risk based on:
- Academic performance (weighted average)
- Observed student behaviors (each behavior adds 5% to risk)

Risk Levels:
- **Low Risk**: 85-100%
- **Mid Risk**: 75-84%
- **Mid High Risk**: 60-74%
- **High Risk**: 0-59%

## Project Structure

```
app/
├── Http/Controllers/    # Application controllers
├── Models/             # Eloquent models
└── Helpers/            # Helper functions

resources/
├── views/              # Blade templates
├── css/                # Stylesheets
└── js/                 # JavaScript files

database/
├── migrations/         # Database migrations
└── seeders/           # Database seeders

routes/
└── web.php            # Web routes

public/
└── img/               # Images and assets
```

## Key Routes

- `/` - Login page
- `/Teacher/dashboard` - Teacher dashboard
- `/Teacher/Quiz/create` - Create new quiz
- `/Teacher/settings` - Grade weight settings
- `/Student/report/{id}` - Student performance report

## Troubleshooting

### Database Issues

If you encounter database errors:

```bash
php artisan migrate:fresh --seed
```

### Permission Errors (Linux/macOS)

```bash
chmod -R 775 storage bootstrap/cache
```

### Assets Not Loading

```bash
npm run build
php artisan optimize:clear
```

## Development

### Running Tests

```bash
php artisan test
```

### Clearing Cache

```bash
php artisan optimize:clear
```

## Technology Stack

- **Backend**: Laravel 11.x
- **Frontend**: Bootstrap 5, Blade Templates
- **Database**: SQLite (default), MySQL/PostgreSQL support
- **JavaScript**: Vanilla JS with AJAX

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and questions, please open an issue on the [GitHub repository](https://github.com/micmicja/grede-eval-system-capstone-main/issues).

---

**Developed as a Capstone Project** - Grade Evaluation System with Dropout Risk Assessment

