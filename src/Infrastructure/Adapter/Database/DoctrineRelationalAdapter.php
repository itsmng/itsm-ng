<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;
use ReflectionClass;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Itsmng\Infrastructure\Persistence\EntityManagerProvider;

class DoctrineRelationalAdapter implements DatabaseAdapterInterface
{
    public string $class;

    private EntityManager $em;
    private $entityName;

    public function __construct(string|CommonDBTM $class)
    {
        $this->class = $class;
        $this->em = EntityManagerProvider::getEntityManager();
        $this->entityName = (new $class())->entity;
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
            dump('Error in findOneBy:', $e->getMessage());
            return null;
        }
    }

    // public function findEntityById(array $id): ?EntitiesEntity
    // {
    //     return $this->em->getRepository(EntitiesEntity::class)->findOneBy(['id' => $id]);
    // }

    public function findEntityById(array $id): mixed
    {
        return $this->em->getRepository($this->entityName)->findOneBy(['id' => $id]);
    }


    public function findBy(array $criteria, array $order = null, int $limit = null): array
    {
        // TODO: Implement findBy() method.
        $result = $this->em->getRepository($this->entityName)->findBy($criteria);
        return $result;
    }
    public function findByRequest(array $request): array
    {

        return [];
    }

    public function deleteByCriteria(array $criteria): bool
    {
        // TODO: Implement deleteByCriteria() method.

        return false;
    }

    // list columns from entity
    public function listFields(): array
    {
        $metadata = $this->em->getClassMetadata($this->entityName);
        $DoctrineFields = $metadata->getFieldNames();
        $DoctrineRelations = $metadata->getAssociationNames();
        $fields = [];
        foreach ($DoctrineFields as $field) {
            $fields[$this->toSnakeCase($field)] = $field;
        }
        foreach ($DoctrineRelations as $relation) {
            $fields[$this->toSnakeCase($relation, true)] = $relation;
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

    private function plurialize($word): string
    {
        $word = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $word));
        if (mb_substr($word, -1, 1) == 'y') {
            $word = mb_substr($word, 0, -1) . 'ies';
        } else {
            $word .= 's';
        }
        return $word;
    }

    protected function toSnakeCase($input, bool $isRelation = false): string
    {
        $input = preg_replace('/[A-Z]/', '_$0', $input);
        $input = strtolower(ltrim($input, '_'));

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

    private function toCamelCase(string $input): string
    {
        if (str_ends_with($input, '_id')) {
            $input = substr($input, 0, -3);
        }

        // Remettre "ies" en "y" si c'est un mot au singulier
        if (preg_match('/ies$/', $input)) {
            $input = preg_replace('/ies$/', 'y', $input);
        }

        $parts = explode('_', $input);
        $parts = array_map('ucfirst', $parts);
        $camelCase = lcfirst(implode('', $parts));

        return $camelCase;
    }

    // get values from entity as array
    public function getFields($content): array
    {
        if (is_array($content)) {
            return $content;
        }

        $fields = [];

        try {
            $getters = $this->getPropertiesAndGetters($content);

            foreach ($getters as $propertyName => $getter) {
                if (!method_exists($content, $getter)) {
                    continue; // Évite d’appeler un getter inexistant
                }

                try {
                    $value = $content->$getter(); // Appel dynamique du getter
                } catch (\Exception $e) {
                    dump("Erreur lors de l'appel de $getter :", $e->getMessage());
                    continue;
                }

                // Vérifier si la propriété est une relation Doctrine
                $isRelation = false;
                $reflectionProperty = new \ReflectionProperty($content, $propertyName);
                $attributes = $reflectionProperty->getAttributes();
                foreach ($attributes as $attribute) {
                    if (in_array($attribute->getName(), [
                        'Doctrine\ORM\Mapping\ManyToOne',
                        'Doctrine\ORM\Mapping\OneToOne',
                        'Doctrine\ORM\Mapping\ManyToMany',
                        'Doctrine\ORM\Mapping\OneToMany'
                    ])) {
                        $isRelation = true;
                        break;
                    }
                }

                // Convertir le nom en snake_case
                $snakeCaseKey = $this->toSnakeCase($propertyName, $isRelation);

                // Cas particuliers pour certaines clés
                if ($snakeCaseKey === 'entity_id' || $snakeCaseKey === 'entity' || $snakeCaseKey === 'entities') {
                    $snakeCaseKey = 'entities_id';
                }
                if (preg_match('/^priority(\d+)$/', $propertyName, $matches)) {
                    $snakeCaseKey = 'priority_' . $matches[1];
                }

                // Gestion des valeurs selon leur type
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
            // dump('final fields'.$this->entityName, $fields);
            return $fields;

        } catch (\Exception $e) {
            dump('Error in getFields:', $e->getMessage());

            return $fields;
        }
    }




    public function getSettersFromFields(array $fields, object $content): array
    {
        $reflect = new ReflectionClass($content);
        $properties = $reflect->getProperties();

        $availableProperties = array_map(
            function ($property) {
                return $property->getName();
            },
            $properties
        );

        $setters = [];
        foreach ($fields as $field => $value) {
            $originalField = $field;
            // Special case for entities_id
            if ($field === 'entities_id') {
                $setter = 'setEntity';
                if (method_exists($content, $setter)) {
                    $setters[$originalField] = $setter;
                }
                continue;
            }

            if (str_ends_with($field, '_id')) {
                $field = substr($field, 0, -3);
            }
            $camelCaseField = $this->toCamelCase($field);


            if (in_array($camelCaseField, $availableProperties)) {
                $setter = 'set' . ucfirst($camelCaseField);

                if (method_exists($content, $setter)) {
                    $setters[$originalField] = $setter;
                }
            }
        }
        return $setters;
    }

    public function save(array $fields): bool
    {
        // TODO: Implement save() method.
        return false;
    }

    public function add(array $fields): bool|array
    {

        // Create new entity
        $entity = new $this->entityName();

        // Transform fields using getFields
        $transformedFields = $this->getFields($fields);

        // Get setters for fields
        $setters = $this->getSettersFromFields($transformedFields, $entity);

        foreach ($setters as $field => $setter) {
            if (!isset($transformedFields[$field])) {
                continue;
            }

            $value = $transformedFields[$field];

            try {
                $reflectionMethod = new \ReflectionMethod($entity, $setter);
                $parameters = $reflectionMethod->getParameters();
                $paramType = $parameters[0]->getType();

                if ($paramType) {
                    $typeName = $paramType->getName();

                    if (class_exists($typeName)) {
                        if (is_numeric($value)) {
                            $value = $this->em->getRepository($typeName)->find((int)$value);
                        } elseif (is_object($value) && !($value instanceof $typeName)) {
                            continue;
                        }
                    } elseif ($typeName === \DateTimeInterface::class) {
                        $value = $value ? new \DateTime($value) : null;
                    }
                }

                if ($value !== null || ($paramType && $paramType->allowsNull())) {
                    $entity->$setter($value);
                }

            } catch (\Exception $e) {
                $e->getMessage();
                continue;
            }
        }

        try {
            $this->em->persist($entity);
            $this->em->flush();

            return ['id' => $entity->getId()];
        } catch (\Exception $e) {
            $e->getMessage();
            return false;
        }
    }




    public function getRelations(): array
    {
        // TODO: Implement getRelations() method.
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



    public function request(array | QueryBuilder $criteria): \Iterator
    {
        if ($criteria instanceof QueryBuilder) {
            return new \ArrayIterator($criteria->getQuery()->getResult());
        }

        if (!is_array($criteria)) {
            throw new \InvalidArgumentException('Expected array or QueryBuilder, got ' . gettype($criteria));
        }

        if (empty($criteria['table'])) {
            throw new \InvalidArgumentException('The "table" key is required in the criteria array.');
        }

        $table = $criteria['table'];
        $alias = $criteria['alias'] ?? 't';
        $conditions = $criteria['conditions'] ?? [];
        $orderBy = $criteria['orderBy'] ?? [];
        $limit = $criteria['limit'] ?? null;
        $offset = $criteria['offset'] ?? null;

        $qb = $this->em->createQueryBuilder();

        $qb->select($alias)
           ->from($table, $alias);


        foreach ($conditions as $field => $value) {
            $parameterName = str_replace('.', '_', $field);
            if (is_array($value)) {
                $qb->andWhere("$alias.$field IN (:$parameterName)")
                   ->setParameter($parameterName, $value);
            } else {
                $qb->andWhere("$alias.$field = :$parameterName")
                   ->setParameter($parameterName, $value);
            }
        }

        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy("$alias.$field", strtoupper($direction));
        }

        if ($limit !== null) {
            $qb->setMaxResults((int)$limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult((int)$offset);
        }

        return $qb->getQuery()->getResult();
        // return [];
    }




}
