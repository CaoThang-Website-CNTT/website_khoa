<?php
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/includes/core/database.php';
require_once BASE_PATH . '/includes/console/commands/base_command.php';
require_once BASE_PATH . '/includes/core/schema/compiler/base_sql_compiler.php';
require_once BASE_PATH . '/includes/core/schema/column_definition.php';
require_once BASE_PATH . '/includes/core/schema/foreign_definition.php';
require_once BASE_PATH . '/includes/core/schema/table_builder.php';
require_once BASE_PATH . '/includes/core/migration/base_migration.php';
require_once BASE_PATH . '/includes/core/schema/compiler/mysql_compiler.php';
require_once BASE_PATH . '/includes/core/schema/table_builder.php';
require_once BASE_PATH . '/includes/core/migration/migration_history_tracker.php';
require_once BASE_PATH . '/includes/core/migration/migration_runner.php';
require_once BASE_PATH . '/includes/console/kernel.php';
require_once BASE_PATH . '/includes/console/commands/migrate_command.php';
require_once BASE_PATH . '/includes/console/commands/rollback_migration_command.php';
require_once BASE_PATH . '/includes/console/commands/add_migration_command.php';

$kernel = new \App\Console\Kernel();

$kernel->register(new App\Console\Commands\AddMigrationCommand());
$kernel->register(new App\Console\Commands\RollbackMigrationCommand());
$kernel->register(new App\Console\Commands\MigrateCommand());

$kernel->handle($argv);