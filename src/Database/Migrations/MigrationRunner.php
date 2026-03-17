<?php

namespace itsmng\Database\Migrations;

use DBmysql;
use RuntimeException;
use itsmng\Database\Schema\Dialect\DialectResolver;
use itsmng\Database\Schema\SchemaInstaller;

class MigrationRunner
{
    public function __construct(
        private readonly DBmysql $database,
        private readonly MigrationRepository $repository,
        private readonly ?MigrationHistoryRepository $history = null,
        private readonly ?SchemaInstaller $installer = null,
        private readonly ?DialectResolver $dialect_resolver = null
    ) {
    }

    /**
     * @return string[]
     */
    public function migrate(): array
    {
        $history = $this->history ?? new MigrationHistoryRepository($this->database);
        $history->ensureTable();

        $applied = $history->applied();
        $pending = array_diff_key($this->repository->all(), $applied);
        if ($pending === []) {
            return [];
        }

        $batch = $history->nextBatch();
        $ran = [];

        $dialect = ($this->dialect_resolver ?? new DialectResolver())->resolve($this->database);
        $transactional = $dialect->supportsTransactionalDdl();
        if ($transactional) {
            $this->database->beginTransaction();
        }

        try {
            foreach ($pending as $version => $metadata) {
                /** @var Migration $migration */
                $migration = new $metadata['class']();
                ($this->installer ?? new SchemaInstaller($this->dialect_resolver))->executeOperations(
                    $migration->buildOperations('up'),
                    $this->database
                );
                $history->record($version, $metadata['class'], $batch);
                $ran[] = $version;
            }

            if ($transactional) {
                $this->database->commit();
            }
        } catch (\Throwable $throwable) {
            if ($transactional && $this->database->inTransaction()) {
                $this->database->rollBack();
            }

            throw new RuntimeException('Schema migration failed: ' . $throwable->getMessage(), 0, $throwable);
        }

        return $ran;
    }

    /**
     * @return string[]
     */
    public function rollbackLatestBatch(): array
    {
        $history = $this->history ?? new MigrationHistoryRepository($this->database);
        $applied = $history->latestBatchMigrations();
        if ($applied === []) {
            return [];
        }

        $available = $this->repository->all();
        $rolled_back = [];
        $dialect = ($this->dialect_resolver ?? new DialectResolver())->resolve($this->database);
        $transactional = $dialect->supportsTransactionalDdl();
        if ($transactional) {
            $this->database->beginTransaction();
        }

        try {
            foreach ($applied as $migration_row) {
                if (!isset($available[$migration_row['version']])) {
                    throw new RuntimeException('Missing migration class for version ' . $migration_row['version']);
                }

                /** @var Migration $migration */
                $migration = new $available[$migration_row['version']]['class']();
                ($this->installer ?? new SchemaInstaller($this->dialect_resolver))->executeOperations(
                    $migration->buildOperations('down'),
                    $this->database
                );
                $history->delete($migration_row['version']);
                $rolled_back[] = $migration_row['version'];
            }

            if ($transactional) {
                $this->database->commit();
            }
        } catch (\Throwable $throwable) {
            if ($transactional && $this->database->inTransaction()) {
                $this->database->rollBack();
            }

            throw new RuntimeException('Schema rollback failed: ' . $throwable->getMessage(), 0, $throwable);
        }

        return $rolled_back;
    }
}
