<?php

namespace framework\db;

use framework\Str;

class Schema
{
    public function migrate()
    {
        $config = include dirname($_SERVER['SCRIPT_FILENAME']) . '/app/config/env.php';
        Db::_init($config['db']);

        $this->createMigrationsTableIfNotExists();

        $versions = array_map(function ($row) {
            return $row['version'];
        }, Db::getRows('SELECT version FROM migration'));

        foreach (array_diff($this->getMigrationsFiles(), $versions) as $fileName) {
            Db::transaction(function () use ($fileName) {
                if (Str::endsWith($fileName, '.php')) {
                    $migration = $this->getMigration($fileName);
                    $migration->migrate();
                } elseif (Str::endsWith($fileName, '.sql')) {
                    Db::exec(file_get_contents("app/migrations/$fileName"));
                }
                Db::query('INSERT INTO migration (version, apply_time) VALUES (' . Db::quote($fileName) . ', ' . time() . ')');
            });
            print 'Applied migration: ' . $fileName . PHP_EOL;
        }

        print 'Database updated' . PHP_EOL;
    }

    private function getMigrationsFiles()
    {
        $files = array_map(function ($file) {
            return pathinfo($file, PATHINFO_BASENAME);
        }, glob('app/migrations/*.{php,sql}', GLOB_BRACE));

        sort($files);

        return $files;
    }

    /**
     * @param $fileName
     * @return Migration
     */
    private function getMigration($fileName)
    {
        $classes = get_declared_classes();
        require 'app/migrations/' . $fileName;

        $classes = array_filter(array_diff(get_declared_classes(), $classes), function ($className) {
            return $className !== Migration::class;
        });
        $class = array_values($classes)[0];

        return new $class;
    }

    private function createMigrationsTableIfNotExists()
    {
        Db::query('
            CREATE TABLE IF NOT EXISTS migration (
              version VARCHAR(255) NOT NULL,
              apply_time INT NOT NULL,
              PRIMARY KEY (version)
            );
        ');
    }
}
