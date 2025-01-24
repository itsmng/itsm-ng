<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;

interface DatabaseAdapterInterface
{
    public function __construct(string|CommonDBTM $class);

    public function getClass(): string;
    public function setClass(string $class): void;

    public function getConnection(): mixed;

    public function findOneBy(array $criteria): mixed;
    public function findBy(array $criteria, array $order = null, int $limit = null): array;
    public function findByRequest(array $request): array;

    public function deleteByCriteria(array $criteria): bool;

    // list columns from entity
    public function listFields(): array;
    // get values from entity as array
    public function getFields(mixed $content): array;
    public function getSettersFromFields(array $fields, object $content): array;

    public function save(array $fields): bool;
    public function add(array $fields): bool|array;

    public function getRelations(): array;

    // protected function adaptQueryObject(QueryObject $queryObject): array;
    public function request(QueryObject $queryObject): array;
}
