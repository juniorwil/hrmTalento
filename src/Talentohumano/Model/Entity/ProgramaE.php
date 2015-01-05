<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Principal\Model\LogFunc; // Traer datos de session activa y datos del pc 

class ProgramaE extends TableGateway
{
    private $id;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_eventos', $adapter, $databaseSchema,$selectResultPrototype);
    }
 
    
    public function getRegistro()
    {
       $datos = $this->select();
       return $datos->toArray();
    }
    
    public function actRegistro($data=array())
    {  
      
       // Datos de transaccion
       $t = new LogFunc($this->adapter);
       $dt = $t->getDatLog();
       // ---       
       $fechaA='';       
       if ($data['estado']==1)
       {
          $fechaA = $dt['fecSis'];
       }
       $fecha = $dt['fecSis'];
       // Fecha de inicio
       $a  =  $data['ano'];	
       $d  =  str_pad( $data['dia'] , 2, "0", STR_PAD_LEFT); 
       $m  =  str_pad( $data['mes'] , 2, "0", STR_PAD_LEFT);
       $fecIni = $a.'-'.$m.'-'.$d;
       if ($data['tipo']==0)
        { // Guarddo inicial cuando se arrastra para programacion
           $fecFin = $fecIni;
           if ($data->id==0)
           {
               $datos=array
               (
                   'nombre'    => $data['nombre'],
                   'comen'     => $data['comen'],
                   'estado'    => $data['estado'], 
                   'idTev'     => $data['tipoE'], 
                   'hora'      => $data['hora'], 
                   'fecDoc'    => $fecha,           
                   'fechaI'    => $fecIni,
                   'fechaF'    => $fecFin,
                );
                $this->insert($datos);
           }else{ // Modificar                
               $datos=array
               (
                   'nombre'    => $data['nombre'],
                   'comen'     => $data['comen'],
                   'estado'    => $data['estado'], 
                   'hora'      => $data['hora'], 
                );
                $this->update($datos, array('id' => $data['id']) );
            }
        }
       else{ // Si tipo es 1 se guardan fechas finales diferentes
            $a  =  $data['anoF'];	
            $d  =  str_pad( $data['diaF'] , 2, "0", STR_PAD_LEFT); 
            $m  =  str_pad( $data['mesF'] , 2, "0", STR_PAD_LEFT);
            $fecFin = $a.'-'.$m.'-'.$d;
            $datos=array
            (
              'fechaI'    => $fecIni,
              'fechaF'    => $fecFin,
            );                       
            $this->update($datos, array('id' => $data['id']) );
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
