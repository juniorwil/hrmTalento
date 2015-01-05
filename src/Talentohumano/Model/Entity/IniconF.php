<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IniconF extends TableGateway
{
    private $id;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq_d_a', $adapter, $databaseSchema,$selectResultPrototype);
    }   

    public function actRegistro($idCheq , $idDcheq, $nombre)
    {
       $datos=array
       (
           'idCheq'  => $idCheq,
           'idDcheq' => $idDcheq,
           'nombre'  => $nombre   
        );
       $this->insert($datos);

    }  
    
}
?>
