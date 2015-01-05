<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Tipcon; // (C)
use Talentohumano\Model\Entity\Tipcono; // (C)
use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos

class TipconController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/tipcon/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Tipos de contratación"; // Titulo listado
    private $tfor = "Actualización tipo de contratación"; // Titulo formulario
    private $ttab = "Tipo de contrato,Orden lista de chequeo,Editar,Eliminar"; // Titulo de las columnas de la tabla
        
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Tipcon($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "datos"     =>  $u->getRegistro(),            
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin
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
     
      $datos=0;
      $valores=array
      (
           "titulo"  => $this->tfor,
           "form"    => $form,
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           'datos'   => $datos,  
           "lin"     => $this->lin
      );       
      // ------------------------ Fin valores del formulario 
      
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Zona de validacion del fomrulario  --------------------
            $album = new ValFormulario();
            $form->setInputFilter($album->getInputFilter());            
            $form->setData($request->getPost());           
            $form->setValidationGroup('nombre'); // ------------------------------------- 2 CAMPOS A VALDIAR DEL FORMULARIO  (C)            
            // Fin validacion de formulario ---------------------------
            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Tipcon($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u->actRegistro($data);
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }
        return new ViewModel($valores);
        
    }else{              
      if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Tipcon($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            $a = $datos['nombre'];
            // Valores guardados
            $form->get("nombre")->setAttribute("value","$a"); 

         }            
         return new ViewModel($valores);
      }
   } // Fin actualizar datos 
   
   // Eliminar dato ********************************************************************************************
   public function listdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Tipcon($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }          
   }

   //----------------------------------------------------------------------------------------------------------
   // FUNCIONES ADICIONALES GUARDADO DE LISTA DE CHQUEOS ASOCIADAS A ESTE TIPO DE CONTRATACION
     
   // Listado de items de la etapa **************************************************************************************
   public function listoAction()
   {
      $form = new Formulario("form");
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value","$id"); 
      
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $u=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)    

      $dat = $u->getGeneral1("Select * from t_nivel_cargo where id = ".$id); 

      $valores=array
      (
           "titulo"    =>  'Lista de chequeo asociada a '.$dat['nombre'],
           'url'       =>  $this->getRequest()->getBaseUrl(),
           "datos"     =>  $u->getLcheq($id), 
           "datos2"    =>  $u->getLcheqTcon($id),
           "form"      =>  $form,
           "lin"       =>  $this->lin
       );           
      return new ViewModel($valores);                           
        
   } // Fin listar registros orden  
   public function listoaAction() 
   { 
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);    
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Tipcono($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $u->actRegistro($data);                            
      }
        $view = new ViewModel();        
        $this->layout('layout/blancoC'); // Layout del login
        return $view;            
      //return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);

   } // Fin actualizar datos 
   // Borrar lista de chequeo para modificacion
   public function listodAction() 
   { 
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);    
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Tipcono($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $u->delRegistro($data->id);                            
      }
      $view = new ViewModel();        
      $this->layout('layout/blancoC'); // Layout del login
      return $view;                  

   } // Fin actualizar datos    
   
   
   
}
