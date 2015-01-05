<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Naspcar extends TableGateway
{
    private $id;
    private $nombre;
     
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_nivelasp', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id     = $datos["id"];    
        $this->nombre = $datos["nombre"];   
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
           'nombre' => $this->nombre,
        );
       if ($id==0) // Nuevo registro
          $this->insert($datos);
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
      try{         
         $this->delete(array('id' => $id));               
       } catch( \Exception $e ) {
          //$mysqli = $this->adapter->getDriver()->getConnection()->getResource();        
          //$file = $e->getFile();
          //$line = $e->getLine();
          //echo $line;
          //echo "$file:$line ERRNO:$mysqli->errno ERROR:$mysqli->error";
      //    $this->rollBack;
          throw new \Exception("Imposible borrar este registro");
       }   
     }    
     
}
?>
