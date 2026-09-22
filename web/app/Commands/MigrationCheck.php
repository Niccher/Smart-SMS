<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Enforces the "one migration file per table" convention.
 *
 * Scans every migration in app/Database/Migrations and reports any file that
 * creates, alters, or drops more than one table. A legacy no-op stub touches
 * zero tables and is allowed.
 *
 * Usage: php spark migrations:check
 */
class MigrationCheck extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'migrations:check';
    protected $description = 'Verify every migration file touches at most one table (one file per table).';

    /**
     * Forge methods whose FIRST string argument is a table name.
     */
    private const TABLE_METHODS = [
        'createTable',
        'dropTable',
        'addColumn',
        'dropColumn',
        'modifyColumn',
        'renameTable',
    ];

    public function run(array $params)
    {
        $dir   = APPPATH . 'Database/Migrations';
        $files = glob($dir . '/*.php') ?: [];

        if (empty($files)) {
            CLI::write('No migration files found in ' . $dir, 'yellow');
            return;
        }

        $errors = 0;
        $checked = 0;

        foreach ($files as $file) {
            $name = basename($file);
            $tables = $this->referencedTables($file);

            // No table references -> legacy no-op stub, allowed.
            if (empty($tables)) {
                continue;
            }

            $checked++;
            if (count($tables) > 1) {
                CLI::write('FAIL ' . $name, 'red');
                CLI::write('     references ' . count($tables) . ' tables: ' . implode(', ', $tables), 'red');
                $errors++;
            } else {
                CLI::write('ok   ' . $name . ' (table: ' . reset($tables) . ')', 'green');
            }
        }

        CLI::newLine();

        if ($errors > 0) {
            CLI::write("{$errors} migration file(s) violate the one-file-per-table convention.", 'red');
            CLI::error('Each migration file must create/alter exactly one table.');
            return;
        }

        CLI::write("All {$checked} active migration file(s) touch at most one table. Convention enforced.", 'green');
    }

    /**
     * Extract the set of distinct table names referenced by a migration file.
     */
    private function referencedTables(string $file): array
    {
        $code = (string) file_get_contents($file);

        $tables = [];
        foreach (self::TABLE_METHODS as $method) {
            $pattern = '/' . preg_quote($method, '/') . '\(\s*[\'"]([A-Za-z0-9_]+)[\'"]/';
            if (preg_match_all($pattern, $code, $matches)) {
                foreach ($matches[1] as $table) {
                    $tables[$table] = true;
                }
            }
        }

        // addForeignKey(table, ...) is a 3-arg form; capture its table arg too.
        if (preg_match_all('/addForeignKey\(\s*[^\'"]+\s*,\s*[\'"]([A-Za-z0-9_]+)[\'"]/', $code, $fk)) {
            foreach ($fk[1] as $table) {
                $tables[$table] = true;
            }
        }

        ksort($tables);
        return array_keys($tables);
    }
}
