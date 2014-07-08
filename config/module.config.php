<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            'zf2entity' => __DIR__ . '/../view',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'nGenZf2EntityStatisticsController' => 'nGen\Zf2Entity\Controller\EntityStatisticsController',
        ),
    ),
    'service_manager' => array(
        'aliases' => array(
            'nGenZf2EntityStatisticsService' => 'nGen\Zf2Entity\Service\EntityStatisticsService',
            'nGenZf2EntityStatisticsMapperFacotry' => 'nGen\Zf2Entity\Mapper\Factory\EntityStatisticsMapperFactory',
        ),
    ),
);