<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Lcheqi extends TableGateway
{
    private $id;
    private $nombre;
    private $idEtacon;
    private $idform;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_etapas_con_i', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id       = $datos["id"];
        $this->nombre   = $datos["nombre"];   
        $this->idEtacon = $datos["idEtacon"]; 
        $this->idform   = $datos["tipo1"]; 
    }
    
    public function getRegistro($id)
    {
       $id  = (int) $id;
       $datos = $this->select(array('idEtacon' => $id));
       return $datos->toArray();
     }     
    
    public function actRegistro($data=array())
    {
       self::cargaAtributos($data);
       $datos=array
       (
           'nombre'   => $this->nombre,
           'idEtacon' => $this->id,// id de la etapa de contratacion 
           'idForm'   => $this->idform
       );
       $this->insert($datos);       
    } 

    public function delRegistro($id)
    {
      $this->delete(array('id' => $id));               
    }
}
?>
