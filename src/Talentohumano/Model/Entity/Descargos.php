<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Principal\Model\LogFunc; // Traer datos de session activa y datos del pc 

class Descargos extends TableGateway
{
    private $id;
    private $idsed;
    private $idTdes;
    private $comen;
    private $estado;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_descargos', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id      = $datos["id"];    
        $this->idsed   = $datos["idSed"];   
        $this->idTdes   = $datos["idCar"]; 
        $this->comen   = $datos["comen"];  
        $this->estado  = $datos["estado"]; 
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
       // Datos de transaccion
       $t = new LogFunc($this->adapter);
       $dt = $t->getDatLog();
       // ---       
       $fecha='';
       if ($this->estado==0)
       {
          $fecha = $dt['fecSis'];
       }
       $fechaA='';
       if ($this->estado==1)
       {
          $fechaA = $dt['fecSis'];
       }       
       $datos=array
       (
           'idTdes'    => $this->idTdes,
           'idEmp'     => $data['idEmp'],   
           'comen'     => $this->comen,   
           'estado'    => $this->estado,   
           'suceso'    => $data['comenN'],   
           'fecCita'   => $data['fecDoc'],   
           'horaCita'  => $data['hora'],   
           'fecSuc'    => $data['fechaIni'],
           'horSuc'    => $data['hora2'],
           'fecDoc'    => $fecha,
           'fecApr'    => $fechaA,
        );                
       if ($id==0) // Nuevo registro
       {
          $this->insert($datos);
          $inserted_id = $this->lastInsertValue;  
          return $inserted_id;                    
       }       
       else // Mdificar registro
       {
          $this->update($datos, array('id' => $id));
          return 0;
       }          
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
