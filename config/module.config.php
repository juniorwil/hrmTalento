<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Talentohumano\Controller\Index'   => 'Talentohumano\Controller\IndexController',
            'Talentohumano\Controller\Sedes'   => 'Talentohumano\Controller\SedesController',
            'Talentohumano\Controller\Hojasvida'=> 'Talentohumano\Controller\HojasvidaController',            
            'Talentohumano\Controller\Fondos'  => 'Talentohumano\Controller\FondosController',
            'Talentohumano\Controller\Aspcar'  => 'Talentohumano\Controller\AspcarController',
            'Talentohumano\Controller\Naspcar' => 'Talentohumano\Controller\NaspcarController',            
            'Talentohumano\Controller\Tipcon'  => 'Talentohumano\Controller\TipconController',            
            'Talentohumano\Controller\Cargos'  => 'Talentohumano\Controller\CargosController',            
            'Talentohumano\Controller\Lcheq'   => 'Talentohumano\Controller\LcheqController',            
            'Talentohumano\Controller\Depar'   => 'Talentohumano\Controller\DeparController',
            'Talentohumano\Controller\Formch'   => 'Talentohumano\Controller\FormchController', // Formularios lista de chequeo
            'Talentohumano\Controller\Solcon'   => 'Talentohumano\Controller\SolconController', // Solicitud de contratacion
            'Talentohumano\Controller\Selper'   => 'Talentohumano\Controller\SelperController', // Seleccion del personal
            'Talentohumano\Controller\Inicon'   => 'Talentohumano\Controller\IniconController', // Inicio de contratacion
            'Talentohumano\Controller\Admcon'   => 'Talentohumano\Controller\AdmconController', // Admin de contratacion            
            'Talentohumano\Controller\Nivelestudios'  => 'Talentohumano\Controller\NivelestudiosController', // Nivel de estudios
            'Talentohumano\Controller\Matdota'  => 'Talentohumano\Controller\MatdotaController', // Maestro de Dotaciones
            'Talentohumano\Controller\Grupdota'  => 'Talentohumano\Controller\GrupdotaController', // Grupo de Dotaciones            
            'Talentohumano\Controller\Dotaciones' => 'Talentohumano\Controller\DotacionesController', // Entrega de dotaciones            
            'Talentohumano\Controller\Entidades' => 'Talentohumano\Controller\EntidadesController', // Entidaes 
            'Talentohumano\Controller\Areas' => 'Talentohumano\Controller\AreasController', // Areas de capacitaciones
            'Talentohumano\Controller\Solcap' => 'Talentohumano\Controller\SolcapController', // Solicitud de capacitaciones
            'Talentohumano\Controller\Programa' => 'Talentohumano\Controller\ProgramaController', // Programacion de eventos y capacitaciones
            'Talentohumano\Controller\Tipdescar' => 'Talentohumano\Controller\TipdescarController', // Tipos de descargos
            'Talentohumano\Controller\Descargos' => 'Talentohumano\Controller\DescargosController', // LLamado a descargos
            'Talentohumano\Controller\Tipeventos' => 'Talentohumano\Controller\TipeventosController', // Tipos de eventos
            'Talentohumano\Controller\Tipcapa' => 'Talentohumano\Controller\TipcapaController', // Tipos de capacitaciones
            'Talentohumano\Controller\Motivoscontra' => 'Talentohumano\Controller\MotivoscontraController', // Motivos de contratacion            
            'Talentohumano\Controller\Evadescar' => 'Talentohumano\Controller\EvadescarController', // Evaluadores descargos
            'Talentohumano\Controller\Tallas' => 'Talentohumano\Controller\TallasController', // Tallas            
            'Talentohumano\Controller\Lineasdot' => 'Talentohumano\Controller\LineasdotController', // Lineas dotaciones
        ),
    ),
    'router' => array(
        'routes' => array(
            'talentohumano' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/talentohumano',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Talentohumano\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action[/:id]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*'
                             ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Talentohumano' => __DIR__ . '/../view',
        ),
    ),
);
