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
        $result = $this->em->find($this->entityName, $criteria);
        return $result;
        // $result = $this->em->getRepository($this->entityName)->findOneBy($criteria);
        // return $result;
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
        dump("listFields called");
        // TODO: Implement listFields() method.
        dump($this->entityName); 
        $metadata = $this->em->getClassMetadata($this->entityName);
        dump($metadata->getFieldNames()); 
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

   

    private function toSnakeCase(string $input): string
    {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
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

            // Récupérer la valeur
            if ($getter && method_exists($content, $getter)) {
                $value = $content->$getter();
            } else {
                // Essayer d'accéder directement à la propriété
                $reflectionProperty->setAccessible(true);
                $value = $reflectionProperty->getValue($content);
            }

            // Vérifier les attributs ORM
            $attributes = $reflectionProperty->getAttributes();
            $relationInfo = null;
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === 'Doctrine\ORM\Mapping\ManyToOne') {
                    $relationInfo = $attribute->getArguments();
                    break;
                }
            }

            // Gestion des relations
            if ($relationInfo !== null) {
                // Générer la clé pluralisée avec `_id`
                $joinColumnName = $relationInfo['joinColumn']['name'] ?? $this->plurialize($snakeCaseKey) . '_id';

                // Ajouter la clé avec l'ID ou null
                $fields[$joinColumnName] = ($value && method_exists($value, 'getId')) ? $value->getId() : null;
            } else {
                // Propriété simple, y compris les valeurs 0 et null
                $fields[$snakeCaseKey] = $value;
            }
        }

        // S'assurer que toutes les relations attendues ont une clé avec `_id`
        foreach ($fields as $key => $value) {
            if (str_ends_with($key, '_id') && !array_key_exists($key, $fields)) {
                $fields[$key] = null;
            }
        }

        return $fields;
    }

    /**
     * Tente de détecter si une propriété correspond à une clé étrangère.
     */
    private function detectRelationKey($entity, string $property): bool
    {
        // Si Doctrine est utilisé, obtenir les métadonnées
        if (method_exists($entity, 'getClassMetadata')) {
            $metadata = $entity->getClassMetadata();
            if (isset($metadata->associationMappings[$property])) {
                return true; // Propriété correspond à une relation
            }
        }

        // Alternative si les métadonnées Doctrine ne sont pas disponibles
        if (property_exists($entity, $property)) {
            $reflectionProperty = new \ReflectionProperty($entity, $property);
            $docComment = $reflectionProperty->getDocComment();
            if ($docComment && strpos($docComment, '@ORM\ManyToOne') !== false) {
                return true; // Relation détectée via annotation
            }
        }

        return false; // Pas une relation
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
        dump($fields);        
        $setters = $this->getSettersFromFields($fields, $entity);
        dump($setters);        
        foreach ($setters as $field => $setter) {            
            if (isset($fields[$field])) {
                $entity->$setter($fields[$field]);
                dump($entity);
            }
        }

        try {            
            $this->em->persist($entity);
            $this->em->flush(); 
            dump("Entity ID after flush: " . $entity->getId());
            
            return ['id' => $entity->getId()];
        } catch (\Exception $e) {
            // Gérer les erreurs d'insertion (par exemple violation de contraintes, etc.)
            dump('message', $e);
            return false;
        }
    // return false;
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
