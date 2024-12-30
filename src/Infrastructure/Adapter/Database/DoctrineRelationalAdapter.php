<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;

class DoctrineRelationalAdapter implements DatabaseAdapterInterface {
    public string $class;
    public function __construct(string|CommonDBTM $class) {
        $this->class = $class;
    }

    public function getClass(): string {
        return $this->class;
    }
    public function setClass(string $class): void {
        $this->class = $class;
    }

    public function getConnection(): mixed {
        return null;
    }

    public function findOneBy(array $criteria): mixed {
        return null;
    }
    public function findBy(array $criteria, array $order = null, int $limit = null): array {
        return [];
    }
    public function findByRequest(array $request): array {
        return [];
    }

    public function deleteByCriteria(array $criteria): bool {
        return false;
    }

    // list columns from entity
    public function listFields(): array {
        return [];
    }
    // get values from entity as array
    public function getFields($content): array {
        return [];
    }

    public function save(array $fields): bool {
        return false;
    }
    public function add(array $fields): bool|array {
        return false;
    }

    public function getRelations(): array {
        return [];
    }
}
