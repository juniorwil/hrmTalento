<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Solcon; // (C)
use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos
use Principal\Model\Pgenerales; // Parametros generales

class SolconController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
        
    private $lin  = "/talentohumano/solcon/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Solicitud de contratación "; // Titulo listado
    private $tfor = "Documento de solicitud"; // Titulo formulario
    private $ttab = "Documento, Fecha, Fecha Apr., Sede solicitante, Centro de costos, Cargo, Estado, Pdf, Editar,Eliminar"; // Titulo de las columnas de la tabla

    // Listado de registros ********************************************************************************************
    public function listAction()
    {            
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)      
                
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $u->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $u->getSolcon(" a.estado in ('0','1') "), // listado de vacantes            
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin,
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
        );    
        return new ViewModel($valores); 
        
    } // Fin listar registros 
    
 
   // Editar y nuevos datos *********************************************************************************************
   public function listaAction() 
   { 
      $form = new Formulario("form");
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value",$id);                       
      // Sedes
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d = New AlbumTable($this->dbAdapter);      
      // Departamentos
      $datos = $d->getCencos();  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom = $dat['nombre'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idCcos")->setValueOptions($arreglo2);           
      $arreglo2 = '';
      // Cargos
      $datos = $d->getCargos();  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom = $dat['codigo'].' - '.$dat['nombre'].' ( '.$dat['deno'].' )';
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idCar")->setValueOptions($arreglo2);     
      // Tipos de contrato
      $arreglo2='';
      $datos = $d->getTipcont();  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("tipo")->setValueOptions($arreglo2);      
      
      // Motivos de contratacion
      $arreglo2='';
      $datos = $d->getMotivosContra();  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idMot")->setValueOptions($arreglo2);
       
      // Estado
      $daPer = $d->getPermisos($this->lin); // Permisos de esta opcion
      if ($daPer['aprobar']==1)
       {        
          $val=array
          (
            "0"  => 'Revisión',
            "1"  => 'Aprobado'
          );       
       }else{
          $val=array
          (
            "0"  => 'Revisión',
          );                  
       } // Aprueba o no aprueba documentos
      $form->get("estado")->setValueOptions($val);

      // Parametros generales
      $pn = new Pgenerales( $this->dbAdapter );
      $dp = $pn->getGeneral1(1);
      $escala = $dp['escala'];// Escala salarial 0 no, 1 si 

      // ------------------------ Fin valores del formulario 
      $datos  = '';
      $idEsal = 0;
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Zona de validacion del fomrulario  --------------------
            $album = new ValFormulario();
            $form->setInputFilter($album->getInputFilter());            
            $form->setData($request->getPost());       
            if ( $escala == 0 )  // No maneja escala salarial, si no sueldo digitado  
               $form->setValidationGroup('id','idCcos','idMot','tipo','numero'); // ------------------------------------- 2 CAMPOS A VALDIAR DEL FORMULARIO  (C)            
            else // Manejo de escala salrial 
               $form->setValidationGroup('id','idCcos','idMot','tipo','idEsal'); // ------------------------------------- 2 CAMPOS A VALDIAR DEL FORMULARIO  (C)                         
            // Fin validacion de formulario ---------------------------
            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Solcon($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u->actRegistro($data);
                $this->flashMessenger()->addMessage('');               
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);// El 1 es para mostrar mensaje de guardado
            }
        }
        
      }else{              
       if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Solcon($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            // Valores guardados
            $form->get("comen")->setAttribute("value",$datos['comen']); 
            $form->get("comenN")->setAttribute("value",$datos['justificacion']);  
            
            $form->get("estado")->setAttribute("value",$datos['estado']); 
            $form->get("idCar")->setAttribute("value",$datos['idCar']);   
            $form->get("idMot")->setAttribute("value",$datos['idMot']);   
            $form->get("idCcos")->setAttribute("value",$datos['idCcos']);   
            $form->get("tipo")->setAttribute("value",$datos['idTcon']);   
            $form->get("cuotas")->setAttribute("value",$datos['vacantes']);   
            $form->get("numero")->setAttribute("value",$datos['salario']);               
            $idEsal = $datos['idEsal'];
            // Motivos de contratacion
            $arreglo2='';
            $datos = $d->getEsalCargo(' and a.idCar='.$datos['idCar']);
         }     
      }
      $valores=array
      (
           "titulo"  => $this->tfor,           
           "form"    => $form,
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           'datos'   => $datos, 
           'idEsal'  => $idEsal, 
           'escala'  => $escala, // detetermina si maneja escala salarial o no 
           "lin"     => $this->lin,          
      );             
      if ($this->lin!='')// Importante para la seguridad de ingreso al modulo
         return new ViewModel($valores);      
   } // Fin actualizar datos 
   
   // Eliminar dato ********************************************************************************************
   public function listdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Solcon($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }
   //----------------------------------------------------------------------------------------------------------

   // Escalas salariales ********************************************************************************************
   public function listeAction() 
   {
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $d    = new AlbumTable($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
            $data = $this->request->getPost();       
            $valores=array
            (
                "datos"   => $d->getEsalCargo(' and a.idCar='.$data->id),
            );          
            $view = new ViewModel($valores);        
            $this->layout('layout/blancoC'); // Layout del login
            return $view;                          
        }
      }            
   }
   //----------------------------------------------------------------------------------------------------------
   
}
