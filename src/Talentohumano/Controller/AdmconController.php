<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Admcon; // (C)
use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Form\FormChequ;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos

class AdmconController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/Admcon/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Administración de la contratación"; // Titulo listado
    private $tfor = "Administración de contratación"; // Titulo formulario
    private $ttab = "Documento, Aspirante,Cargo del aspirante ,Proceso"; // Titulo de las columnas de la tabla
        
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "datos"     =>  $d->getAspi("a.estado in ('1','2')"),  
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);        
    } // Fin listar registros 

    public function listiAction() // Listar aspirantes a cargos  
    {   
        $form  = new Formulario("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");        
        //----
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter);  
        // Estado
        $daPer = $d->getPermisos($this->lin); // Permisos de esta opcion
        if ($daPer['aprobar']==1)
        {        
          $val=array
          (
            "0"  => 'Revisión',
            "1"  => 'Terminado',
            "2"  => 'Revisión y administración'
          );       
        }else{
          $val=array
          (
            "0"  => 'Revisión',
            "1"  => 'Terminado',
            "2"  => 'Revisión y administración'
          );                  
       } // Aprueba o no aprueba documentos
      $form->get("estado")->setValueOptions($val);
      $form->get("tipo")->setValueOptions(array("1"=>"Contratado","2"=>"No contratado"));
      //
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d = New AlbumTable($this->dbAdapter); 
      $con=' f.tipo=2 and a.id='.$id;
      // Insertar registros que no estan en la lista
      $con2 = 'insert into t_lista_cheq_d (idHoj,  idCheq, idEtaI, etapa ) (
select a.idHoj,a.id, g.id, f.tipo 
      from t_lista_cheq a inner join t_sol_con b
      on a.idSol=b.id inner join t_cargos c on b.idCar=c.id
      inner join t_nivel_cargo d on d.id=c.idNcar
      inner join t_nivel_cargo_o e on e.idNcar=d.id
      inner join t_etapas_con f on e.idEtapa=f.id
      inner join t_etapas_con_i g on g.idEtacon=f.id 
      WHERE not exists (SELECT null from t_lista_cheq_d where a.idHoj=idHoj and a.id=idCheq and g.id=idEtaI and f.tipo=etapa ) and '.$con.'
      )' ;     
        $d->modGeneral($con2);        
        // Estado del documento
        $datos = $d->getGeneral1("Select estado from t_lista_cheq where id=".$id);
        $form->get("estado")->setAttribute("value",$datos['estado']); 

        // Buscar las etapas de la contratacion        
        
        $valores=array
        (
            "titulo"    =>  $this->tfor,
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datos"     =>  $d->getEtcehq($con), // extrae las etapas de contratacion de un determinado cargo
            "ttablas"   =>  'Detalle, Descripción, Estado, Adjunto, Acción',
            "form"      =>  $form,
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);        
    } // Fin listar registros 
 
   public function listaAction() // Guardar postulados
   { 
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);    
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Admcon($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $u->actRegistro($data);
        
        //return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
      }      
     
   }
   // Cambiar estados del proceso
   public function listeAction()
   {        
     $data = $this->request->getPost();
     $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
     $d = New AlbumTable($this->dbAdapter); 
     $con = 'update t_lista_cheq set estado=1 where id='.$data['id'];     
     $d->modGeneral($con); // listado de vacantes            
     return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
    } // Fin listar registros    
   //----------------------------------------------------------------------------------------------------------
        
}
