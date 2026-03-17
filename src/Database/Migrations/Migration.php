<?php

namespace itsmng\Database\Migrations;

use LogicException;

abstract class Migration
{
    /**
     * @var array<int, MigrationBuilderInterface>
     */
    private array $builders = [];

    abstract public function up(): void;

    abstract public function down(): void;

    public function create(): CreateFacade
    {
        return new CreateFacade($this);
    }

    public function alter(): AlterFacade
    {
        return new AlterFacade($this);
    }

    public function delete(): DeleteFacade
    {
        return new DeleteFacade($this);
    }

    public function rename(): RenameFacade
    {
        return new RenameFacade($this);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildOperations(string $direction): array
    {
        $this->builders = [];
        match ($direction) {
            'up' => $this->up(),
            'down' => $this->down(),
            default => throw new LogicException('Unknown migration direction: ' . $direction),
        };

        return array_map(
            static fn (MigrationBuilderInterface $builder): array => $builder->build(),
            $this->builders
        );
    }

    public function registerBuilder(MigrationBuilderInterface $builder): void
    {
        $this->builders[] = $builder;
    }
}

interface MigrationBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array;
}

final class CreateFacade
{
    public function __construct(private readonly Migration $migration)
    {
    }

    public function table(string $table): CreateTableBuilder
    {
        $builder = new CreateTableBuilder($table);
        $this->migration->registerBuilder($builder);

        return $builder;
    }
}

final class AlterFacade
{
    public function __construct(private readonly Migration $migration)
    {
    }

    public function table(string $table): AlterTableBuilder
    {
        $builder = new AlterTableBuilder($table);
        $this->migration->registerBuilder($builder);

        return $builder;
    }
}

final class DeleteFacade
{
    public function __construct(private readonly Migration $migration)
    {
    }

    public function table(string $table): DeleteTableBuilder
    {
        $builder = new DeleteTableBuilder($table);
        $this->migration->registerBuilder($builder);

        return $builder;
    }

    public function column(string $column): DeleteColumnBuilder
    {
        return new DeleteColumnBuilder($this->migration, [$column]);
    }

    public function index(string $name): DeleteIndexBuilder
    {
        return new DeleteIndexBuilder($this->migration, $name);
    }
}

final class RenameFacade
{
    public function __construct(private readonly Migration $migration)
    {
    }

    public function table(string $from): RenameTableBuilder
    {
        $builder = new RenameTableBuilder($from);
        $this->migration->registerBuilder($builder);

        return $builder;
    }

    public function column(string $from): RenameColumnBuilder
    {
        return new RenameColumnBuilder($this->migration, $from);
    }
}

final class CreateTableBuilder implements MigrationBuilderInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $table;

    public function __construct(string $name)
    {
        $this->table = [
            'name'    => $name,
            'columns' => [],
            'indexes' => [],
        ];
    }

    public function withColumn(string $name): ColumnBuilder
    {
        $column = [
            'name'     => $name,
            'type'     => 'string',
            'length'   => 255,
            'nullable' => true,
        ];

        $this->table['columns'][] = &$column;

        return new ColumnBuilder($this, $column, 'create');
    }

    public function addIndex(array $index): void
    {
        $this->table['indexes'][] = $index;
    }

    public function build(): array
    {
        return [
            'kind'  => 'create_table',
            'table' => $this->table,
        ];
    }
}

final class AlterTableBuilder implements MigrationBuilderInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $operation;

    public function __construct(string $table)
    {
        $this->operation = [
            'kind'          => 'alter_table',
            'table'         => $table,
            'add_columns'   => [],
            'alter_columns' => [],
            'drop_columns'  => [],
            'indexes'       => [],
        ];
    }

    public function addColumn(string $name): ColumnBuilder
    {
        $column = [
            'name'     => $name,
            'type'     => 'string',
            'length'   => 255,
            'nullable' => true,
        ];

        $this->operation['add_columns'][] = &$column;

        return new ColumnBuilder($this, $column, 'alter_add');
    }

    public function alterColumn(string $name): ColumnBuilder
    {
        $column = [
            'name'     => $name,
            'type'     => 'string',
            'length'   => 255,
            'nullable' => true,
        ];

        $this->operation['alter_columns'][] = &$column;

        return new ColumnBuilder($this, $column, 'alter_modify');
    }

    public function dropColumn(string $name): self
    {
        $this->operation['drop_columns'][] = $name;

        return $this;
    }

    public function addIndex(array $index): void
    {
        $this->operation['indexes'][] = $index;
    }

    public function build(): array
    {
        return $this->operation;
    }
}

