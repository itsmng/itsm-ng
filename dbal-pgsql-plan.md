# DBAL Re-Architecture Plan for PostgreSQL Support

## Executive Summary

This document outlines the plan to re-architecture the database abstraction layer (DBAL) to support both MySQL and PostgreSQL databases. The current codebase is MySQL-centric, and this plan provides a structured approach to enable users to run the application with PostgreSQL.

---

## Current State Analysis

### 1. Database Connection & Configuration

**Hard-coded MySQL assumptions:**
- `config/config_db.php` defines `DB extends DBmysql`
- `config/config_db_pg.php` exists but still extends `DBmysql` (only adds `dbtype = 'pgsql'`)
- No runtime selection mechanism based on `dbtype`

**Files involved:**
- `config/config_db.php`
- `config/config_db_pg.php`
- `config/config_db_mysql.php`
- `inc/dbconnection.class.php` (replication logic assumes `DBmysql`)

### 2. Core Database Driver (`inc/dbmysql.class.php`)

**MySQL-specific implementations:**
- Uses `mysqli` extension directly (`new mysqli()`, `mysqli_ssl_set()`, etc.)
- Schema inspection uses MySQL-specific queries:
  - `SHOW COLUMNS FROM`
  - `SHOW CREATE TABLE`
  - `SHOW DATABASES`
  - `SHOW INDEX FROM`
  - `information_schema` queries with MySQL-specific columns
- Timezone handling queries `mysql.time_zone_name` table
- Quoting uses backticks via `getQuoteNameChar()` returning `` ` ``
- Charset/collation setup uses `SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'`

### 3. Query Builder (`inc/dbmysqliterator.class.php`)

**MySQL-specific features:**
- Static calls to `DBmysql::quoteName()` (backticks)
- `REGEXP` operator in `$allowed_operators`
- Generates backtick-quoted identifiers

### 4. Install & CLI Infrastructure

**MySQL-only:**
- `install/install.php` - uses `new mysqli()` directly
- `inc/console/database/installcommand.class.php` - uses `new mysqli()` directly
- `inc/console/database/abstractconfigurecommand.class.php` - MySQL connection logic
- `install/mysql/glpi-empty.sql` - MySQL-specific DDL (ENGINE, AUTO_INCREMENT, COLLATE)

### 5. Migrations (`inc/migration.class.php`)

**MySQL-specific DDL generation:**
- `fieldFormat()` produces MySQL types:
  - `TINYINT(1)`, `INT(11)`, `VARCHAR(255) COLLATE utf8_unicode_ci`
  - `TEXT COLLATE utf8_unicode_ci`, `LONGTEXT COLLATE utf8_unicode_ci`
  - `AUTO_INCREMENT`
- Many update scripts contain raw MySQL DDL

### 6. Application SQL with MySQL Functions

**Hotspots with MySQL-specific functions:**

| File | Functions Used |
|------|----------------|
| `inc/search.class.php` | `GROUP_CONCAT`, `IFNULL`, `ADDDATE`, `DATE_SUB` |
| `inc/ticket.class.php` | `ADDDATE`, `NOW()`, `DATEDIFF`, `COALESCE`, `TIME_TO_SEC`, `TIMEDIFF` |
| `inc/contract.class.php` | `DATEDIFF`, `ADDDATE`, `CURDATE()` |
| `inc/commonitilobject.class.php` | `IF()`, `TIME_TO_SEC`, `TIMEDIFF`, `NOW()` |
| `inc/user.class.php` | `NOW()`, `ADDDATE`, `CONCAT`, `IFNULL` |
| `inc/reservationitem.class.php` | `UNIX_TIMESTAMP`, `NOW()` |
| `inc/crontask.class.php` | `DATE_FORMAT`, `LOCATE` |
| `inc/projecttask.class.php` | `DATE_SUB`, `DATE_ADD`, `CAST(... AS UNSIGNED)` |
| `inc/oidc.class.php` | `INSERT IGNORE` |
| `inc/api/api.class.php` | Direct `fetch_row()` on mysqli result |

### 7. Direct mysqli Result Usage

**Bypassing DBmysql abstraction:**
- `inc/ticket.class.php:4373` - `$result->fetch_assoc()`
- `inc/api/api.class.php:1427` - `$DB->query()->fetch_row()[0]`
- `install/install.php:140,158` - `$DB_ver->fetch_array()`
- `inc/console/database/installcommand.class.php:268` - `$tables_result->fetch_array()[0]`

