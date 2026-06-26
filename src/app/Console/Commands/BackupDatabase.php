<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup MariaDB database to storage/backups as SQL file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        try {
            $pdo = DB::connection()->getPdo();
            $tables = [];
            
            // Get all tables
            $stmt = $pdo->query('SHOW TABLES');
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sql = "-- Database Backup\n";
            $sql .= "-- Date: " . now()->toDateTimeString() . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $this->info("Backing up table: {$table}");
                
                // Get table structure
                $stmtStructure = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $structure = $stmtStructure->fetch(\PDO::FETCH_ASSOC);
                
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $structure['Create Table'] . ";\n\n";

                // Get table data
                $stmtData = $pdo->query("SELECT * FROM `{$table}`");
                while ($row = $stmtData->fetch(\PDO::FETCH_ASSOC)) {
                    $keys = array_map(fn($k) => "`{$k}`", array_keys($row));
                    $values = array_map(function($v) use ($pdo) {
                        if ($v === null) return 'NULL';
                        return $pdo->quote($v);
                    }, array_values($row));

                    $sql .= "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Save to storage
            $filename = 'backups/backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
            Storage::disk('local')->put($filename, $sql);

            $path = Storage::disk('local')->path($filename);
            $this->info("Backup successfully completed! File saved at: {$path}");
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
