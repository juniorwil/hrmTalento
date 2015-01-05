<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Formchi extends TableGateway
{
    private $id;
    private $nombre;
    private $tipo;
    private $ubi;
    private $lista;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_form_i', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id       = $datos["id"];
        $this->nombre   = $datos["nombre"];   
        $this->tipo     = $datos["tipo"];   
        $this->ubi      = $datos["ubicacion"];   
        $this->lista    = $datos["comenN"];   
    }
    
    public function getRegistro($id)
    {
       $id  = (int) $id;
       $datos = $this->select(array('idForm' => $id));
       return $datos->toArray();
     }     
    
    public function actRegistro($data=array())
    {
       self::cargaAtributos($data);
       $datos=array
       (
           'nombre'    => $this->nombre,
           'ubi'       => $this->ubi,
           'idForm'    => $this->id,
           'lista'     => $this->lista,    
           'tipo'      => $this->tipo    
       );
       $this->insert($datos);       
    } 

    public function delRegistro($id)
    {
      $this->delete(array('id' => $id));               
    }
}
?>
