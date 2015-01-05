<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IniconVc extends TableGateway
{
    private $id;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq_vc', $adapter, $databaseSchema,$selectResultPrototype);
    }   

    public function actRegistro($idDcheq , $idAspI, $lista, $texto , $a, $b, $c, $d, $e, $estado)
    {
       $datos=array
       (
           'idDcheq'     => $idDcheq,
           'idAspI'      => $idAspI,
           'texto'       => $texto,
           'lista'       => $lista,
           'a'       => $a,
           'b'       => $b,
           'c'       => $c,
           'd'       => $d,           
           'e'       => $e,  
           'estado'  => $estado,  
        );
       $this->insert($datos);

    }  
    
}
?>
