<?php

namespace itsmng\Database\Migrations;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use itsmng\Database\Migrations\Attribute\SchemaMigration;

class MigrationRepository
{
    public function __construct(
        private readonly string $directory
    ) {
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

            if (!$reflection->getFileName() || !str_starts_with($reflection->getFileName(), $this->directory)) {
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
