<?php

namespace Infrastructure\Adapter\Database;

use ArrayIterator;
use CommonDBTM;
use DateTime;
use DBmysqlIterator;
use Doctrine\DBAL\Result;
use ReflectionClass;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Html;
use Itsmng\Infrastructure\Persistence\EntityManagerProvider;
use Traversable;

class DoctrineRelationalAdapter implements DatabaseAdapterInterface
{
    public string $class;

    private EntityManager $em;
    private $entityName;
    private bool $isSpecialCase = false;
    private ?string $fallbackTableName = null;

    public function __construct(string|CommonDBTM $class)
    {
        $this->class = $class;
        $this->em = EntityManagerProvider::getEntityManager();
        $entityPrefix = '\Itsmng\Domain\Entities\\';
        $tableClass = '';
        $currentClass = $class;
        while (empty($tableClass)) {
            $parent = get_parent_class($currentClass);
            if (!$parent
                || !method_exists($parent, 'getTable')
                || $currentClass::getTable() != $parent::getTable()
            ) {
                $classPath = explode('\\', $currentClass::getType());
                $basename = end($classPath);
                $tableClass = str_replace('_', '', $basename);
                break;
            }
            $currentClass = get_parent_class($currentClass);
            if (!$currentClass) {
                throw new \Exception("Class does not have getTable function");
            }
        }
        $entityName = $entityPrefix . $tableClass;

        if (!isset($entityName) || !class_exists($entityName)) {
            $this->entityName = null;
            $this->isSpecialCase = true;
            $this->fallbackTableName = $currentClass::getTable();
            return;
        }

        $this->entityName = $entityName;
        $this->isSpecialCase = false;
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
        return $this->em->getConnection();
    }

    /**
     * Get the value of em
     */
    public function getEntityManager()
    {
        return $this->em;
    }


    public function findOneBy(array $criteria): mixed
    {
        if (!class_exists($this->entityName)) {
            throw new \Exception("Entity class {$this->entityName} does not exist");
        }
        try {
            $repository = $this->em->getRepository($this->entityName);
            $result = $repository->findOneBy($criteria);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error in findOneBy: " . $e->getMessage());
            return null;
        }
    }

    public function findBy(array $criteria, array $order = null, int $limit = null): array
    {
        $result = $this->em->getRepository($this->entityName)->findBy($criteria);
        return $result;
    }
    public function findByRequest(array $request): array
    {

        return [];
    }

    public function deleteByCriteria(array $criteria): bool
    {
        $em = $this->em;
        $items = $this->findBy($criteria);
        foreach ($items as $item) {
            $em->remove($item);
        }
        $em->flush();

        return false;

    }

    // list columns from entity
    public function listFields(): array
    {
        if ($this->isSpecialCase || $this->entityName === null) {

            $conn = $this->em->getConnection();
            $schemaManager = $conn->createSchemaManager();
            $columns = $schemaManager->listTableColumns($this->fallbackTableName);

            $fields = [];
            foreach ($columns as $column) {
                $fieldName = $column->getName();
                $entityFieldName = $this->toEntityFormat($fieldName);
                $fields[$fieldName] = $entityFieldName;
            }

            return $fields;
        }
        $metadata = $this->em->getClassMetadata($this->entityName);
        $DoctrineFields = $metadata->getFieldNames();
        $DoctrineRelations = $metadata->getAssociationNames();
        $fields = [];
        foreach ($DoctrineFields as $field) {
            $fields[$this->toDbFormat($field)] = $field;
        }
        foreach ($DoctrineRelations as $relation) {
            $fields[$this->toDbFormat($relation, true)] = $relation;
        }
        return $fields;
    }


    private function getPropertiesAndGetters($content): array
    {
        $reflect = new ReflectionClass($content);
        $properties = $reflect->getProperties();
        $names = array_map(
            function ($property) {
                return $property->getName();
            },
            $properties,
        );
        $getters = array_map(
            function ($property) {
                $name = $property->getName();
                $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
                $name = str_replace('_', '', $name);
                return 'get' . $name;
            },
            $properties,
        );
        return array_combine($names, $getters);
    }