### 8. System Requirements

**Extension checks (`inc/config.class.php`, `inc/system/requirement/mysqlimysqlnd.class.php`):**
- Only checks for `mysqli` extension
- Requires `mysqlnd` driver specifically

---

## Architecture Design

### Class Hierarchy

```
DBmysql (abstract base, renamed conceptually but kept for compatibility)
├── DBmysqlAdapter (concrete MySQL implementation)
└── DBpgsqlAdapter (concrete PostgreSQL implementation)

DBFunction (abstract)
├── DBmysqlFunction
└── DBpgsqlFunction
```

### New Files to Create

```
inc/
├── db/
│   ├── dbinterface.interface.php      # Interface contract
│   ├── dbmysql.class.php              # Refactored base/MySQL adapter
│   ├── dbpgsql.class.php              # PostgreSQL adapter
│   ├── dbfactory.class.php            # Factory for connection selection
│   ├── dbfunction.interface.php       # Function abstraction interface
│   ├── dbmysqlfunction.class.php      # MySQL function implementations
│   └── dbpgsqlfunction.class.php      # PostgreSQL function implementations
```

---

## Implementation Plan

### Phase 1: DBAL Foundation (Estimated: 3-4 days)

#### 1.1 Create Database Interface

Define a clear interface contract that both adapters must implement:

```php
interface DBInterface {
    public function connect($choice = null);
    public function query($query);
    public function prepare($query);
    public function escape($string);
    public function quote($value, int $type = PDO::PARAM_STR);
    public static function quoteName($name);
    public static function quoteValue($value);
    public static function getQuoteNameChar(): string;
    public function insertId();
    public function affectedRows();
    public function beginTransaction();
    public function commit();
    public function rollBack();
    public function inTransaction();
    // ... other methods
}
```

#### 1.2 Refactor DBmysql

- Extract abstract methods to a base class or interface
- Keep class name `DBmysql` for backward compatibility
- Make MySQL-specific parts overridable:
  - `connect()` - mysqli connection logic
  - `getQuoteNameChar()` - backtick character
  - Schema inspection methods
  - Timezone methods

#### 1.3 Create DBpgsql Class

Implement PostgreSQL adapter using PDO:

```php
class DBpgsql extends DBmysql {
    public function connect($choice = null) {
        // Use PDO with pgsql driver
        $dsn = "pgsql:host={$this->dbhost};dbname={$this->dbdefault}";
        $this->dbh = new PDO($dsn, $this->dbuser, rawurldecode($this->dbpassword));
        // PostgreSQL-specific setup
    }
    
    public static function getQuoteNameChar(): string {
        return '"';
    }
    
    // Override schema inspection for PostgreSQL
    public function listFields($table, $usecache = true) {
        // Use information_schema with PG-specific queries
    }
}
```

#### 1.4 Create Connection Factory

```php
class DBFactory {
    public static function create(array $config): DBInterface {
        $dbtype = $config['dbtype'] ?? 'mysql';
        switch ($dbtype) {
            case 'pgsql':
            case 'postgresql':
                return new DBpgsql($config);
            case 'mysql':
            default:
                return new DBmysql($config);
        }
    }
}
```

#### 1.5 Update Configuration Files

**config/config_db.php (MySQL):**
```php
<?php
class DB extends DBmysql {
   public $dbhost     = 'localhost';
   public $dbuser     = 'root';
   public $dbpassword = 'itsmpassword';
   public $dbdefault  = 'itsm_main';
   public $dbtype     = 'mysql';
}
```

**config/config_db_pg.php (PostgreSQL):**
```php
<?php
class DB extends DBpgsql {
   public $dbhost     = 'localhost';
   public $dbuser     = 'postgres';
   public $dbpassword = 'mypass';
   public $dbdefault  = 'itsm_main';
   public $dbtype     = 'pgsql';
}
```

---

### Phase 2: Database Function Abstraction (Estimated: 2-3 days)

#### 2.1 Create Function Interface

```php
interface DBFunctionInterface {
    public function groupConcat(string $expr, string $separator = ','): string;
    public function ifNull(string $expr, string $default): string;
    public function now(): string;
    public function curDate(): string;
    public function dateAdd(string $date, string $interval, string $unit): string;
    public function dateSub(string $date, string $interval, string $unit): string;
    public function dateDiff(string $expr1, string $expr2): string;
    public function unixTimestamp(string $expr = ''): string;
    public function fromUnixTime(string $timestamp): string;
    public function dateFormat(string $date, string $format): string;
    public function concat(...$exprs): string;
    public function coalesce(...$exprs): string;
    public function cast(string $expr, string $type): string;
    public function locate(string $substring, string $string): string;
    public function regexp(): string;
}
```

