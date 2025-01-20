<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;
use Doctrine\ORM\EntityManager;
use Itsmng\Infrastructure\Persistence\EntityManagerProvider;
use ReflectionClass;

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

    public function findOneBy(array $criteria): mixed
    {
        // $result = $this->em->find($this->entityName, $criteria);
        // return $result;
        $result = $this->em->getRepository($this->entityName)->findOneBy($criteria);
    return $result;
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

    // get values from entity as array
    public function getFields($content): array
    {
        $propertiesAndGetters = $this->getPropertiesAndGetters($content);
        $fields = [];
        foreach ($propertiesAndGetters as $property => $getter) {
            if (method_exists($content, $getter)) {
                $value = $content->$getter();
            } else {
                $value = $property;
            }
            if (is_object($value) && method_exists($value, 'getId')) {
                $fields[$this->plurialize($property) . '_id'] = $value->getId();
            } elseif ($value === null) {
                $fields[$property] = null;
                $fields[$this->plurialize($property) . '_id'] = null;
            } else {
                $fields[$property] = $value;
            }
        }
        return $fields;
    }

    public function save(array $fields): bool
    {
        // TODO: Implement save() method.
        return false;
    }
    public function add(array $fields): bool|array
    {
        // TODO: Implement add() method.
        return false;
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
}
