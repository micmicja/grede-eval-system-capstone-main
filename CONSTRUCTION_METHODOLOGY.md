# CONSTRUCTION METHODOLOGY

## 3.0 System Construction

This section describes the systematic construction and implementation of the Grade Evaluation System, detailing the technical architecture, development tools, and implementation processes used in building the system.

---

## 3.1 System Architecture

### 3.1.1 Architectural Pattern
The system follows the **Model-View-Controller (MVC)** architectural pattern provided by the Laravel framework, ensuring separation of concerns and maintainability.

**Components:**
- **Model Layer**: Handles data logic and database interactions
- **View Layer**: Manages user interface presentation
- **Controller Layer**: Processes business logic and user requests

### 3.1.2 Technology Stack

#### Backend Technologies
- **Framework**: Laravel 12.0
- **Programming Language**: PHP 8.2
- **Database**: SQLite (default), with support for MySQL/PostgreSQL
- **Authentication**: Laravel built-in authentication system
- **Queue Management**: Laravel Queue with database driver

#### Frontend Technologies
- **CSS Framework**: Tailwind CSS 4.0
- **Build Tool**: Vite 7.0.7
- **JavaScript**: Vanilla JavaScript with Axios for HTTP requests
- **Asset Compilation**: Laravel Vite Plugin 2.0

#### Development Tools
- **Dependency Management**: Composer (PHP), npm (JavaScript)
- **Testing Framework**: PestPHP 4.1
- **Code Quality**: Laravel Pint (code formatter)
- **Version Control**: Git

---

## 3.2 Database Design and Implementation

### 3.2.1 Database Schema Architecture

The system uses a relational database structure with the following core entities:

#### Primary Tables

**1. Users Table**
```
- id (Primary Key)
- name
- email
- password
- role (admin/teacher/counselor)
- created_at, updated_at
```

**2. Students Table**
```
- id (Primary Key)
- full_name
- section
- subject
- teacher_id (Foreign Key → users.id)
- created_at, updated_at
```

**3. Attendance Table**
```
- id (Primary Key)
- student_id (Foreign Key → students.id)
- attendance_date
- status (present/absent/late)
- created_at, updated_at
```

**4. Quiz_Exam_Activity Table**
```
- id (Primary Key)
- student_id (Foreign Key → students.id)
- category (quiz/exam/activity/project/recitation)
- score
- max_score
- weighted_score
- quiz_name
- total_score
- created_at, updated_at
```

**5. Student_Observations Table**
```
- id (Primary Key)
- student_id (Foreign Key → students.id)
- teacher_id (Foreign Key → users.id)
- calculated_average (decimal 5,2)
- risk_status
- observed_behaviors (JSON)
- referred_to_councilor (boolean)
- scheduled_at (timestamp)
- created_at, updated_at
```

**6. Evaluations Table**
```
- id (Primary Key)
- student_id (Foreign Key → students.id)
- teacher_id (Foreign Key → users.id)
- evaluation_data (JSON)
- status
- urgency
- scheduled_at
- created_at, updated_at
```

**7. Teacher_Settings Table**
```
- id (Primary Key)
- teacher_id (Foreign Key → users.id)
- quiz_weight
- exam_weight
- activity_weight
- project_weight
- recitation_weight
- attendance_weight
- created_at, updated_at
```

### 3.2.2 Database Relationships

- **One-to-Many**: Users → Students (A teacher can have multiple students)
- **One-to-Many**: Students → Attendance (A student can have multiple attendance records)
- **One-to-Many**: Students → Quiz_Exam_Activity (A student can have multiple grade records)
- **One-to-Many**: Students → Student_Observations (A student can have multiple observations)
- **One-to-One**: Users → Teacher_Settings (A teacher has one settings configuration)
- **Many-to-One**: Student_Observations → Users (Multiple observations can be created by one teacher)

### 3.2.3 Migration Strategy

The database schema was implemented using Laravel migrations in chronological order:

1. **Base Tables** (0001_01_01_000000): users, cache, jobs
2. **Core Features** (2025_12_06 - 2025_12_08): attendance, quiz_exam_activity, students
3. **Enhancements** (2025_12_14 - 2025_12_20): teacher_settings, weighted_score, evaluations
4. **Advanced Features** (2026_01_01 - 2026_02_01): student_observations, urgency, scheduling

---

## 3.3 System Modules Implementation

### 3.3.1 Authentication Module

**Purpose**: Secure user access control with role-based permissions

**Features Implemented**:
- User login with email and password
- Session management
- Login attempt limiting (3 attempts with 5-minute lockout)
- Role-based access (Admin, Teacher, Counselor)