#### 2.2 MySQL Function Implementations

```php
class DBmysqlFunction implements DBFunctionInterface {
    public function groupConcat(string $expr, string $separator = ','): string {
        return "GROUP_CONCAT($expr SEPARATOR '$separator')";
    }
    
    public function ifNull(string $expr, string $default): string {
        return "IFNULL($expr, $default)";
    }
    
    public function dateAdd(string $date, string $interval, string $unit): string {
        return "ADDDATE($date, INTERVAL $interval $unit)";
    }
    
    public function dateDiff(string $expr1, string $expr2): string {
        return "DATEDIFF($expr1, $expr2)";
    }
    
    public function cast(string $expr, string $type): string {
        $typeMap = [
            'unsigned' => 'UNSIGNED',
            'signed' => 'SIGNED',
            'char' => 'CHAR',
            'date' => 'DATE',
            'datetime' => 'DATETIME',
            'time' => 'TIME',
        ];
        return "CAST($expr AS " . ($typeMap[$type] ?? $type) . ")";
    }
}
```

#### 2.3 PostgreSQL Function Implementations

```php
class DBpgsqlFunction implements DBFunctionInterface {
    public function groupConcat(string $expr, string $separator = ','): string {
        return "STRING_AGG($expr::text, '$separator')";
    }
    
    public function ifNull(string $expr, string $default): string {
        return "COALESCE($expr, $default)";
    }
    
    public function dateAdd(string $date, string $interval, string $unit): string {
        // PostgreSQL: ($date + INTERVAL '7 DAY')
        return "($date + INTERVAL '$interval $unit')";
    }
    
    public function dateDiff(string $expr1, string $expr2): string {
        // PostgreSQL: DATE_PART('day', $expr1 - $expr2)
        return "DATE_PART('day', $expr1 - $expr2)";
    }
    
    public function now(): string {
        return "NOW()"; // Same as MySQL
    }
    
    public function curDate(): string {
        return "CURRENT_DATE";
    }
    
    public function unixTimestamp(string $expr = ''): string {
        if (empty($expr)) {
            return "EXTRACT(EPOCH FROM NOW())";
        }
        return "EXTRACT(EPOCH FROM $expr)";
    }
    
    public function fromUnixTime(string $timestamp): string {
        return "TO_TIMESTAMP($timestamp)";
    }
    
    public function dateFormat(string $date, string $format): string {
        // Convert MySQL format to PostgreSQL
        $formatMap = [
            '%Y-%m-%d' => 'YYYY-MM-DD',
            '%Y-%m-%d %H:%i:%s' => 'YYYY-MM-DD HH24:MI:SS',
            '%H:%i:%s' => 'HH24:MI:SS',
            '%Y' => 'YYYY',
            '%m' => 'MM',
            '%d' => 'DD',
        ];
        $pgFormat = $formatMap[$format] ?? $format;
        return "TO_CHAR($date, '$pgFormat')";
    }
    
    public function cast(string $expr, string $type): string {
        $typeMap = [
            'unsigned' => 'BIGINT',
            'signed' => 'BIGINT',
            'char' => 'VARCHAR',
            'date' => 'DATE',
            'datetime' => 'TIMESTAMP',
            'time' => 'TIME',
        ];
        return "$expr::" . ($typeMap[$type] ?? $type);
    }
    
    public function locate(string $substring, string $string): string {
        return "POSITION($substring IN $string)";
    }
    
    public function regexp(): string {
        return '~'; // PostgreSQL uses ~ for regex matching
    }
}
```

#### 2.4 Integrate Functions into DB Class

```php
abstract class DBmysql implements DBInterface {
    protected $fn;
    
    public function __construct($choice = null) {
        $this->fn = $this->createFunctionAdapter();
        $this->connect($choice);
    }
    
    protected function createFunctionAdapter(): DBFunctionInterface {
        return new DBmysqlFunction();
    }
    
    public function fn(): DBFunctionInterface {
        return $this->fn;
    }
}

// Usage:
// $DB->fn()->groupConcat('name')
// $DB->fn()->dateAdd('date_field', '7', 'DAY')
```

---

### Phase 3: Query Builder Refactoring (Estimated: 2-3 days)

