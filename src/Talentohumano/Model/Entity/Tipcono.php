<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Tipcono extends TableGateway
{
    private $id;
    private $idncar;
    private $idetapa;
    private $orden;
    
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_nivel_cargo_o', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->idncar   = $datos["idncar"];    
        $this->idetapa  = $datos["idetapa"];   
        $this->orden    = $datos["orden"];   
    }
              
    public function actRegistro($data=array())
    {
       self::cargaAtributos($data);       

       $datos=array
       (
           'idNcar'   => $this->idncar,
           'idEtapa'  => $this->idetapa,
           'orden'    => $this->orden,
        );
       $this->insert($datos);
    }  

    public function delRegistro($id)
    {
       $this->delete(array('idNcar' => $id));
    }  

     
}
?>
