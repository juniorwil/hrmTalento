<?php
/*
 * STANDAR DE NISSI MODELO A LA BD MAESTROS
 * 
 */
namespace Talentohumano\Model\Entity;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Admcon extends TableGateway
{
    private $id;
    private $idhoj;
    private $idsol;
    private $idetai;
    private $texto;
    private $resul;
    private $numero;
    
    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null)
    {
        return parent::__construct('t_lista_cheq_d', $adapter, $databaseSchema,$selectResultPrototype);
    }

    private function cargaAtributos($datos=array())
    {
        $this->id      = $datos["id"];    
        $this->idhoj   = $datos["idHoj"];   
        $this->idsol   = $datos["idSol"];   
        $this->idetai  = $datos["iditem"];  
        $this->texto   = $datos["texto"];  
        $this->resul   = $datos["estado"]; // Aprobado / no aprobado  
        $this->numero  = $datos["numero"];  
    }
    

    public function actRegistro($data=array())
    {
       self::cargaAtributos($data);
       $id = $this->id;
       
       // Fecha del sistema
       $date    = new \DateTime(); 
       $fecSis  = $date->format('Y-m-d H:i');       

       $datos=array
       (
           'idHoj'       => $this->idhoj,
           'idCheq'      => $this->idsol,
           'idEtaI'      => $this->idetai,
           'descripcion' => $this->texto,
           'resultado'   => $this->resul,
           'numero'      => $this->numero   
        );
       $this->update($datos, array( 'idCheq' => $this->idsol, 'idHoj' => $this->idhoj, 'idEtaI' => $this->idetai, 'etapa' => '2' ));
    }  
    
}
?>