**Implementation**:
- Controller: `AuthController.php`
- Middleware: Laravel's built-in authentication middleware
- Security: Password hashing using bcrypt

### 3.3.2 Student Management Module

**Purpose**: Comprehensive student record management

**Features Implemented**:
- Add new student records
- Edit existing student information
- View student details
- Delete student records
- Filter students by section/subject

**Implementation**:
- Model: `Student.php`
- Controller: `StudentController.php`
- CRUD operations with validation

### 3.3.3 Grade Recording Module

**Purpose**: Two-step workflow for recording student grades

**Sub-modules**:
1. **Quiz Recording** (`QuizController.php`)
2. **Exam Recording** (`ExamController.php`)
3. **Activity Recording** (`ActivityController.php`)
4. **Project Recording** (`ProjectController.php`)
5. **Recitation Recording** (`RecitationController.php`)

**Workflow**:
- Step 1: Enter raw scores
- Step 2: System calculates weighted scores based on teacher settings
- Automatic score validation (score ≤ max_score)

**Implementation**:
- Model: `Quiz_exam_activity.php`
- Database: Single table with category differentiation
- Calculation: Dynamic weight application from teacher settings

### 3.3.4 Attendance Tracking Module

**Purpose**: Monitor and record student attendance

**Features Implemented**:
- Mark attendance (Present/Absent/Late)
- View attendance history
- Attendance percentage calculation
- Integration with performance reports

**Implementation**:
- Model: `Attendance.php`
- Controller: `AttendanceController.php`
- Date-based tracking system

### 3.3.5 Grade Weight Configuration Module

**Purpose**: Customizable grading criteria for teachers

**Features Implemented**:
- Set custom weights for each grade category:
  - Quiz (default: 20%)
  - Exam (default: 30%)
  - Activity (default: 15%)
  - Project (default: 15%)
  - Recitation (default: 10%)
  - Attendance (default: 10%)
- Weight validation (total must equal 100%)
- Per-teacher configuration

**Implementation**:
- Model: `TeacherSetting.php`
- Controller: `TeacherSettingsController.php`
- Validation: Real-time weight sum verification

### 3.3.6 Student Performance Report Module

**Purpose**: Generate comprehensive student performance analytics

**Features Implemented**:
- Individual student reports
- Weighted grade calculations
- Performance visualization
- Category-wise breakdowns
- Overall grade computation

**Implementation**:
- Controller: `StudentReportController.php`
- Calculations: Aggregate queries with weighted averages
- Export functionality

### 3.3.7 Dropout Risk Assessment Module

**Purpose**: Identify at-risk students for early intervention

**Features Implemented**:
- Academic performance analysis
- Behavioral observation tracking (JSON storage)
- Risk status categorization (High/Medium/Low)
- Counselor referral system
- Scheduled observation tracking

**Implementation**:
- Model: `StudentObservation.php`
- Controller: Multiple observation-related controllers
- Algorithm: Multi-factor risk calculation
- Data Storage: JSON format for flexible behavior tracking

### 3.3.8 Evaluation Management Module

**Purpose**: Structured student evaluation system

**Features Implemented**:
- Create evaluations
- Schedule evaluations
- Urgency flagging
- Evaluation status tracking
- Comment system

**Implementation**:
- Model: `EvalutionComment.php` (Evaluations)
- Controller: `EvalutionCommentController.php`
- JSON storage for flexible evaluation data

### 3.3.9 Admin & Teacher Dashboards

**Purpose**: Role-specific management interfaces

**Features Implemented**:
- Admin: System-wide oversight and user management
- Teacher: Student management and grade recording
- Counselor: Access to at-risk student reports

**Implementation**:
- Controllers: `AdminController.php`, `TeacherController.php`
- Role-based view rendering
- Dashboard analytics

---

## 3.4 Development Process

### 3.4.1 Development Methodology

**Approach**: Incremental and Iterative Development

**Phases**:
1. **Requirements Analysis**: Identified core features and user needs
2. **Database Design**: Structured relational schema
3. **Module Development**: Implemented features incrementally
4. **Testing**: Continuous testing with PestPHP
5. **Integration**: Combined modules into cohesive system
6. **Refinement**: Bug fixes and performance optimization

### 3.4.2 Version Control Strategy

- **Repository Management**: Git-based version control
- **Branch Strategy**: Feature-based branching
- **Commit Standards**: Descriptive commit messages
- **Migration Tracking**: Chronological database version control

### 3.4.3 Code Organization

```
Project Structure:
- app/Models: Data models and business logic
- app/Http/Controllers: Request handling and routing
- app/Http/Middleware: Authentication and security
- database/migrations: Database schema versions
- resources/views: User interface templates
- resources/js: Frontend JavaScript
- resources/css: Styling and design
- routes/web.php: Application routing
- config/: System configuration files
```