#### 3.1 Update DBmysqlIterator

- Remove static `DBmysql::quoteName()` calls
- Use instance method `$this->conn->quoteName()`
- Make `REGEXP` operator conditional based on driver
- Handle identifier quoting dynamically

**Before:**
```php
$ret .= DBmysql::quoteName($name) . ' ' . $this->analyseCriterion($value);
```

**After:**
```php
$ret .= $this->conn->quoteName($name) . ' ' . $this->analyseCriterion($value);
```

#### 3.2 Update QuerySubQuery and QueryUnion

Ensure they use the connection instance for quoting rather than static methods.

---

### Phase 4: Schema & Migration Support (Estimated: 3-4 days)

#### 4.1 Create PostgreSQL Schema File

Create `install/pgsql/glpi-empty.sql` with PostgreSQL-specific DDL:

```sql
-- PostgreSQL equivalent of glpi_alerts
DROP TABLE IF EXISTS glpi_alerts;
CREATE TABLE glpi_alerts (
  id SERIAL PRIMARY KEY,
  itemtype VARCHAR(100) NOT NULL,
  items_id INTEGER NOT NULL DEFAULT 0,
  type INTEGER NOT NULL DEFAULT 0,
  date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT unicity UNIQUE (itemtype, items_id, type)
);
CREATE INDEX idx_alerts_type ON glpi_alerts(type);
CREATE INDEX idx_alerts_date ON glpi_alerts(date);
```

#### 4.2 Update Migration Class

Add dialect-aware type mapping:

```php
class Migration {
    private function fieldFormat($type, $default_value, $nodefault = false) {
        global $DB;
        
        if ($DB instanceof DBpgsql) {
            return $this->fieldFormatPgsql($type, $default_value, $nodefault);
        }
        return $this->fieldFormatMysql($type, $default_value, $nodefault);
    }
    
    private function fieldFormatPgsql($type, $default_value, $nodefault = false) {
        switch ($type) {
            case 'bool':
            case 'boolean':
                return "BOOLEAN NOT NULL" . ($nodefault ? "" : " DEFAULT " . ($default_value ?? "FALSE"));
            case 'integer':
                return "INTEGER NOT NULL" . ($nodefault ? "" : " DEFAULT " . ($default_value ?? 0));
            case 'string':
                return "VARCHAR(255)" . ($nodefault ? "" : ($default_value === null ? " DEFAULT NULL" : " NOT NULL DEFAULT '$default_value'"));
            case 'text':
                return "TEXT" . ($nodefault ? "" : " DEFAULT NULL");
            case 'longtext':
                return "TEXT"; // PostgreSQL has no separate LONGTEXT
            case 'autoincrement':
                return "SERIAL";
            case 'timestamp':
            case 'datetime':
                return "TIMESTAMP" . ($nodefault ? "" : " DEFAULT " . ($default_value ?? "NULL"));
            default:
                return $type;
        }
    }
}
```

#### 4.3 Identify and Update MySQL-Specific Migration Scripts

Files requiring review:
- `install/update_*.php` (all update scripts)
- `inc/console/migration/*.php`
- Custom ITSM update scripts (`install/itsm_update_*.php`)

---

### Phase 5: Search Class Refactoring (Estimated: 2-3 days)

The `inc/search.class.php` file is the largest consumer of MySQL functions. Key changes needed:

#### 5.1 GROUP_CONCAT Replacements

**Before (MySQL):**
```php
return " GROUP_CONCAT(DISTINCT `$table$addtable`.`name` SEPARATOR '" . self::LONGSEP . "')";
```

**After (DBAL):**
```php
$expr = $DB->quoteName("$table$addtable.name");
return " " . $DB->fn()->groupConcat($expr, self::LONGSEP, true);
```

#### 5.2 Date Function Replacements

**Before:**
```php
"ADDDATE(`$table$addtable`.`date`, INTERVAL " . $searchopt[$ID]["datafields"][2] . " DAY)"
```

**After:**
```php
$DB->fn()->dateAdd(
    $DB->quoteName("$table$addtable.date"),
    $searchopt[$ID]["datafields"][2],
    'DAY'
)
```

#### 5.3 IFNULL Replacements

**Before:**
```php
"IFNULL(`$table$addtable`.`$field`, '" . self::NULLVALUE . "')"
```

**After:**
```php
$DB->fn()->ifNull($DB->quoteName("$table$addtable.$field"), $DB->quoteValue(self::NULLVALUE))
```

