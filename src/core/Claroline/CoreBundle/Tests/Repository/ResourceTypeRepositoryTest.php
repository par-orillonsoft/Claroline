<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Entity\Extension;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class ResourceTypeRepositoryTest extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    /** @var ResourceTypeRepository */
    private $repo;
    
    protected function setUp()
    {
        parent::setUp();
        //$this->markTestSkipped('columnType cannot be null');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->repo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
    }
    
    public function testFindPluginResourceTypes()
    {
        $typeCount = count($this->repo->findPluginResourceTypes());
        
        $this->createResourceTypes();
        
        $newTypeCount = count($types = $this->repo->findPluginResourceTypes());
        
        // Some plugin types may be already registered, so we only test
        // that the repository can retrieve the ones we have added
        $this->assertEquals($newTypeCount, $typeCount + 2);
        $lastType = array_pop($types);
        $this->assertEquals('Type y', $lastType->getType());
        $lastType = array_pop($types);
        $this->assertEquals('Type x', $lastType->getType());
    }
    
    public function testFindPluginResourceNameFqcns()
    {
        $typeCount = count($this->repo->findPluginResourceNameFqcns());
        
        $this->createResourceTypes();
        
        $newTypeCount = count($types = $this->repo->findPluginResourceNameFqcns());
        
        // see previous test
        $this->assertEquals($newTypeCount, $typeCount + 2);
        $lastType = array_pop($types);
        $this->assertEquals('YYY/YYY/YYY', $lastType['class']);
        $lastType = array_pop($types);
        $this->assertEquals('XXX/XXX/XXX', $lastType['class']);
    }
    
    private function createResourceTypes()
    {
        $plugin = new Extension();
        $plugin->setVendorName('Test');
        $plugin->setBundleName('Test');
        $plugin->setType('test');
        $plugin->setBundleFQCN('Test\Test');
        $plugin->setNameTranslationKey('test');
        $plugin->setDescriptionTranslationKey('test');
        
        $firstType = new ResourceType();
        $firstType->setType('Type x');
        $firstType->setClass('XXX/XXX/XXX');
        $firstType->setListable(true);
        $firstType->setNavigable(false);
        $firstType->setPlugin($plugin);
        
        $secondType = new ResourceType();
        $secondType->setType('Type y');
        $secondType->setClass('YYY/YYY/YYY');
        $secondType->setListable(true);
        $secondType->setNavigable(false);
        $secondType->setPlugin($plugin);
        
        $thirdType = new ResourceType();
        $thirdType->setType('Type z');
        $thirdType->setListable(true);
        $thirdType->setNavigable(false);
        
        $this->em->persist($plugin);
        $this->em->persist($firstType);
        $this->em->persist($secondType);
        $this->em->persist($thirdType);
        $this->em->flush();
    }
}