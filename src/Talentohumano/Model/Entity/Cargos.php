<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Cargos extends TableGateway
{
    private $id;
    private $nombre;
    private $deno;
    private $idcar_a;
    private $plazas;
    private $respo;
    private $mision;
    private $idncar;
    private $iddep;
    private $idgdot;
    private $idnasp;
    private $idSed;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_cargos', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id      = $datos["id"];    
        $this->nombre  = $datos["nombre"];   
        $this->deno    = $datos["deno"];   
        $this->idcar_a = $datos["idCar"];   
        $this->plazas  = $datos["numero"];   
        $this->respo   = $datos["respo"]; 
        $this->mision  = $datos["mision"]; 
        $this->idncar  = $datos["idNcar"]; 
        $this->iddep   = $datos["idDep"]; 
        $this->idgdot  = $datos["idGdot"]; 
        $this->idnasp  = $datos["idNasp"]; 
        $this->idSed   = $datos["idSed"]; 
    }
    
    public function getRegistro()
    {
       $datos = $this->select();
       return $datos->toArray();
    }
    
    public function actRegistro($data=array())
    {
       self::cargaAtributos($data);
       $id = $this->id;
       $datos=array
       (
           'nombre'   => $this->nombre,
           'deno'     => $this->deno,
           'idCar_a'  => $this->idcar_a,
           'plazas'   => $this->plazas,
           'respo'    => $this->respo,
           'mision'   => $this->mision,
           'idNcar'   => $this->idncar,
           'idCcos'    => $this->iddep,
           'idGdot'   => $this->idgdot,
           'idSed'    => $this->idSed,
           'idNasp'   => $this->idnasp
        );
       if ($id==0) // Nuevo registro
       {
          $this->insert($datos);
          $inserted_id = $this->lastInsertValue;  
          return $inserted_id;                    
       }
       else // Mdificar registro
          $this->update($datos, array('id' => $id));
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
