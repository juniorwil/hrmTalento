<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambian nombre de funciones al crear nuevo controlador para otro modulo
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Depar;
use Principal\Form\Formulario;    // Componentes generales de todos los formularios
use Principal\Model\ValFormulario; // Validaciones de entradas de datos

class DeparController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/depar/list";   // Variable lin de acceso
    private $tlis = "Departamentos";               // Titulo listado
    private $tfor = "Actualización departamento";  // Titulo formulario
    private $ttab = "Departamento,A,E";            // Titulo de las columnas de la tabla
    private $mod  = "Departamento,A,E";            // Funcion del modelo
    
    // Listado de registros *********************************************************************************** 
    public function listAction()
    {       
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Depar($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "datos"     =>  $u->getRegistro(),            
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
    } // Fin listar registros 
    
       
   // Editar y nuevos datos ***********************************************************************************
   public function listaAction() 
   { 
     // Atributos iniciales campos del formulario
     $form = new Formulario();
     $id = (int) $this->params()->fromRoute('id', 0);
     $form->get("id")->setAttribute("value",$id);
     // Fin atributos campos formularios --------
        
     if($this->getRequest()->isPost()) // POST DEL FORMULARIO
     {
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            // Zona de validacion del fomrulario  --------------------
            $album = new ValFormulario();
            $form->setInputFilter($album->getInputFilter());            
            $form->setData($request->getPost());           
            $form->setValidationGroup('nombre'); // ------------------------------------- CAMPOS A VALDAR DEL FORMULARIO (C)                        
            
            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Depar($this->dbAdapter);// -------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)   
                $data = $this->request->getPost();
                //print_r($data);
                $u->actRegistro($data);
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }
        $valores=array
        (
          "titulo"  => $this->tfor,
          "form"    => $form,
          'id'      => $id,
          'url'     => $this->getRequest()->getBaseUrl(),
          "lin"     => $this->lin
        );        
        return new ViewModel($valores);
        
    }else{
      $datos=0;            
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Depar($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            $n = $datos['nombre'];
            // Valores guardados
            $form->get("nombre")->setAttribute("value","$n"); 
         }            
         $valores=array
         (
            "titulo"  => $this->tfor,
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
   // Eliminar dato ***********************************************************************************
   public function listdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Depar($this->dbAdapter);  // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }
   //----------------------------------------------------------------------------------------------------------
        
}
