<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

use Principal\Model\LogFunc; // Traer datos de session activa y datos del pc 

class Selper extends TableGateway
{
    private $id;

    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq', $adapter, $databaseSchema,$selectResultPrototype);
    }

    public function actRegistro($idSol, $idHoj)
    {
       // Datos de transaccion
       $t = new LogFunc($this->adapter);
       $dt = $t->getDatLog();       
       
       $this->delete(array('idSol' => $idSol, 'idHoj' => $idHoj ));       
       $datos=array
       (
           'idHoj'   => $idHoj,
           'idSol'   => $idSol,
           'fecDoc'  => $dt['fecSis'],
        );
          $this->insert($datos);
    }  
    public function delRegistro($data=array())
    {
       self::cargaAtributos($data);
       $id = $this->id;         
       $this->delete(array('idSol' => $this->idsol, 'idHoj' => $this->idhoj ));       
    }  
     
}
?>