---

### Phase 6: Installer & CLI Updates (Estimated: 2 days)

#### 6.1 Update install/install.php

Replace direct `mysqli` usage with DBAL factory:

```php
// Before
$link = new mysqli($hostport[0], $_SESSION['db_user'], $_SESSION['db_pass']);

// After
$dbConfig = [
    'dbhost' => $_SESSION['db_host'],
    'dbuser' => $_SESSION['db_user'],
    'dbpassword' => $_SESSION['db_pass'],
    'dbdefault' => $_SESSION['databasename'],
    'dbtype' => $_SESSION['db_type'] ?? 'mysql',
];
$link = DBFactory::createTestConnection($dbConfig);
```

#### 6.2 Update Console Commands

- `inc/console/database/installcommand.class.php`
- `inc/console/database/abstractconfigurecommand.class.php`

Add `--db-type` option and use DBAL for connections.

#### 6.3 Add PostgreSQL Schema Option to Installer

Update installer UI to offer PostgreSQL as an option, and load the appropriate schema file.

---

### Phase 7: System Requirements Updates (Estimated: 1 day)

#### 7.1 Update Extension Checks

**inc/config.class.php:**
```php
$extensions_to_check = [
    'mysqli' => ['required' => false],  // MySQL only
    'pdo_pgsql' => ['required' => false], // PostgreSQL
    'pgsql' => ['required' => false],    // Alternative PG extension
    // ... other extensions
];
```

#### 7.2 Create New Requirement Classes

```
inc/system/requirement/
├── dbextension.class.php      # Checks for mysqli OR pdo_pgsql
└── pgsqlextension.class.php   # PostgreSQL-specific checks
```

---

### Phase 8: Direct Result Access Cleanup (Estimated: 1 day)

Replace direct `mysqli_result` method calls with DB abstraction:

| File | Line | Current | Fix |
|------|------|---------|-----|
| `inc/ticket.class.php` | 4373 | `$result->fetch_assoc()` | `$DB->fetchAssoc($result)` |
| `inc/api/api.class.php` | 1427 | `->fetch_row()[0]` | `$DB->fetchRow($result)[0]` |
| `install/install.php` | 140, 158 | `->fetch_array()` | `$DB->fetchArray()` |
| `inc/console/database/installcommand.class.php` | 268 | `->fetch_array()[0]` | Use DBAL |

---

### Phase 9: Testing & Validation (Estimated: 3-4 days)

#### 9.1 Unit Tests

Create tests for:
- `tests/units/DBpgsql.php`
- `tests/units/DBmysqlFunction.php`
- `tests/units/DBpgsqlFunction.php`
- `tests/units/DBFactory.php`

#### 9.2 Integration Tests

- Test full installation on PostgreSQL
- Test migration paths on PostgreSQL
- Test search functionality on PostgreSQL
- Test API endpoints on PostgreSQL

#### 9.3 Continuous Integration

Add PostgreSQL to CI pipeline:
```yaml
# .github/workflows/tests.yml
services:
  postgres:
    image: postgres:13
    env:
      POSTGRES_PASSWORD: itsmpassword
      POSTGRES_DB: itsm_main_test
```

---

## MySQL to PostgreSQL Function Mapping Reference

| MySQL | PostgreSQL | Notes |
|-------|------------|-------|
| `GROUP_CONCAT(DISTINCT x SEPARATOR ',')` | `STRING_AGG(x::text, ',')` | PG needs cast to text |
| `IFNULL(x, y)` | `COALESCE(x, y)` | Direct replacement |
| `IF(cond, t, f)` | `CASE WHEN cond THEN t ELSE f END` | |
| `NOW()` | `NOW()` | Same |
| `CURDATE()` | `CURRENT_DATE` | |
| `ADDDATE(d, INTERVAL n UNIT)` | `d + INTERVAL 'n UNIT'` | |
| `DATE_SUB(d, INTERVAL n UNIT)` | `d - INTERVAL 'n UNIT'` | |
| `DATEDIFF(d1, d2)` | `DATE_PART('day', d1 - d2)` | |
| `UNIX_TIMESTAMP(d)` | `EXTRACT(EPOCH FROM d)` | |
| `FROM_UNIXTIME(ts)` | `TO_TIMESTAMP(ts)` | |
| `DATE_FORMAT(d, '%Y-%m-%d')` | `TO_CHAR(d, 'YYYY-MM-DD')` | Different format codes |
| `TIME_TO_SEC(t)` | `EXTRACT(EPOCH FROM t)` | |
| `TIMEDIFF(t1, t2)` | `t1 - t2` | Returns interval |
| `LOCATE(sub, str)` | `POSITION(sub IN str)` | |
| `REGEXP` | `~` | PG uses tilde |
| `INSERT IGNORE` | `INSERT ... ON CONFLICT DO NOTHING` | |
| `REPLACE INTO` | `INSERT ... ON CONFLICT DO UPDATE` | |
| `AUTO_INCREMENT` | `SERIAL` or `IDENTITY` | |
| `UNSIGNED` | (No equivalent) | Use CHECK constraint |
| `ENGINE=InnoDB` | (N/A) | Remove |
| `COLLATE utf8_unicode_ci` | (N/A or LC_COLLATE) | DB-level setting |

