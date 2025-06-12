<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;
use Doctrine\DBAL\Result;
use Traversable;

interface DatabaseAdapterInterface
{
    public function __construct(string|CommonDBTM $class);

    public function getClass(): string;
    public function setClass(string $class): void;

    public function getConnection(): mixed;

    public function getEntityManager();

    public function findOneBy(array $criteria): mixed;
    public function findBy(array $criteria, array $order = null, int $limit = null): array;
    public function findByRequest(array $request): array;

    public function deleteByCriteria(array $criteria): bool;

    // list columns from entity
    public function listFields(): array;
    // get values from entity as array
    public function getFields(mixed $content): array;
    public function getSettersFromFields(array $fields): array;

    public function save(array $fields): bool;
    public function add(array $fields): bool|array;

    public function getRelations(): array;

    public function request(array $sql): mixed;
    public function query(string $sql): Result;
    public function getDateAdd(string $date, $interval, string $unit, ?string $alias = null): string;
    public function getPositionExpression(string $substring, string $string, ?string $alias = null): string;
    public function getCurrentHourExpression(): string;
    public function getUnixTimestamp(string $field, ?string $alias = null): string;
    public function getRightExpression(string $field, int $value): array;
    public function getGroupConcat(string $field, string $separator = ', ', ?string $order_by = null, bool $distinct = true): string;
    public function concat(array $exprs): string;
    public function dateAdd(string $date, string $interval_unit, string $interval): string;
    public function ifnull(string $expr, string $default): string;
}