final class ColumnBuilder
{
    /**
     * @param CreateTableBuilder|AlterTableBuilder $parent
     * @param array<string, mixed>                 $column
     */
    public function __construct(
        private readonly object $parent,
        private array &$column,
        private readonly string $mode
    ) {
    }

    public function withColumn(string $name): self
    {
        return $this->parent->withColumn($name);
    }

    public function addColumn(string $name): self
    {
        return $this->parent->addColumn($name);
    }

    public function asBoolean(): self
    {
        $this->column['type'] = 'boolean';
        unset($this->column['length'], $this->column['precision'], $this->column['scale'], $this->column['custom']);

        return $this;
    }

    public function asChar(int $length = 1): self
    {
        $this->column['type'] = 'char';
        $this->column['length'] = $length;

        return $this;
    }

    public function asString(?int $length = 255): self
    {
        $this->column['type'] = 'string';
        $this->column['length'] = $length;

        return $this;
    }

    public function asText(): self
    {
        $this->column['type'] = 'text';
        unset($this->column['length']);

        return $this;
    }

    public function asLongText(): self
    {
        $this->column['type'] = 'longtext';
        unset($this->column['length']);

        return $this;
    }

    public function asInt16(bool $unsigned = false): self
    {
        $this->column['type'] = 'int16';
        $this->column['unsigned'] = $unsigned;

        return $this;
    }

    public function asInt32(bool $unsigned = false): self
    {
        $this->column['type'] = 'int32';
        $this->column['unsigned'] = $unsigned;

        return $this;
    }

    public function asInt64(bool $unsigned = false): self
    {
        $this->column['type'] = 'int64';
        $this->column['unsigned'] = $unsigned;

        return $this;
    }

    public function asDecimal(int $precision, int $scale): self
    {
        $this->column['type'] = 'decimal';
        $this->column['precision'] = $precision;
        $this->column['scale'] = $scale;

        return $this;
    }

    public function asFloat(): self
    {
        $this->column['type'] = 'float';
        unset($this->column['length'], $this->column['precision'], $this->column['scale']);

        return $this;
    }

    public function asDate(): self
    {
        $this->column['type'] = 'date';

        return $this;
    }

    public function asTime(): self
    {
        $this->column['type'] = 'time';

        return $this;
    }

    public function asTimestamp(): self
    {
        $this->column['type'] = 'timestamp';

        return $this;
    }

    public function asJson(): self
    {
        $this->column['type'] = 'json';

        return $this;
    }

    public function asBinary(?int $length = null): self
    {
        $this->column['type'] = 'binary';
        $this->column['length'] = $length;

        return $this;
    }

    public function asCustom(string $definition): self
    {
        $this->column['type'] = 'custom';
        $this->column['custom'] = $definition;

        return $this;
    }

    public function nullable(): self
    {
        $this->column['nullable'] = true;

        return $this;
    }

    public function notNullable(): self
    {
        $this->column['nullable'] = false;

        return $this;
    }

    public function withDefaultValue(mixed $value): self
    {
        $this->column['default'] = $value;

        return $this;
    }

    public function withDefaultExpression(string $expression): self
    {
        $this->column['default'] = [
            'kind'  => 'expression',
            'value' => $expression,
        ];

        return $this;
    }

    public function identity(): self
    {
        $this->column['autoIncrement'] = true;
        $this->column['nullable'] = false;

        return $this;
    }

