Place new PSR-4 schema migrations in this directory.

Each migration must:
- extend `itsmng\Database\Migrations\Migration`
- declare `#[itsmng\Database\Migrations\Attribute\SchemaMigration(...)]`
- implement `up()` and `down()`
