<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\BatchInsert;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Loads a large amount of workspace.
 * This fixture assume the user JohnDoe (admin) already exists.
 */
class LoadWorkspacesData extends LoggableFixture implements ContainerAwareInterface
{
    private $container;
    private $numberWorkspaces;
    const BATCH_SIZE = 5;

    public function __construct($numberWorkspaces)
    {
        $this->numberWorkspaces = $numberWorkspaces;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

        /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $workspaceCreator = $this->container->get('claroline.workspace.creator');
        $count = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->count();
        $totalWorkspaces = $count + 1;
        $admin = $this->findJohnDoe($manager);

        for ($j = 0, $i = 0; $i < $this->numberWorkspaces; $i++, $totalWorkspaces++) {
            $manfatoryFieldValue = "ws_batch" . $totalWorkspaces;
            $config = new Configuration();
            $config->setWorkspaceName($manfatoryFieldValue);
            $config->setWorkspaceCode($manfatoryFieldValue);
            $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
            $workspaceCreator->createWorkspace($config, $admin);
            $this->log("UOW[{$manager->getUnitOfWork()->size()}]");

            if (($i % self::BATCH_SIZE) === 0) {
                $j++;
                $manager->flush();
                $manager->clear();
                $admin = $this->findJohnDoe($manager);
                $totalInserts = $i + 1;
                $this->log("batch [{$j}] | workspaces [{$totalInserts}] | UOW  [{$manager->getUnitOfWork()->size()}]");
            }
        }
    }

    private function findJohnDoe(ObjectManager $manager)
    {
        $query = $manager->createQuery("SELECT u FROM Claroline\CoreBundle\Entity\User u where u.username = 'JohnDoe'");
        $query->setFetchMode("MyProject\User", "address", "EXTRA_LAZY");

        return $query->getSingleResult();
    }

}