    protected function toDbFormat($input, bool $isRelation = false): string
    {
        if (preg_match('/^(phone|mobile|fax)\d+$/', $input)) {
            return $input;
        }

        if (preg_match('/^(priority)(\d+)$/', $input, $matches)) {
            return $matches[1] . '_' . $matches[2];
        }
        $input = preg_replace('/[A-Z]/', '_$0', $input);
        $input = mb_strtolower(ltrim($input, '_'));

        if ($isRelation) {
            if (str_ends_with($input, 'y')) {
                $input = preg_replace('/y$/', 'ies', $input);
            } else {
                $input .= 's';
            }

            $input .= '_id';
        }
        return $input;
    }

    private static function toEntityFormat(string $input, bool $expandId = true): string
    {
        $isRelation = str_ends_with($input, 's_id');
        if ($isRelation && $expandId) {
            $input = substr($input, 0, -4);
            if (str_ends_with($input, 'ie')) {
                $input = substr($input, 0, -2);
                $input .= 'y';
            }
        }
        if (preg_match('/^(phone|mobile|fax)\d+$/', $input)) {
            return $input;
        }

        if (preg_match('/^(priority)_(\d+)$/', $input, $matches)) {
            return $matches[1] . $matches[2];
        }
        $input = lcfirst(str_replace('_', '', ucwords($input, '_')));
        return $input;
    }

