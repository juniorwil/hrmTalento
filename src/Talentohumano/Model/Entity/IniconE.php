<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IniconE extends TableGateway
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
    private $fecIng;  // Fecha de registro
    private $idHoj;
            
    // Fondos
    private $idsal; 
    private $idpen; 
    private $idces;              
    private $idarp; 
    private $idcaja; 
    private $idfav;              
    private $idfafc;              
    // Contractuales
    private $idcar; 
    private $idcencos; 
    private $idgrupo; 
    private $idtau;                              
    private $idtau2;                              
    private $idtau3;                              
    private $idprej;                              
    private $tipo;       
    private $idtemp;
    private $sueldo;
    private $foto;
        
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('a_empleados', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id        = $datos["id"];    
        $this->fecIng    = $datos["fecDoc"];
        
        // Fondos
        $this->idsal     = $datos["idSal"];    
        $this->idpen     = $datos["idPen"];    
        $this->idces     = $datos["idCes"];                 
        $this->idarp     = $datos["idArp"];    
        $this->idcaja    = $datos["idCaja"];    
        $this->idfav     = $datos["idFav"];                 
        $this->idfafc    = $datos["idFafc"];                 
        // Contractuales
        $this->idgrupo   = $datos["idGrupo"];    
        $this->idtau     = $datos["idTau"];                                 
        $this->idtau2    = $datos["idTau2"];                                 
        $this->idtau3    = $datos["idTau3"];                                 
        $this->idprej    = $datos["idPrej"];                                 
        $this->tipo      = $datos["tipo2"]; 
        $this->idtemp    = $datos["idTemp"];
        $this->sueldo    = 0;
    }
    
    public function getRegistro()
    {
       $datos = $this->select();
       return $datos->toArray();
    }
    
    public function actRegistro($data=array(), $datos2=array(), $idCcos)
    {
       self::cargaAtributos($data);
       $id = $this->id;
       //print_r($datos);
        $this->cedula    = $datos2["cedula"];   
        $this->nombre    = $datos2["nombre"];     
        $this->apellido  = $datos2["apellido"];    
        $this->dir       = $datos2["DirEmp"];    
        $this->numero    = $datos2["TelEmp"];    // Telefono
        $this->SexEmp    = $datos2["SexEmp"];      
        $this->FecNac    = $datos2["FecNac"];    // Fecha de nacimiento
        $this->email     = $datos2["email"];        
        $this->idHoj     = $datos2["idHoj"];      
        $this->idcar     = $datos2["idCar"];    
       
       $datos=array
       (
           "idHoj"   => $this->idHoj,
           "CedEmp"   => $this->cedula,   
           "nombre"   => $this->nombre,   
           "apellido" => $this->apellido,    
           "DirEmp"  => $this->dir,    
           "TelEmp"  => $this->numero,    // Telefono
           "idFsal"  => $this->idsal,    
           "idFpen"  => $this->idpen,    
           "idFces"  => $this->idces,                 
           "idFarp"  => $this->idarp,    
           "idcaja"  => $this->idcaja,    
           "idFav"   => $this->idfav,                 
           "idFafc"  => $this->idfafc,                 
           "idCar"   => $this->idcar,    
           "idCcos"  => $idCcos,    
           "idGrup"  => $this->idgrupo,    
           "idTau"   => $this->idtau,         
           "idTau2"  => $this->idtau2,         
           "idTau3"  => $this->idtau3,         
           "IdTcon"  => $this->tipo,// Tipo de contrato                                                  
           "IdTemp"  => $this->idtemp,
           "sueldo"  => $this->sueldo,
           "SexEmp"  => $this->SexEmp,      
           "FecNac"  => $this->FecNac,      
           "fecIng"  => $this->fecIng,      
           "email"   => $this->email,      
        );
       //if ($id==0) // Nuevo registro
          $this->insert($datos);
       //else // Mdificar registro
        //  $this->update($datos, array('id' => $id));
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
    
}
?>
