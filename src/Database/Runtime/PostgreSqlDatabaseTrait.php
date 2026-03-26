<?php

namespace itsmng\Database\Runtime;

trait PostgreSqlDatabaseTrait
{
    public $dbschema = 'public';

    public function connect($choice = null)
    {
        $this->connected = false;
        $this->dbh = null;
        $this->in_transaction = false;
        $this->rememberActiveDialect();

        if (is_array($this->dbhost)) {
            $index = (isset($choice) ? $choice : mt_rand(0, count($this->dbhost) - 1));
            $host = $this->dbhost[$index];
        } else {
            $host = $this->dbhost;
        }

        $dsn_parts = [];
        $host_value = (string) $host;
        if ($host_value !== '') {
            $host_parts = explode(':', $host_value, 2);
            if (count($host_parts) === 2 && ctype_digit($host_parts[1])) {
                $dsn_parts[] = 'host=' . $host_parts[0];
                $dsn_parts[] = 'port=' . $host_parts[1];
            } else {
                $dsn_parts[] = 'host=' . $host_value;
            }
        }

        if (!empty($this->dbdefault)) {
            $dsn_parts[] = 'dbname=' . $this->dbdefault;
        }

        try {
            $this->dbh = new \PDO(
                'pgsql:' . implode(';', $dsn_parts),
                $this->dbuser,
                rawurldecode((string) $this->dbpassword),
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                    \PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]
            );

            $this->connected = true;
            $this->error = 0;
            $this->clearLastError();
            $this->rememberActiveDialect();
            $this->setTimezone($this->guessTimezone());
        } catch (\PDOException $exception) {
            $this->dbh = null;
            $this->connected = false;
            $this->error = 1;
            $this->registerError($exception->getCode(), $exception->getMessage());
        }
    }

    public function insert($table, $params)
    {
        $result = parent::insert($table, $params);
        if ($result && $this->dbh instanceof \PDO) {
            $this->last_insert_id = $this->resolveLastInsertId((string) $table, (array) $params);
            $this->syncAutoIncrementSequence((string) $table, (array) $params);
        }

        return $result;
    }

    public function insertId()
    {
        if ($this->last_insert_id !== null) {
            return $this->last_insert_id;
        }

        try {
            return parent::insertId();
        } catch (\PDOException $exception) {
            return 0;
        }
    }

    public function syncAllAutoIncrementSequences(): void
    {
        $tables = $this->listTables();
        while ($table = $tables->next()) {
            $table_name = $table['TABLE_NAME'] ?? null;
            if (!is_string($table_name) || $table_name === '') {
                continue;
            }

            $this->syncAutoIncrementSequence($table_name, []);
        }
    }

    public function setGroupConcatMaxLen(int $length): bool
    {
        return true;
    }

    public function getLastWarning(): ?array
    {
        return null;
    }

    protected function getDriverErrorCode(?\Throwable $exception = null)
    {
        if ($exception instanceof \PDOException) {
            return $exception->getCode();
        }

        return $this->dbh instanceof \PDO ? (string) ($this->dbh->errorInfo()[0] ?? 0) : 0;
    }

    protected function getDriverErrorMessage(?\Throwable $exception = null): string
    {
        if ($exception instanceof \PDOException) {
            return $exception->getMessage();
        }

        if ($this->dbh instanceof \PDO) {
            $info = $this->dbh->errorInfo();
            return $info[2] ?? '';
        }

        return '';
    }

    private function syncAutoIncrementSequence(string $table, array $params): void
    {
        $fields = $this->listFields($table);
        if (!is_array($fields)) {
            return;
        }

        foreach ($fields as $field_name => $field) {
            if (($field['Extra'] ?? '') !== 'auto_increment') {
                continue;
            }

            if (array_key_exists($field_name, $params) && !is_numeric($params[$field_name])) {
                continue;
            }

            $sequence_result = $this->query(sprintf(
                'SELECT pg_get_serial_sequence(%s, %s) AS sequence_name',
                $this->quote($table),
                $this->quote($field_name)
            ));
            $sequence_row = $this->fetchAssoc($sequence_result);
            $sequence_name = $sequence_row['sequence_name'] ?? null;
            if (!is_string($sequence_name) || $sequence_name === '') {
                continue;
            }

            $max_value_sql = sprintf(
                '(SELECT MAX(%s) FROM %s)',
                $this->quoteName($field_name),
                $this->quoteName($table)
            );

            $this->query(sprintf(
                'SELECT setval(%s, GREATEST(COALESCE(%s, 1), 1), (COALESCE(%s, 0) >= 1))',
                $this->quote($sequence_name),
                $max_value_sql,
                $max_value_sql
            ));
        }
    }

    private function resolveLastInsertId(string $table, array $params)
    {
        if (array_key_exists('id', $params) && is_numeric($params['id'])) {
            return $params['id'];
        }

        $sequence_result = $this->query(sprintf(
            'SELECT pg_get_serial_sequence(%s, %s) AS sequence_name',
            $this->quote($table),
            $this->quote('id')
        ));
        $sequence_row = $this->fetchAssoc($sequence_result);
        $sequence_name = $sequence_row['sequence_name'] ?? null;
        if (!is_string($sequence_name) || $sequence_name === '') {
            return null;
        }

        $value_result = $this->query(sprintf(
            'SELECT CURRVAL(%s) AS last_insert_id',
            $this->quote($sequence_name)
        ));
        $value_row = $this->fetchAssoc($value_result);

        return $value_row['last_insert_id'] ?? null;
    }
}
