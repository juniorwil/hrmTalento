<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Hojasvida extends TableGateway
{
    private $id;
    private $cedula;
    private $nombre;
    private $apellido; 
    private $dir; 
    private $numero; // Telefono
    private $SexEmp; 
    private $FecNac; // Fecha de nacimiento
    private $email;  
            
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_hoja_vida', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id        = $datos["id"];    
        $this->cedula    = $datos["cedula"];   
        $this->nombre    = $datos["nombre"];     
        $this->apellido  = $datos["apellido1"];    
        $this->dir       = $datos["dir"];    
        $this->numero    = $datos["numero"];    // Telefono
        $this->SexEmp    = $datos["sexo"];      
        $this->FecNac    = $datos["fecDoc"];    // Fecha de nacimiento
        $this->email     = $datos["email"];        
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
           "cedula"   => $this->cedula,   
           "nombre"   => $this->nombre,   
           "apellido" => $this->apellido,    
           "DirEmp"  => $this->dir,    
           "TelEmp"  => $this->numero,    // Telefono
           "SexEmp"  => $this->SexEmp,      
           "FecNac"  => $this->FecNac,      
           "email"   => $this->email,      
           "estCivil" => $data['estCivil'],
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
