<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class CargosA extends TableGateway
{
    private $id;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_cargos_a', $adapter, $databaseSchema,$selectResultPrototype);
    }
    public function actRegistro($idCar, $idAsp, $idIasp,$texto, $a, $b, $c, $d, $e)
    {
       $datos=array
       (
           'idIasp' => $idIasp,
           'idAsp'  => $idAsp,
           'idCar'  => $idCar,
           'texto'  => $texto,
           'a' => $a,
           'b' => $b,
           'c' => $c,
           'd' => $d,
           'e' => $e,
        );
        $this->insert($datos);
    }    
    public function getRegistroId($id)
    {
       $id  = (int) $id;
       $rowset = $this->select(array('id' => $id));
       $row = $rowset->current();
      
       if (!$row) {
          throw new \Exception("No hay registros asociados al valor $id");
       }
       return $row;
     }        
 
    public function delRegistro($id)
    {
      $this->delete(array('id' => $id));               
    }
}
?>
