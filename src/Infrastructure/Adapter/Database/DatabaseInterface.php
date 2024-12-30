<?php

namespace Infrastructure\Adapter\Database;

interface DatabaseInterface
{
    public function getFromDB(int $ID): bool;
    public function getFromResultSet(array $rs): void;
    public function getFromDBByCrit(array $crit): bool|array;
    public function post_getFromDB(): void;
    public function find(array $condition = [], array|string $order = [], int $limit = null): array;

    public function getEmpty(): bool;
    public function post_getEmpty(): void;

    public function add($input, $options = [], $history = true): int;
    public function addToDB(): int|bool;
    public function post_addItem(): void;
    public function addFiles(array $input, array $options = []): array;

    public function update(array $input, bool $history = 1, array $options = []): bool;
    public function pre_updateInDB(): void;
    public function updateInDB(array $updates, array $oldvalues = []): void;
    public function prepareInputForUpdate(array $input): array;
    public function post_updateItem(bool $history = 1): void;

    public function restore(array $input, bool $history = 1): bool;
    public function restoreInDB(): bool;
    public function post_restoreItem(): void;

    public function delete(array $input, bool $force = 0, bool $history = 1): bool;
    public function pre_deleteItem(): bool;
    public function deleteFromDB($force = 0): bool;
    public function cleanDBonMarkDeleted(): void;
    public function post_deleteFromDB(): void;
    public function post_deleteItem(): void;
    public function deleteByCriteria(array $crit, bool $force = 0, bool $history = 1): bool;

    public function cleanDBonPurge(): void;
    public function post_purgeItem(): void;

    public function clone(array $override_input = [], bool $history = true): int|bool;
    public function cloneMultiple($n, $override_input = [], $history = true): bool;
    public function computeCloneName(string $current_name, int $copy_index): string;
    public function prepareInputForClone(array $input): array;
    public function post_clone($source, bool $history): void;

    public function cleanTranslations(): void;

    public function cleanHistory(): void;
    public function cleanRelationData(): void;
    public function cleanRelationTable(): void;

    public function addNeededInfoToInput(array $input): array;
    public function prepareInputForAdd(array $input): array;

    public function reset(): void;

    public function can(int $ID, mixed $right, array &$input = null): bool;
    public function canGlobal(mixed $right): void;
    public function canAddItem(string $type): bool;
    public function canCreateItem(): bool;
    public function canUpdateItem(): bool;
    public function canDeleteItem(): bool;
    public function canPurgeItem(): bool;
    public function canViewItem(): bool;
    public function canEdit($ID): bool;
    public function canUnrecurs(): bool;
    public function canMassiveAction($action, $field, $value): bool;

    public function check($ID, $right, array &$input = null): bool;
    public function checkEntity(bool $recursive = false): bool;
    public function checkGlobal(mixed $right): void;
    public function checkSpecificValues(string $datatype, mixed $value): bool;
    public function checkUnicity(bool $add = false, array $options = []): bool;

    public function initForm(int $ID, array $options = []): int|null;
    public static function isNewID($ID): bool;
    public function isNewItem(): bool;
    public function isEntityAssign(): bool;
    public function maybeRecursive(): bool;
    public function isRecursive(): bool;
    public function maybeDeleted(): bool;
    public function isDeleted(): bool;
    public function maybeActive(): bool;
    public function isActive(): bool;
    public function maybeTemplate(): bool;
    public function isTemplate(): bool;
    public function maybeDynamic(): bool;
    public function isDynamic(): bool;
    public function maybePrivate(): bool;
    public function isPrivate(): bool;
    public function maybeLocated(): bool;
    public function isField(string $field): bool;

    public function getID(): int;
    public function getLogTypeID(): array;
    public function getLinkURL(): string;
    public function getLinkedItems(): array;
    public function getLinkedItemsCount(): int;
    public function getEntityID(): int;
    public function getField(string $field): string;
    public function getComments(): string;
    public static function getNameField(): string;
    public static function getCompleteNameField(): string;
    public function getRawName(): string;
    public function getRawCompleteName(): string;
    public function getName($options = []): string;
    public function getFriendlyName(): string;
    public function getPreAdditionalInfosForName(): string;
    public function getPostAdditionalInfosForName(): string;
    public function getNameID($options = []): string;
    public function searchOptions(): array;
    public function rawSearchOptions(): array;
    public static function getSearchOptionsToAdd(string $itemtype = null): array;
    public static function getMassiveActionsForItemtype(array &$actions, string $itemtype, bool $is_deleted = 0, mixed $checkitem = null): void;
    public function getForbiddenStandardMassiveActions(): array;
    public function getForbiddenSingleMassiveActions(): array;
    public function getWhitelistedSingleMassiveActions(): array;
    public function getSpecificMassiveActions(mixed $checkitem = null): array;
    public function getSearchOptionByField(string $field, string $value, string $table = ''): array;
    public function getOptions(): array;
    public function getSearchOptionIdByField(string $field, string $value, string $table = ''): array;
    public static function getSpecificValueToDisplay(string $field, mixed $values, array $options = []): string;
    public function getValueToDisplay(string $field_id_or_search_options, mixed $values, array $options = []): string;

    public function saveInput(): void;
    public function clearSavedInput(): void;
    public function restoreInput(array $default = []): array;
    public function restoreSavedValues(array $saved = []): array;
}
