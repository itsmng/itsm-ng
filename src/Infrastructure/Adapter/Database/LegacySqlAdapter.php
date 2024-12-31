<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;
use Infrastructure\Adapter\Database\DatabaseAdapterInterface;

class LegacySqlAdapter implements DatabaseAdapterInterface
{
    public string $class;

    public function __construct(string|CommonDBTM $class)
    {
        $this->class = $class;
    }

    public function getClass(): string
    {
        return $this->class;
    }
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getConnection(): mixed
    {
        global $DB;

        return $DB;
    }

    public function findOneBy(array $criteria): mixed
    {
        global $DB;

        $iterator = $DB->request([
            'FROM'   => $this->class::getTable(),
            'WHERE'  => $criteria,
            'LIMIT'  => 1
        ]);
        if ($iterator->count()) {
            return $iterator->next();
        }
        return null;
    }

    public function findBy(array $criteria, array $order = null, int $limit = null): array
    {
        global $DB;

        $request = [
            'FROM'   => $this->class::getTable(),
            'WHERE'  => $criteria,
        ];
        if ($order) {
            $request['ORDERBY'] = $order;
        }
        if ($limit) {
            $request['LIMIT'] = $limit;
        }
        $iterator = $DB->request($request);
        if ($iterator->count()) {
            return iterator_to_array($iterator);
        }
        return [];
    }

    public function findByRequest(array $request): array
    {
        global $DB;

        $iterator = $DB->request($request);
        if ($iterator->count()) {
            return iterator_to_array($iterator);
        }
        return [];
    }

    public function deleteByCriteria(array $criteria): bool
    {
        global $DB;

        $error = $DB->delete($this->class::getTable(), $criteria);
        return $error === false;
    }

    // list columns from entity
    public function listFields(): array
    {
        return [];
    }
    // get values from entity as array
    public function getFields(array $content): array
    {
        return $content;
    }

    public function save(array $fields): bool
    {
        global $DB;

        $error = $DB->update(
            $this->class::getTable(),
            $fields,
            [$this->class::getIndexName() => $fields[$this->class::getIndexName()]]
        );
        return $error === false;
    }

    public function add(array $fields): bool|array
    {
        global $DB;

        $error = $DB->insert($this->class::getTable(), $fields);
        if ($error === false) {
            $fields[$this->class::getIndexName()] = $DB->insertId();
        }
        return $error === false;
    }

    public function getRelations(): array
    {
        return [];
    }
}
