<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IniconA extends TableGateway
{
    private $id;
    private $texto;
    private $estado;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id      = $datos["id"];    
        $this->texto   = "";  
        $this->estado  = $datos["tipo"]; // Contratado / No contratado
    }
    

    public function actRegistro($data=array())
    {
       self::cargaAtributos($data);
       $id = $this->id;       
       // Fecha del sistema
       $date    = new \DateTime(); 
       $fecSis  = $date->format('Y-m-d H:i');   
       $datos=array
       (
           'contratado'  => $this->estado   
        );
       $this->update($datos, array( 'id' => $this->id ));

    }  
    
}
?>
