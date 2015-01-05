<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Inicon extends TableGateway
{
    private $id;
    private $texto;
    private $estado;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq', $adapter, $databaseSchema,$selectResultPrototype);
    }    

    public function actRegistro($id, $texto,$estado)
    {     
       // Fecha del sistema
       $date    = new \DateTime(); 
       $fecSis  = $date->format('Y-m-d H:i');   
       $datos=array
       (
           'descripcion' => $texto,
           'estado'      => $estado   
        );
       $this->update($datos, array( 'id' => $id ));

    }  
    
}
?>