---

## Risk Assessment

### High Risk Areas

1. **Search Class** - Most complex SQL generation, heavy MySQL function usage
2. **Migration Scripts** - Many contain raw MySQL DDL
3. **Replication/Slave Support** - MySQL-specific in `dbconnection.class.php`

### Medium Risk Areas

1. **Plugin Compatibility** - Third-party plugins may use MySQL-specific SQL
2. **Performance** - Query plans may differ significantly
3. **Character Encoding** - Different handling between engines

### Low Risk Areas

1. **Core CRUD Operations** - Well-abstracted through `CommonDBTM`
2. **Query Builder** - Mostly standard SQL
3. **Transaction Handling** - Standard COMMIT/ROLLBACK

---

## Rollout Strategy

### Stage 1: Internal Testing
- Complete DBAL implementation
- Test with existing MySQL setup (regression testing)
- Unit and integration tests

### Stage 2: PostgreSQL Alpha
- Basic PostgreSQL support
- Fresh install only
- Limited feature testing

### Stage 3: PostgreSQL Beta
- Full feature parity testing
- Migration tooling
- Documentation

### Stage 4: Production Ready
- Both MySQL and PostgreSQL fully supported
- Migration guide published
- CI/CD tests both databases

---

## Files to Create/Modify Summary

### New Files
- `inc/db/dbinterface.interface.php`
- `inc/db/dbpgsql.class.php`
- `inc/db/dbfactory.class.php`
- `inc/db/dbfunction.interface.php`
- `inc/db/dbmysqlfunction.class.php`
- `inc/db/dbpgsqlfunction.class.php`
- `install/pgsql/glpi-empty.sql`
- `inc/system/requirement/dbextension.class.php`

### Files to Modify
- `inc/dbmysql.class.php` - Refactor for abstraction
- `inc/dbmysqliterator.class.php` - Remove static calls
- `inc/querysubquery.class.php` - Use instance methods
- `inc/queryunion.class.php` - Use instance methods
- `inc/migration.class.php` - Add dialect support
- `inc/search.class.php` - Replace MySQL functions
- `inc/ticket.class.php` - Replace MySQL functions
- `inc/contract.class.php` - Replace MySQL functions
- `inc/commonitilobject.class.php` - Replace MySQL functions
- `inc/install.php` - Use DBAL factory
- `inc/dbconnection.class.php` - Support both DB types
- `inc/config.class.php` - Update extension checks
- `inc/console/database/*.php` - Add PostgreSQL support
- `config/config_db.php` - Add `dbtype` property
- `config/config_db_pg.php` - Extend `DBpgsql`

---

## Estimated Timeline

| Phase | Duration | Dependencies |
|-------|----------|--------------|
| Phase 1: DBAL Foundation | 3-4 days | None |
| Phase 2: Function Abstraction | 2-3 days | Phase 1 |
| Phase 3: Query Builder | 2-3 days | Phase 1 |
| Phase 4: Schema & Migration | 3-4 days | Phase 1, 2 |
| Phase 5: Search Refactoring | 2-3 days | Phase 2 |
| Phase 6: Installer Updates | 2 days | Phase 1, 4 |
| Phase 7: Requirements | 1 day | None |
| Phase 8: Result Cleanup | 1 day | Phase 1 |
| Phase 9: Testing | 3-4 days | All phases |
| **Total** | **19-25 days** | |

---

## Next Steps

1. Review and approve this plan
2. Begin Phase 1: Create DBAL foundation with interface and base classes
3. Set up PostgreSQL development environment for testing
4. Create feature branch for DBAL work
5. Begin incremental implementation with continuous testing against MySQL
