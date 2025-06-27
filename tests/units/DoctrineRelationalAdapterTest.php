<?php


namespace Tests\Units;
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../../inc/queryexpression.class.php';

use PHPUnit\Framework\TestCase;
use Infrastructure\Adapter\Database\DoctrineRelationalAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager; 

class DoctrineRelationalAdapterTest extends TestCase
{
    private $emMock;
    private $connectionMock;
    private $adapter;
    private $schemaManagerMock;

    protected function setUp(): void
    {
         // Configuration des mocks nécessaires
        $this->emMock = $this->createMock(EntityManager::class);
        $this->connectionMock = $this->createMock(Connection::class);
        $this->schemaManagerMock = $this->createMock(AbstractSchemaManager::class);
        
        // Liaison entre les mocks
        $this->emMock->method('getConnection')->willReturn($this->connectionMock);
        $this->connectionMock->method('createSchemaManager')->willReturn($this->schemaManagerMock);
        
        // Création de l'adaptateur avec réflection car le constructeur a besoin d'une classe
        $reflection = new \ReflectionClass(DoctrineRelationalAdapter::class);
        $this->adapter = $reflection->newInstanceWithoutConstructor();
        
        // Définir les propriétés nécessaires
        $reflectionProperty = $reflection->getProperty('em');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->adapter, $this->emMock);
        
