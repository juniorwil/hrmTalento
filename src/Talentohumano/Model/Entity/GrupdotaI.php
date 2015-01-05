<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class GrupdotaI extends TableGateway
{
    private $id;
    private $nombre;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_grup_dota_m', $adapter, $databaseSchema,$selectResultPrototype);
    }

    public function getRegistro()
    {
       $datos = $this->select();
       return $datos->toArray();
    }
    
    public function actRegistro($idDot, $idGdot)
    {
       $datos=array
       (
           'idDot'   => $idDot,
           'idGdot'  => $idGdot,
        );
       $this->insert($datos);
    }
    
    public function getRegistroId($id)
    {
       $id  = (int) $id;
       $datos = $this->select(array('idGdot' => $id));
       $row = $datos->toArray();
       return $row;
     }        
     public function delRegistro($id)
     {
       $this->delete(array('id' => $id));               
     }
}
?>
