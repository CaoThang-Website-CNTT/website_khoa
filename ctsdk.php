<?php
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/includes/env_loader.php';

App\EnvLoader::load(BASE_PATH . '/.env.local');

// Database
require_once BASE_PATH . '/includes/core/database.php';
// Schema
require_once BASE_PATH . '/includes/core/schema/column_definition.php';
require_once BASE_PATH . '/includes/core/schema/foreign_definition.php';
require_once BASE_PATH . '/includes/core/schema/table_builder.php';
require_once BASE_PATH . '/includes/core/schema/query_builder.php';
// Schema Compiler
require_once BASE_PATH . '/includes/core/schema/compiler/base_sql_compiler.php';
require_once BASE_PATH . '/includes/core/schema/compiler/mysql_compiler.php';

// Migration
require_once BASE_PATH . '/includes/migration/base_migration.php';
require_once BASE_PATH . '/includes/migration/migration_history_tracker.php';
require_once BASE_PATH . '/includes/migration/migration_runner.php';

// Console
require_once BASE_PATH . '/includes/console/kernel.php';
require_once BASE_PATH . '/includes/console/commands/base_command.php';
require_once BASE_PATH . '/includes/console/commands/migrate_command.php';
require_once BASE_PATH . '/includes/console/commands/rollback_migration_command.php';
require_once BASE_PATH . '/includes/console/commands/add_migration_command.php';


use App\Console\Kernel;
use App\Console\Commands\{AddMigrationCommand, RollbackMigrationCommand, MigrateCommand};

$kernel = new Kernel();

$kernel->register(new AddMigrationCommand());
$kernel->register(new RollbackMigrationCommand());
$kernel->register(new MigrateCommand());

$kernel->handle($argv);