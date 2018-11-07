<?php

namespace Gesof\MakerBundle\Doctrine;

use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException as ORMMappingException;
use Doctrine\Common\Persistence\Mapping\MappingException as PersistenceMappingException;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;

/**
 * 
 */
class DoctrineHelper
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    public function getRegistry(): ManagerRegistry
    {
        // this should never happen: we will have checked for the
        // DoctrineBundle dependency before calling this
        if (null === $this->registry) {
            throw new \Exception('Somehow the doctrine service is missing. Is DoctrineBundle installed?');
        }

        return $this->registry;
    }

    private function isDoctrineInstalled(): bool
    {
        return null !== $this->registry;
    }

    /**
     * @param string $className
     *
     * @return MappingDriver|null
     *
     * @throws \Exception
     */
    public function getMappingDriverForClass(string $className)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getRegistry()->getManagerForClass($className);

        if (null === $em) {
            throw new \InvalidArgumentException(sprintf('Cannot find the entity manager for class "%s"', $className));
        }

        $metadataDriver = $em->getConfiguration()->getMetadataDriverImpl();

        if (!$metadataDriver instanceof MappingDriverChain) {
            return $metadataDriver;
        }

        foreach ($metadataDriver->getDrivers() as $namespace => $driver) {
            if (0 === strpos($className, $namespace)) {
                return $driver;
            }
        }

        return $metadataDriver->getDefaultDriver();
    }

    public function getEntitiesForAutocomplete(): array
    {
        $entities = [];

        if ($this->isDoctrineInstalled()) {
            $allMetadata = $this->getMetadata();

            /* @var ClassMetadata $metadata */
            foreach (array_keys($allMetadata) as $classname) {
                $entities[] = $classname;
            }
        }

        return $entities;
    }

    /**
     * @param string|null $classOrNamespace
     * @param bool        $disconnected
     *
     * @return array|ClassMetadata
     */
    public function getMetadata(string $classOrNamespace = null, bool $disconnected = false)
    {
        $metadata = [];

        /** @var EntityManagerInterface $em */
        foreach ($this->getRegistry()->getManagers() as $em) {
            $cmf = $em->getMetadataFactory();

            if ($disconnected) {
                try {
                    $loaded = $cmf->getAllMetadata();
                } catch (ORMMappingException $e) {
                    $loaded = $cmf instanceof AbstractClassMetadataFactory ? $cmf->getLoadedMetadata() : [];
                } catch (PersistenceMappingException $e) {
                    $loaded = $cmf instanceof AbstractClassMetadataFactory ? $cmf->getLoadedMetadata() : [];
                }

                $cmf = new DisconnectedClassMetadataFactory();
                $cmf->setEntityManager($em);

                foreach ($loaded as $m) {
                    $cmf->setMetadataFor($m->getName(), $m);
                }
            }

            foreach ($cmf->getAllMetadata() as $m) {
                if (null === $classOrNamespace) {
                    $metadata[$m->getName()] = $m;
                } else {
                    if ($m->getName() === $classOrNamespace) {
                        return $m;
                    }

                    if (0 === strpos($m->getName(), $classOrNamespace)) {
                        $metadata[$m->getName()] = $m;
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * @param string $entityClassName
     *
     * @return EntityDetails|null
     */
    public function createDoctrineDetails(string $entityClassName)
    {
        $metadata = $this->getMetadata($entityClassName);

        if ($metadata instanceof ClassMetadata) {
            return new EntityDetails($metadata);
        }

        return null;
    }

    public function isClassAMappedEntity(string $className): bool
    {
        if (!$this->isDoctrineInstalled()) {
            return false;
        }

        return (bool) $this->getMetadata($className);
    }
}
