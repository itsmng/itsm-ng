Place new PSR-4 schema migrations in this directory.

Each migration must:
- extend `itsmng\Database\Migrations\Migration`
- declare `#[itsmng\Database\Migrations\Attribute\SchemaMigration(...)]`
- use the lexical version format `YYYYMMDDHHMM_Name` for both the filename and the attribute `version`
- implement `up()` and `down()`

When a foreign-key candidate needs non-default semantics, encode it explicitly in the migration policy map instead of adding new implicit heuristics.
