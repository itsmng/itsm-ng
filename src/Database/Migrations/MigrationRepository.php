<?php

namespace itsmng\Database\Migrations;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use itsmng\Database\Migrations\Attribute\SchemaMigration;

class MigrationRepository
{
    private readonly string $directory;

    public function __construct(string $directory)
    {
        $this->directory = rtrim((string) (realpath($directory) ?: $directory), DIRECTORY_SEPARATOR);
    }

    /**
     * @return array<string, array{class: class-string<Migration>, description: string}>
     */
    public function all(): array
    {
        $migrations = [];
        if (!is_dir($this->directory)) {
            return $migrations;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            require_once $file->getPathname();
        }

        foreach (get_declared_classes() as $class) {
            if (!is_subclass_of($class, Migration::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }

            $filename = $reflection->getFileName();
            if ($filename === false) {
                continue;
            }

            $filename = (string) (realpath($filename) ?: $filename);
            if ($filename !== $this->directory && !str_starts_with($filename, $this->directory . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $attributes = $reflection->getAttributes(SchemaMigration::class);
            if ($attributes === []) {
                continue;
            }

            /** @var SchemaMigration $attribute */
            $attribute = $attributes[0]->newInstance();
            $migrations[$attribute->version] = [
                'class'       => $class,
                'description' => $attribute->description,
            ];
        }

        ksort($migrations, SORT_STRING);

        return $migrations;
    }
}
