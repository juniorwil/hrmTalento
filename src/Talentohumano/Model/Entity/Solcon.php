<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Principal\Model\LogFunc; // Traer datos de session activa y datos del pc 
use Principal\Model\Pgenerales; // Parametros generales


class Solcon extends TableGateway
{
    private $id;
    private $idsed;
    private $idcar;
    private $comen;
    private $vaca;
    private $estado;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_sol_con', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id      = $datos["id"];    
        $this->idsed   = $datos["idSed"];   
        $this->idcar   = $datos["idCar"]; 
        $this->vaca    = $datos["cuotas"]; 
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
       if ($this->estado==1)
       {
          $fecha = $dt['fecSis'];
       }
       $sueldo = 0 ; 
       // Parametros generales
       $pn = new Pgenerales( $this->adapter );
       $dp = $pn->getGeneral1(1);
       if (  $dp['escala'] == 0 ) // Escala salarial 0 no, 1 si 
       {
          $sueldo = $data['numero'] ;        
          $idEsal = 1; // POR INTEGRIDAD REFERENCIAL CON LA TABLA DE ESACALAS SALARIALES, DEBE CREARSE EL ID 1 PARA NO APLICA ESCALA SALARIAL CUANDO NO MANEJE ESCALA SALARIAL
       }else
          $idEsal = $data['idEsal'];

       $datos=array
       (
           'idCar'     => $this->idcar,
           'comen'     => $this->comen,   
           'vacantes'  => $this->vaca,  
           'estado'    => $this->estado,
           'idMot'     => $data['idMot'],
           'idTcon'    => $data['tipo'],
           'idCcos'    => $data['idCcos'],
           'idEsal'    => $idEsal,
           'justificacion' => $data['comenN'],
           'salario'   => $sueldo,
           'fecApr'    => $fecha,
           'fecDoc'    => $dt['fecSis'],
           'idUsu'     => $dt['idUsu'],
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
       $this->delete(array('id' => $id));               
     }    
     
}
?>
