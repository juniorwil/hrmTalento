<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IniconHl extends TableGateway
{
    private $id;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq_hl', $adapter, $databaseSchema,$selectResultPrototype);
    }   

    public function actRegistro($idDcheq , $idLhoj, $nombre, $estado)
    {
       $datos=array
       (
           'idDcheq'     => $idDcheq,
           'idLhoj'      => $idLhoj,
           'estado'      => $estado,
           'descripcion' => $nombre   
        );
       $this->insert($datos);

    }  
    
}
?>