    private static function getLinkedEntity(object $object, string $field): string | null
    {
        $property = self::toEntityFormat($field);
        if (!property_exists($object, $property)) {
            return null;
        }
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $attributes = $reflectionProperty->getAttributes();
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), [
                'Doctrine\ORM\Mapping\ManyToOne',
                'Doctrine\ORM\Mapping\OneToOne',
                'Doctrine\ORM\Mapping\ManyToMany',
                'Doctrine\ORM\Mapping\OneToMany'
            ])) {
                $targetEntity = $attribute->getArguments()['targetEntity'];
                return $targetEntity;
            }
        }
        return null;
    }

    private static function isDateFormat(object $object, string $field): bool
    {
        $property = self::toEntityFormat($field);
        if (!property_exists($object, $property)) {
            return false;
        }
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $attributes = $reflectionProperty->getAttributes();
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), [
                'Doctrine\ORM\Mapping\Column',
            ])) {
                $type = $attribute->getArguments()['type'];
                if ($type === 'date' || $type === 'datetime') {
                    return true;
                }
            }
        }
        return false;
    }

    private static function isRelation(object $content, string $property): bool
    {
        return self::getLinkedEntity($content, $property) !== null;
    }

    public function getFields($content): array
    {
        if (is_array($content)) {
            return $content;
        }

        $fields = [];

        $getters = $this->getPropertiesAndGetters($content);

        foreach ($getters as $propertyName => $getter) {
            if (!method_exists($content, $getter)) {
                continue;
            }

            try {
                $value = $content->$getter();
            } catch (\Exception $e) {
                throw new \Exception('Cannot get value for property ' . $propertyName . ' of class ' . get_class($content));
                continue;
            }

            $isRelation = self::isRelation($content, $propertyName);

            $snakeCaseKey = $this->toDbFormat($propertyName, $isRelation);

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            if ($isRelation && $value !== null && method_exists($value, 'getId')) {
                $fields[$snakeCaseKey] = $value->getId();
            } elseif (is_object($value)) {
                if (method_exists($value, 'getId')) {
                    $fields[$snakeCaseKey] = $value->getId();
                }
            } else {
                if (!($value instanceof \Closure) && !($value instanceof \Doctrine\ORM\PersistentCollection)) {
                    $fields[$snakeCaseKey] = $value;
                }
            }
        }
        return $fields;
    }

    public function getSettersFromFields(array $fields): array
    {
        $setters = [];
        foreach ($fields as $field) {
            $setter = 'set' . ucfirst(self::toEntityFormat($field));
            if (method_exists($this->entityName, $setter)) {
                $setters[$field] = $setter;
            } else {
                $newSetter = self::toEntityFormat($field, false);
                $setter = 'set' . ucfirst(self::toEntityFormat($newSetter));
                if (method_exists($this->entityName, $setter)) {
                    $setters[$field] = $setter;
                }
            }
        }
        return $setters;
    }

    public function save(array $fields): bool
    {
        if (!isset($fields['id'])) {
            return false;
        }

        $entity = $this->findOneBy(['id' => $fields['id']]);
        $setters = $this->getSettersFromFields(array_keys($fields));
        $object = new $this->entityName();

        foreach ($fields as $field => $value) {
            try {
                $linkedEntity = self::getLinkedEntity($object, $field);
            } catch (\Exception $e) {
                $linkedEntity = null;
            }
            if (self::isDateFormat($object, $field) && !($value instanceof \DateTime)) {
                if ($value === false || $value === null) {
                    $value = null;
                } else {
                    $value = DateTime::createFromFormat('Y-m-d H:i:s', $fields[$field]);
                }
                if ($value === false) {
                    $value = DateTime::createFromFormat('Y-m-d', $fields[$field]);
                }
                if ($value === false) {
                    $value = null;
                }
            } elseif ($linkedEntity !== null) {
                $value = self::getReferencedEntity($linkedEntity, intval($fields[$field]));
            } else {
                $value = $fields[$field];
            }
            $setter = $setters[$field] ?? '';
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }
        try {
            $this->em->persist($entity);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('error: ' . $e->getMessage());
            return false;
        }
    }

    private function getReferencedEntity(string $entity, int $id): object | int | null
    {
        if (!class_exists($entity)) {
            return $id;
        } elseif ($id === 0 && $entity !== \Itsmng\Domain\Entities\Entity::class) {
            return null;
        }
        return $this->em->getReference($entity, $id);
    }

    public function add(array $fields): bool|array
    {
        $entity = new $this->entityName();

        $setters = $this->getSettersFromFields(array_keys($fields));

        $object = new $this->entityName();
        foreach ($setters as $field => $setter) {
            if (!isset($fields[$field])) {
                continue;
            }

            try {
                $linkedEntity = self::getLinkedEntity($object, $field);
            } catch (\Exception $e) {
                $linkedEntity = null;
            }
            if ($linkedEntity !== null) {
                $value = self::getReferencedEntity($linkedEntity, intval($fields[$field]));
            } else {
                $value = $fields[$field];
            }
            $entity->$setter($value);
        }
        try {
            $this->em->persist($entity);
            $this->em->flush();

            return ['id' => $entity->getId()];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
            return false;
        }
    }




    public function getRelations(): array
    {
        $classMetadata = $this->em->getClassMetadata($this->entityName);
        $relations = $classMetadata->getAssociationMappings();
        $formattedRelations = [];
        foreach ($relations as $relationName => $relationDetails) {
            $formattedRelations[] = [
                'field' => $relationName,
                'type' => $relationDetails['type'], // relation typen (ex: ManyToOne)
                'targetEntity' => $relationDetails['targetEntity'], // target class
                'mappedBy' => $relationDetails['mappedBy'] ?? null, // if applicable
                'inversedBy' => $relationDetails['inversedBy'] ?? null, // if applicable
            ];
        }

        return $formattedRelations;

        // return [];
    }


    public function request(array $request): Result
    {
        global $DB;

        $SqlIterator = new DBmysqlIterator($DB);
        if ($this->isSpecialCase && $this->fallbackTableName) {
            if (!isset($request['FROM'])) {
                $request['FROM'] = $this->fallbackTableName;
            }
        }
        $query = $SqlIterator->buildQuery($request);
        // dump('SQL Query', $query);
        return $this->query($query);
    }

    public function query(string $query): Result
    {
        $stmt = $this->em->getConnection()->prepare($query);
        $results = $stmt->executeQuery();

        return $results;
    }
}
