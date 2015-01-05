<?php
/** STANDAR MAESTROS NISSI  */
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Talentohumano\Model\Entity\Sedes;
use Principal\Form\Formulario;     // Componentes generales de todos los formularios
use Principal\Model\ValFormulario; // Validaciones de entradas de datos

class SedesController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin = "/talentohumano/sedes/list"; // Variable lin de acceso
    
    // Listado de registros 
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Sedes($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  "Sedes",
            "datos"     =>  $u->getRegistro(),            
            "ttablas"   =>  "Sedes,Direccion,A,E",
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
    } // Fin listar registros 
    
   //----------------------------------------------------------------------------------------------------------
    
   // Editar y nuevos datos 
   public function listaAction() 
   { 
      if($this->getRequest()->isPost()) // Actulizar datos
      {

        $form = new Formulario();

        $request = $this->getRequest();
        if ($request->isPost()) {

            $album = new ValFormulario();
            $form->setInputFilter($album->getInputFilter());            
            $form->setData($request->getPost());           
            $form->setValidationGroup('nombre', 'dir');

            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Sedes($this->dbAdapter);   
                $data = $this->request->getPost();
                $u->actRegistro($data);
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }
        $valores=array
        (
          "titulo"  => "Registro de sedes",
          "form"    => $form,
          'url'     => $this->getRequest()->getBaseUrl(),
          "lin"     => $this->lin
        );        
        return new ViewModel($valores);
        
    }else{
              
      //zona del formulario de actualizaciones
      $form=new Formulario();            
      $datos = 0;            
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Sedes($this->dbAdapter);            
            $datos = $u->getRegistroId($id);
            $n = $datos['nombre'];
            $d = $datos['dir'];
            // Colocar los valores guardados
            $form->get("nombre")->setAttribute("value","$n"); 
            $form->get("dir")->setAttribute("value","$d"); 
         }            
         
         $form->get("id")->setAttribute("value",$id);                       
         $valores=array
         (
            "titulo"  => "Registro de sedes",
            "form"    => $form,
            'url'     => $this->getRequest()->getBaseUrl(),
            'id'      => $id,
            'datos'   => $datos,  
            "lin"     => $this->lin
         );
          return new ViewModel($valores);
      }
   } // Fin actualizar datos 
   
   //----------------------------------------------------------------------------------------------------------
   // Eliminar dato 
   public function listdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Sedes($this->dbAdapter);            
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }
   //----------------------------------------------------------------------------------------------------------
        
}
