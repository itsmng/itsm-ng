<?php

namespace itsmng\Database\Migrations;

use RuntimeException;
use itsmng\Database\Runtime\DatabaseInterface;
use itsmng\Database\Runtime\LegacyDatabase;
use itsmng\Database\Schema\Dialect\DialectResolver;
use itsmng\Database\Schema\SchemaInstaller;

class MigrationRunner
{
    private const MIGRATION_LOCK_NAME = 'itsmng_migration';

    public function __construct(
        private readonly DatabaseInterface $database,
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

        return $this->withinLockAndTransaction('Schema migration failed', function () use ($history): array {
            $history->ensureTable();
            $applied = $history->applied();
            $pending = array_diff_key($this->repository->all(), $applied);
            if ($pending === []) {
                return [];
            }

            $batch = $history->nextBatch();
            $ran = [];
            $installer = $this->installer ?? new SchemaInstaller($this->dialect_resolver);

            foreach ($pending as $version => $metadata) {
                /** @var Migration $migration */
                $migration = new $metadata['class']();
                $installer->executeOperations(
                    $migration->buildOperations('up'),
                    $this->database
                );
                $history->record($version, $metadata['class'], $batch);
                $ran[] = $version;
            }

            return $ran;
        });
    }

    /**
     * @return string[]
     */
    public function rollbackLatestBatch(): array
    {
        $history = $this->history ?? new MigrationHistoryRepository($this->database);

        return $this->withinLockAndTransaction('Schema rollback failed', function () use ($history): array {
            $applied = array_values(array_filter(
                $history->latestBatchMigrations(),
                static fn (array $row): bool => !$history->isBaselineMigration($row)
            ));
            if ($applied === []) {
                return [];
            }

            $available = $this->repository->all();
            $rolled_back = [];
            $installer = $this->installer ?? new SchemaInstaller($this->dialect_resolver);

            foreach ($applied as $row) {
                if (!isset($available[$row['version']])) {
                    throw new RuntimeException('Missing migration class for version ' . $row['version']);
                }

                /** @var Migration $migration */
                $migration = new $available[$row['version']]['class']();
                $installer->executeOperations(
                    $migration->buildOperations('down'),
                    $this->database
                );
                $history->delete($row['version']);
                $rolled_back[] = $row['version'];
            }

            return $rolled_back;
        });
    }

    /**
     * Execute a callback within an advisory lock and optional DDL transaction.
     *
     * @template T
     * @param string $errorPrefix Error message prefix on failure
     * @param callable(): T $callback
     * @return T
     */
    private function withinLockAndTransaction(string $errorPrefix, callable $callback): mixed
    {
        $this->acquireLock();

        try {
            $dialect = ($this->dialect_resolver ?? new DialectResolver())->resolve($this->database);
            $transactional = $dialect->supportsTransactionalDdl();

            if ($transactional) {
                $this->database->beginTransaction();
            }

            try {
                $result = $callback();

                if ($transactional) {
                    $this->database->commit();
                }

                return $result;
            } catch (\Throwable $throwable) {
                if ($transactional && $this->database->inTransaction()) {
                    $this->database->rollBack();
                }

                throw new RuntimeException($errorPrefix . ': ' . $throwable->getMessage(), 0, $throwable);
            }
        } finally {
            $this->releaseLock();
        }
    }

    private function acquireLock(): void
    {
        if (
            $this->database instanceof LegacyDatabase
            && $this->database->getConnectionHandle() !== null
        ) {
            if (!$this->database->getLock(self::MIGRATION_LOCK_NAME)) {
                throw new RuntimeException(
                    'Could not acquire migration lock. Another migration may be running concurrently.'
                );
            }
        }
    }

    private function releaseLock(): void
    {
        if (
            $this->database instanceof LegacyDatabase
            && $this->database->getConnectionHandle() !== null
        ) {
            $this->database->releaseLock(self::MIGRATION_LOCK_NAME);
        }
    }
}
