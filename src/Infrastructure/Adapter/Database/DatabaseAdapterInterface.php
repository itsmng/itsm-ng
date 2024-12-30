<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;

interface DatabaseAdapterInterface
{
    public string $class;

    // Constructor
    public function __construct(string|CommonDBTM $class);

    // Get the connection
    public function getConnection(): mixed;

    // Find one and load it in content
    public function findOneBy(array $criteria): mixed;
    public function findBy(array $criteria, array $order = null, int $limit = null): array;
    public function findByRequest(array $request): array;

    // Delete
    public function deleteByCriteria(array $criteria): bool;

    public function listFields(): array;
    public function getFields(): array;

    public function save($fields): bool;

    // Insert a new row in the database
    public function add(array $values): bool|array;

    public function getRelations(): array;
}