    public function primaryKey(string $name = 'PRIMARY'): self
    {
        $this->parent->addIndex([
            'name'    => $name,
            'type'    => 'primary',
            'columns' => [
                ['name' => $this->column['name']],
            ],
        ]);

        return $this;
    }

    public function unique(?string $name = null): self
    {
        $this->parent->addIndex([
            'name'    => $name ?? $this->column['name'],
            'type'    => 'unique',
            'columns' => [
                ['name' => $this->column['name']],
            ],
        ]);

        return $this;
    }

    public function indexed(?string $name = null): self
    {
        $this->parent->addIndex([
            'name'    => $name ?? $this->column['name'],
            'type'    => 'index',
            'columns' => [
                ['name' => $this->column['name']],
            ],
        ]);

        return $this;
    }

    public function comment(string $comment): self
    {
        $this->column['comment'] = $comment;

        return $this;
    }

    public function end(): CreateTableBuilder|AlterTableBuilder
    {
        return $this->parent;
    }
}

final class DeleteTableBuilder implements MigrationBuilderInterface
{
    public function __construct(private readonly string $table)
    {
    }

    public function build(): array
    {
        return [
            'kind'  => 'delete_table',
            'table' => $this->table,
        ];
    }
}

final class DeleteColumnBuilder implements MigrationBuilderInterface
{
    /**
     * @param string[] $columns
     */
    public function __construct(
        private readonly Migration $migration,
        private array $columns
    ) {
    }

    public function column(string $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    public function fromTable(string $table): void
    {
        $this->migration->registerBuilder(new class ($table, $this->columns) implements MigrationBuilderInterface {
            public function __construct(private readonly string $table, private readonly array $columns)
            {
            }

            public function build(): array
            {
                return [
                    'kind'    => 'delete_column',
                    'table'   => $this->table,
                    'columns' => $this->columns,
                ];
            }
        });
    }

    public function build(): array
    {
        throw new LogicException('DeleteColumnBuilder must be completed with fromTable().');
    }
}

final class DeleteIndexBuilder implements MigrationBuilderInterface
{
    public function __construct(
        private readonly Migration $migration,
        private readonly string $name
    ) {
    }

    public function onTable(string $table): void
    {
        $this->migration->registerBuilder(new class ($table, $this->name) implements MigrationBuilderInterface {
            public function __construct(private readonly string $table, private readonly string $name)
            {
            }

            public function build(): array
            {
                return [
                    'kind'  => 'delete_index',
                    'table' => $this->table,
                    'name'  => $this->name,
                ];
            }
        });
    }

    public function build(): array
    {
        throw new LogicException('DeleteIndexBuilder must be completed with onTable().');
    }
}

final class RenameTableBuilder implements MigrationBuilderInterface
{
    private ?string $to = null;

    public function __construct(private readonly string $from)
    {
    }

    public function to(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function build(): array
    {
        if ($this->to === null) {
            throw new LogicException('RenameTableBuilder must be completed with to().');
        }

        return [
            'kind' => 'rename_table',
            'from' => $this->from,
            'to'   => $this->to,
        ];
    }
}

final class RenameColumnBuilder implements MigrationBuilderInterface
{
    private ?string $table = null;

    public function __construct(
        private readonly Migration $migration,
        private readonly string $from
    ) {
    }

    public function onTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function to(string $to): void
    {
        if ($this->table === null) {
            throw new LogicException('RenameColumnBuilder requires onTable() before to().');
        }

        $table = $this->table;
        $from  = $this->from;
        $this->migration->registerBuilder(new class ($table, $from, $to) implements MigrationBuilderInterface {
            public function __construct(
                private readonly string $table,
                private readonly string $from,
                private readonly string $to
            ) {
            }

            public function build(): array
            {
                return [
                    'kind'  => 'rename_column',
                    'table' => $this->table,
                    'from'  => $this->from,
                    'to'    => $this->to,
                ];
            }
        });
    }

    public function build(): array
    {
        throw new LogicException('RenameColumnBuilder must be completed with onTable() and to().');
    }
}