---

## 3.5 Implementation Standards

### 3.5.1 Coding Standards

- **PSR-12**: PHP coding style compliance
- **Laravel Conventions**: Framework best practices
- **Naming Conventions**: 
  - Models: Singular (Student, User)
  - Tables: Plural (students, users)
  - Controllers: ResourceController pattern

### 3.5.2 Security Implementation

**Measures Implemented**:
1. **Input Validation**: Server-side validation for all user inputs
2. **SQL Injection Prevention**: Laravel Eloquent ORM
3. **XSS Protection**: Blade template escaping
4. **CSRF Protection**: Laravel CSRF tokens
5. **Password Security**: Bcrypt hashing
6. **Session Security**: Encrypted session storage
7. **Login Protection**: Rate limiting and lockout mechanism

### 3.5.3 Error Handling

- **Validation Errors**: User-friendly error messages
- **Exception Handling**: Try-catch blocks for critical operations
- **Logging**: Laravel logging system for debugging
- **User Feedback**: Flash messages for operation results

---

## 3.6 Testing Strategy

### 3.6.1 Testing Framework

**Tool**: PestPHP 4.1 with Laravel integration

### 3.6.2 Testing Coverage

**Unit Tests**:
- Model validation
- Calculation logic (weighted scores, averages)
- Helper functions

**Feature Tests**:
- Authentication flow
- CRUD operations
- Grade recording workflow
- Report generation

**Integration Tests**:
- Database transactions
- Controller-model interactions
- API endpoints

### 3.6.3 Test Execution

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
```

---

## 3.7 Deployment Configuration

### 3.7.1 Environment Setup

**Requirements**:
- PHP >= 8.2
- Composer
- Node.js & npm
- SQLite/MySQL/PostgreSQL

### 3.7.2 Installation Process

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment configuration
cp .env.example .env
php artisan key:generate

# 3. Database setup
php artisan migrate

# 4. Build assets
npm run build

# 5. Start development server
php artisan serve
```

### 3.7.3 Production Optimization

- **Asset Compilation**: Production builds with Vite
- **Database Optimization**: Indexed columns for performance
- **Caching**: Laravel cache for configuration and routes
- **Queue Workers**: Background job processing

---

## 3.8 System Integration

### 3.8.1 Internal Integration

- **Models ↔ Controllers**: Eloquent ORM
- **Frontend ↔ Backend**: Blade templates with JavaScript
- **Database ↔ Application**: Laravel migration system
- **Queue System**: Background processing for evaluations

### 3.8.2 Data Flow Architecture

```
User Input → Routes → Controllers → Models → Database
                ↓                      ↓
              Views ← Business Logic ← Data Retrieval
```

---

## 3.9 Quality Assurance

### 3.9.1 Code Quality Tools

- **Laravel Pint**: Automated code formatting
- **Static Analysis**: Code inspection and bug detection
- **Peer Review**: Code review process

### 3.9.2 Performance Optimization

- **Database Queries**: Eager loading to prevent N+1 queries
- **Asset Optimization**: Minified CSS and JavaScript
- **Caching Strategy**: Session and query result caching

### 3.9.3 Validation Processes

- **Input Validation**: Laravel validation rules
- **Business Logic Validation**: Custom validation for weights and scores
- **Database Constraints**: Foreign keys and data integrity

---

## 3.10 Documentation

### 3.10.1 Technical Documentation

- **Code Comments**: Inline documentation for complex logic
- **README**: Installation and setup guide
- **Migration Files**: Self-documenting database changes
- **API Documentation**: Controller method descriptions

### 3.10.2 User Documentation

- **Feature Guides**: How to use each module
- **Role-Based Guides**: Specific instructions for Admin/Teacher/Counselor
- **Troubleshooting**: Common issues and solutions

---

## 3.11 Maintenance and Updates

### 3.11.1 Update Strategy

- **Database Migrations**: Version-controlled schema changes
- **Feature Additions**: Modular development approach
- **Bug Fixes**: Issue tracking and resolution

### 3.11.2 Backup and Recovery

- **Database Backups**: Regular SQLite file backups
- **Code Repository**: Git-based version history
- **Migration Rollback**: Database rollback capability

---

## Summary

The construction phase of the Grade Evaluation System followed a systematic, incremental approach using modern web development technologies and best practices. The Laravel framework provided a robust foundation for implementing a secure, scalable, and maintainable educational management system. Through careful database design, modular development, comprehensive testing, and adherence to coding standards, the system successfully delivers all planned features while maintaining code quality and security standards.
