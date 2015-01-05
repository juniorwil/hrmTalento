<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Aspcari extends TableGateway
{
    private $id;
    private $nombre;
    private $tipo;
    private $defi;
    private $lista;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_asp_cargo_i', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id       = $datos["id"];
        $this->nombre   = $datos["nombre"];   
        $this->tipo     = $datos["tipo"];   
        $this->defi     = $datos["ubicacion"];   
        $this->lista    = $datos["comenN"];   
    }
    
    public function getRegistro($id)
    {
       $id  = (int) $id;
       $datos = $this->select(array('idAsp' => $id));
       return $datos->toArray();
     }     
    
    public function actRegistro($data=array())
    {
       self::cargaAtributos($data);
       $datos=array
       (
           'nombre'    => $this->nombre,
           'defi'      => $this->defi,
           'idAsp'     => $this->id,
           'lista'     => $this->lista,
           'tipo'      => $this->tipo    
       );
       $this->insert($datos);       
    } 
    public function actRegistroO($data=array())
    {
       $datos=array
       (
           'a' => $data->comenNa,
           'b' => $data->comenNb,
           'c' => $data->comenNc,
           'd' => $data->comenNd,
           'e' => $data->comenNe,
        );
        $this->update($datos, array('id' => $data->id));
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
