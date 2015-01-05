<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IniconHla extends TableGateway
{
    private $id;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq_hl', $adapter, $databaseSchema,$selectResultPrototype);
    }   

    public function actRegistro($idDcheq , $idLhoj, $observa, $estado, $cargo, $funcion, $motivo, $des, $contra, $nombre, $carInf, $telInf)
    {
       $datos=array
       (
           'idDcheq'     => $idDcheq,
           'idLhoj'      => $idLhoj,
           'estado'      => $estado,
           'descripcion' => $observa,   
           'cargo'       => $cargo,   
           'funciones'   => $funcion,   
           'motivo'      => $motivo,              
           'des'         => $des,                         
           'contra'      => $contra,                                   
           'nomInf'      => $nombre,                                              
           'carInf'      => $carInf,
           'telInf'      => $telInf               
        );
       $this->insert($datos);

    }  
    
}
?>