        $reflectionProperty = $reflection->getProperty('isSpecialCase');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->adapter, true);
        
        $reflectionProperty = $reflection->getProperty('fallbackTableName');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->adapter, 'glpi_tests');

    }


    /**
     * getPropertiesAndGetters method test
     */
    public function testGetPropertiesAndGetters()
    {        
        $reflection = new \ReflectionClass(DoctrineRelationalAdapter::class);
        $method = $reflection->getMethod('getPropertiesAndGetters');
        $method->setAccessible(true);

         $testClass = new class {
            private $id;
            protected $name;
            public $email;
        };
        $result = $method->invoke($this->adapter, $testClass);

        $this->assertIsArray($result); 
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('getId', $result['id']);
        
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('getName', $result['name']);
        
        $this->assertArrayHasKey('email', $result);
        $this->assertEquals('getEmail', $result['email']);
    }


    /**
     * toDbFormat method test
     */
    public function testToDbFormat()
    {
        // Access to private method by ReflectionClass
        $reflection = new \ReflectionClass(DoctrineRelationalAdapter::class);
        $method = $reflection->getMethod('toDbFormat');
        $method->setAccessible(true);
        
        // test with a simple field that not changes
        $result = $method->invoke($this->adapter, 'id');
        $this->assertEquals('id', $result);

        // test with internal capital letters and isRelation=true
        $result = $method->invoke($this->adapter, 'softwareItem', true);
        $this->assertEquals('software_items_id', $result);

        // test special case priority + number
        $result = $method->invoke($this->adapter, 'priority1');
        $this->assertEquals('priority_1', $result);

        // test with a field ending in _id (relations)
        $result = $method->invoke($this->adapter, 'location', true);
        $this->assertEquals('locations_id', $result);

        // test with a foreign key to a plural table
        $result = $method->invoke($this->adapter, 'category', true);
        $this->assertEquals('categories_id', $result);

        // test with a complex column name
        $result = $method->invoke($this->adapter, 'dateCreation');
        $this->assertEquals('date_creation', $result);

        // test with a column name that already contains uppercase letters
        $result = $method->invoke($this->adapter, 'User', true);
        $this->assertEquals('users_id', $result);
    }

    /**
     * getLinkedEntity test method
     */
    // public function testGetLinkedEntity()
    // {
    //     // Access a private method via ReflectionClass
    //     $reflection = new \ReflectionClass(DoctrineRelationalAdapter::class);
    //     $method = $reflection->getMethod('getLinkedEntity');
    //     $method->setAccessible(true);

    //     $adapterPartialMock = $this->getMockBuilder(DoctrineRelationalAdapter::class)
    //         ->disableOriginalConstructor() 
    //         ->onlyMethods(['toEntityFormat'])  
    //         ->getMock();

    //      // Configure the toEntityFormat behavior
    //     $adapterPartialMock->method('toEntityFormat')
    //         ->willReturnMap([
    //             ['id', false, 'id'],
    //             ['name', false, 'name'],
    //             ['email', false, 'email'],
    //             ['groups_id', true, 'group'],
    //             ['profiles_id', true, 'profile']
    //         ]);

    //     // Test with a simple case
    //     $result = $method->invokeArgs($adapterPartialMock, 'users_id');
    //     $this->assertEquals('User', $result);

    //     // Test with a complex case
    //     $result = $method->invoke($adapterPartialMock, 'locations_id');
    //     $this->assertEquals('Location', $result);
    // }

     /**
     * fixBitwiseInCriteria method test
     */
    public function testFixBitwiseInCriteria()
    {
        // Access a private method via ReflectionClass
        $reflection = new \ReflectionClass(DoctrineRelationalAdapter::class);
        $method = $reflection->getMethod('fixBitwiseInCriteria');
        $method->setAccessible(true);

        // Test with a simple criterion
        $criteria = ['rights' => ['&', 8192]];
        $result = $method->invoke($this->adapter, $criteria);
        $this->assertInstanceOf(\QueryExpression::class, $result[0]);

        // Test with a complex criterion
        $criteria = [
            'AND' => [
                'rights' => ['&', 8192],
                'id' => 1
            ]
        ];
        $result = $method->invoke($this->adapter, $criteria);
        $this->assertIsArray($result['AND']);
        $this->assertEquals(1, $result['AND']['id']);
        $this->assertInstanceOf(\QueryExpression::class, $result['AND'][0]);
    }


    /**
     * listFields method test for the special case
     */
    public function testListFieldsWithSpecialCase()
    {
        // Configure the mock for schemaManager
        $columnMocks = [];
        $columnNames = ['id', 'name', 'users_id', 'date_creation'];
        
        foreach ($columnNames as $name) {
            $columnMock = $this->createMock(\Doctrine\DBAL\Schema\Column::class);
            $columnMock->method('getName')->willReturn($name);
            $columnMocks[] = $columnMock;
        }

        // The listTableColumns should return a collection of columns
        $this->schemaManagerMock->method('listTableColumns')
            ->with('glpi_tests')
            ->willReturn($columnMocks);

        // The isSpecialCase method is already set to true in setUp()

        // Execute the method to test
        $result = $this->adapter->listFields();

        // Check the result
        $this->assertIsArray($result);
        $this->assertCount(count($columnNames), $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('id', $result['id']);
        $this->assertArrayHasKey('users_id', $result);
        $this->assertEquals('user', $result['users_id']); 
    }

    /**
     * listFields method test for the normal case with entityName
     */
    public function testListFieldsWithEntityName()
    {
        // Use ReflectionClass to modify properties
        $reflection = new \ReflectionClass(DoctrineRelationalAdapter::class);

        // Configure the adapter to use a specific entity
        $reflectionProperty = $reflection->getProperty('isSpecialCase');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->adapter, false);
        
        $reflectionProperty = $reflection->getProperty('entityName');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->adapter, 'Itsmng\Domain\Entities\User');

        // Configure the mock for entity metadata
        $metadataMock = $this->createMock(\Doctrine\ORM\Mapping\ClassMetadata::class);
        $metadataMock->method('getFieldNames')
            ->willReturn(['id', 'name', 'email']);
        $metadataMock->method('getAssociationNames')
            ->willReturn(['group', 'profile']);

        // Configure EntityManager to return metadata
        $this->emMock->method('getClassMetadata')
            ->with('Itsmng\Domain\Entities\User')
            ->willReturn($metadataMock);

        // Create a complete mock that inherits all methods
        $adapterPartialMock = $this->getMockBuilder(DoctrineRelationalAdapter::class)
            ->disableOriginalConstructor() 
            ->onlyMethods(['toDbFormat'])  
            ->getMock();
        // Configure the toDbFormat behavior
        $adapterPartialMock->method('toDbFormat')
            ->willReturnMap([
                ['id', false, 'id'],
                ['name', false, 'name'],
                ['email', false, 'email'],
                ['group', true, 'groups_id'],
                ['profile', true, 'profiles_id']
            ]);
        
        // Use reflection to set the necessary properties on the mock
        $reflectionProperty = $reflection->getProperty('em');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($adapterPartialMock, $this->emMock);
        
        $reflectionProperty = $reflection->getProperty('isSpecialCase');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($adapterPartialMock, false);
        
        $reflectionProperty = $reflection->getProperty('entityName');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($adapterPartialMock, 'Itsmng\Domain\Entities\User');
        
        $result = $adapterPartialMock->listFields();
        
        $this->assertIsArray($result);
        $this->assertCount(5, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('id', $result['id']);
        $this->assertArrayHasKey('groups_id', $result);
        $this->assertEquals('group', $result['groups_id']);
        $this->assertArrayHasKey('profiles_id', $result);
        $this->assertEquals('profile', $result['profiles_id']);
    }



    

}