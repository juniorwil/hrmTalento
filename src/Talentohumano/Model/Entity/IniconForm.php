<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IniconForm extends TableGateway
{
    private $id;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq_f', $adapter, $databaseSchema,$selectResultPrototype);
    }   

    public function actRegistro($idDcheq , $idIform, $lista, $texto , $a)
    {
       $datos=array
       (
           'idDcheq'     => $idDcheq,
           'idIform'     => $idIform,
           'texto'       => $texto,
           'lista'       => $lista,
           'casilla'     => $a,
        );
       $this->insert($datos);

    }  
    
}
?>
