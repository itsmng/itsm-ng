<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;
use ReflectionClass;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Itsmng\Domain\Entities\Entity as EntitiesEntity;
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
        
        // $result = $this->em->find($this->entityName, $criteria);      

        // return $result;
        $result = $this->em->getRepository($this->entityName)->findOneBy($criteria);
        
        return $result;
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
        // TODO: Implement listFields() method.
        $metadata = $this->em->getClassMetadata($this->entityName);
        return $metadata->getFieldNames();
        // return [];
    }

    // public function getTableFields($content = null): array
    // {
    //     // dump($this->em->getMetadataFactory()->hasMetadataFor($this->entityName));

    //     $metadata = $this->em->getClassMetadata($this->entityName);
    //     $table_fields = [];

    //     // simple fields conversions
    //     foreach ($metadata->fieldMappings as $fieldName => $mapping) {
    //         $snakeField = $this->toSnakeCase($fieldName);
    //         $table_fields[$snakeField] = null;
    //     }

    //     // relationships conversions
    //     foreach ($metadata->associationMappings as $fieldName => $mapping) {
    //         // special case for entity
    //         if ($fieldName === 'entity') {
    //             $table_fields['entities_id'] = null;
    //             continue;
    //         }

    //         $snakeField = $this->toSnakeCase($fieldName);

    //         // separate parts for special prefix (like "tech_")
    //         $parts = explode('_', $snakeField);
    //         $lastPart = end($parts);

    //         // pluralize the last part
    //         $pluralLastPart = $this->plurialize($lastPart);

    //         // Rebuild the field name
    //         array_pop($parts);
    //         array_push($parts, $pluralLastPart);
    //         $finalField = implode('_', $parts) . '_id';

    //         $table_fields[$finalField] = null;
    //     }
    //     return $table_fields;
    // }




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

    protected function toSnakeCase($input): string
    {
        $pattern = '/[A-Z]/';
        $replacement = '_$0';
        return strtolower(ltrim(preg_replace($pattern, $replacement, $input), '_'));
    }


    private function toCamelCase(string $input): string
    {
        if (str_ends_with($input, '_id')) {
            $input = substr($input, 0, -3);
        }

        if (str_ends_with($input, 's')) {
            $input = substr($input, 0, -1);
        }
        $parts = explode('_', $input);
        $parts = array_map('ucfirst', $parts);
        $camelCase = lcfirst(implode('', $parts));

        return $camelCase;
    }

    // get values from entity as array
    public function getFields($content): array
    {
        $propertiesAndGetters = $this->getPropertiesAndGetters($content);
        $fields = [];

        $reflectionClass = new \ReflectionClass($content::class);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = $reflectionProperty->getName();
            $snakeCaseKey = $this->toSnakeCase($property);
            $getter = $propertiesAndGetters[$property] ?? null;

            // recover the value
            if ($getter && method_exists($content, $getter)) {
                $value = $content->$getter();
            } else {
                // try to access directly to the property
                $reflectionProperty->setAccessible(true);
                $value = $reflectionProperty->getValue($content);
            }

            // verify ORM attributes
            $attributes = $reflectionProperty->getAttributes();
            $relationInfo = null;
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === 'Doctrine\ORM\Mapping\ManyToOne') {
                    $relationInfo = $attribute->getArguments();
                    break;
                }
            }

            // relationships management
            if ($relationInfo !== null) {
                // generate the pluralized key with `_id`
                $joinColumnName = $relationInfo['joinColumn']['name'] ?? $this->plurialize($snakeCaseKey) . '_id';

                // add the key with the ID or null
                $fields[$joinColumnName] = ($value && method_exists($value, 'getId')) ? $value->getId() : null;
            } else {
                // simple property, include values 0 and null
                $fields[$snakeCaseKey] = $value;
            }
        }

        // make sure that all expected relationships have a key with `_id`
        foreach ($fields as $key => $value) {
            if (str_ends_with($key, '_id') && !array_key_exists($key, $fields)) {
                $fields[$key] = null;
            }
        }

        return $fields;
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
        $entity = new $this->entityName();
        $setters = $this->getSettersFromFields($fields, $entity);

        // apply setters dynamically
        foreach ($setters as $field => $setter) {

            if (isset($fields[$field])) {
                $value = $fields[$field];

                // if the field is an ID, it is transformed in integer
                if (strpos($field, '_id') !== false) {
                    $value = (int) $value;

                    // Extract the name of the entity withdrawing '_id' and putting the first letter in uppercase
                    $entityName = ucfirst(str_replace('_id', '', $field));

                    // Dynamically convert certain names into known entities, without having to explicitly code them
                    $entityName = $this->convertFieldToEntityName($entityName);

                    // Check if the corresponding class exists (and if it is indeed an entity)
                    if (class_exists("Itsmng\\Domain\\Entities\\$entityName")) {
                        $entityClass = "Itsmng\\Domain\\Entities\\$entityName";
                        // Search entity by its ID
                        $relatedEntity = $this->em->getRepository($entityClass)->find($value);

                        if ($relatedEntity) {
                            $value = $relatedEntity;  // Replace ID with entity object
                        } else {
                            // If the entity is not found, we keep null
                            $value = null;
                        }
                    }
                }
                // Convertir les chaînes de caractères en objets DateTime pour les champs de type DateTimeInterface
                $reflectionMethod = new \ReflectionMethod($entity, $setter);
                $parameters = $reflectionMethod->getParameters();
                if (count($parameters) > 0 && $parameters[0]->getType() && $parameters[0]->getType()->getName() === \DateTimeInterface::class) {
                    try {
                        $value = new \DateTime($value);
                    } catch (\Exception $e) {
                        throw new \Exception("Invalid date format for field $field: " . $e->getMessage());
                    }
                }

                // Call the setter with the processed value
                dump('setter:', $value);
                $entity->$setter($value);
            }
        }

        try {
             
            dump('isManaged before flush:', $this->em->contains($entity));
            $this->em->persist($entity);
            dump('isManaged after persist:', $this->em->contains($entity));
            $this->em->flush();
            // dump("Entity ID after flush: " . $entity->getId());

            return ['id' => $entity->getId()];
        } catch (\Exception $e) {
            dump('Exception message: ', $e->getMessage());
            return false;
        }

        // return false;
    }

    // Fonction générique pour convertir un nom de champ en nom d'entité
    private function convertFieldToEntityName(string $fieldName): string
    {
         $fieldName = strtolower($fieldName);
    
        // Special case for entities
        if (strpos($fieldName, 'entities_id') !== false) {
            return 'Entity';
        }
        
        // Separate the different parts of the name
        $parts = explode('_', $fieldName);

        // Remove technical parts
        $parts = array_filter($parts, function ($part) {
            return !in_array($part, ['tech', 'id']);
        });

        // Take the first remaining part and remove the final 's' if present
        $entityName = reset($parts);
        if (str_ends_with($entityName, 's')) {
            $entityName = substr($entityName, 0, -1);
        }

        // Capitalize the first letter
        return ucfirst($entityName);
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
