-- --------------------------------------------------------
-- Host:                         C:\Users\Admin\Downloads\grede-eval-system-capstone-main\grede-eval-system-capstone-main\database\database.sqlite
-- Server version:               3.51.0
-- Server OS:                    
-- HeidiSQL Version:             12.14.0.7165
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES  */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for database
CREATE DATABASE IF NOT EXISTS "database";
;

-- Dumping structure for table database.attendance
CREATE TABLE IF NOT EXISTS "attendance" ("id" integer primary key autoincrement not null, "full_name" varchar not null, "subject" varchar not null, "section" varchar not null, "user_id" integer not null, "date" date not null, "present" tinyint(1) not null default '0', "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade);

-- Data exporting was unselected.

-- Dumping structure for table database.evaluations
CREATE TABLE IF NOT EXISTS "evaluations" ("id" integer primary key autoincrement not null, "teacher_id" integer not null, "status" varchar not null default ('pending'), "comments" varchar not null, "category" varchar not null, "created_at" datetime, "updated_at" datetime, "scheduled_at" datetime, "urgency" varchar, "student_id" integer not null, foreign key("teacher_id") references users("id") on delete cascade on update no action, foreign key("student_id") references "students"("id") on delete cascade);

-- Data exporting was unselected.

-- Dumping structure for table database.migrations
CREATE TABLE IF NOT EXISTS "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

-- Data exporting was unselected.

-- Dumping structure for table database.quiz_exam_activity
CREATE TABLE IF NOT EXISTS "quiz_exam_activity" ("id" integer primary key autoincrement not null, "full_name" varchar not null, "subject" varchar not null, "section" varchar not null, "user_id" integer not null, "activity_type" varchar not null, "activity_title" varchar not null, "date_taken" date not null, "score" integer not null default '0', "created_at" datetime, "updated_at" datetime, "weighted_score" numeric, "total_score" integer, "quiz_name" varchar, foreign key("user_id") references "users"("id") on delete cascade);

-- Data exporting was unselected.

-- Dumping structure for table database.sessions
CREATE TABLE IF NOT EXISTS "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));
;
CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");

-- Data exporting was unselected.

-- Dumping structure for table database.student_observations
CREATE TABLE IF NOT EXISTS "student_observations" ("id" integer primary key autoincrement not null, "student_id" integer not null, "teacher_id" integer not null, "calculated_average" numeric not null, "risk_status" varchar not null, "observed_behaviors" text not null, "referred_to_councilor" tinyint(1) not null default '0', "created_at" datetime, "updated_at" datetime, "scheduled_at" datetime, "counseling_status" varchar not null default 'pending', foreign key("student_id") references "students"("id") on delete cascade, foreign key("teacher_id") references "users"("id") on delete cascade);

-- Data exporting was unselected.

-- Dumping structure for table database.students
CREATE TABLE IF NOT EXISTS "students" ("id" integer primary key autoincrement not null, "full_name" varchar not null, "section" varchar not null, "subject" varchar not null, "teacher_id" integer not null, "created_at" datetime, "updated_at" datetime, "student_id" varchar, foreign key("teacher_id") references "users"("id") on delete cascade);

-- Data exporting was unselected.

-- Dumping structure for table database.teacher_settings
CREATE TABLE IF NOT EXISTS "teacher_settings" ("id" integer primary key autoincrement not null, "user_id" integer not null, "quiz_weight" integer not null default '25', "exam_weight" integer not null default '25', "activity_weight" integer not null default '25', "project_weight" integer not null default '15', "recitation_weight" integer not null default '10', "attendance_weight" integer not null default '10', "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade);
CREATE UNIQUE INDEX "teacher_settings_user_id_unique" on "teacher_settings" ("user_id");

-- Data exporting was unselected.

-- Dumping structure for table database.users
CREATE TABLE IF NOT EXISTS "users" ("id" integer primary key autoincrement not null, "full_name" varchar not null, "username" varchar not null, "password" varchar not null, "role" varchar not null default 'student', "section" varchar, "subject" varchar, "created_at" datetime, "updated_at" datetime);
CREATE UNIQUE INDEX "users_username_unique" on "users" ("username");

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
