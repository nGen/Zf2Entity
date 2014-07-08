<?php
namespace nGen\Zf2Entity\Mapper\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;

class EntityStatisticsMapperFactory {

    public function createDefaultService(ServiceLocatorInterface $serviceLocator, $entity, $mapper, $hydrator = null) {
        $dbAdapter = $serviceLocator -> get('Zend\Db\Adapter\Adapter');
        $mapper -> setDbAdapter($dbAdapter);
        $mapper -> setEntityPrototype($entity);
        
        if(!$hydrator) {
            $hydrator = new \Zend\Stdlib\Hydrator\ClassMethods();
        }

        $mapper -> setHydrator($hydrator);

        $auth = $serviceLocator -> get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            $mapper -> setUserEntityId($auth ->getIdentity() -> getId());
        }

        return $mapper;
    }

}