<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Principal\Model\AlbumTable;        // Libreria de datos
use Talentohumano\Model\Entity\Formch;
use Talentohumano\Model\Entity\Formchi;
use Principal\Form\Formulario;     // Componentes generales de todos los formularios
use Principal\Model\ValFormulario; // Validaciones de entradas de datos

class FormchController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/formch/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Lista de formularios"; // Titulo listado
    private $tfor = "Actualización listado de formularios"; // Titulo formulario
    private $ttab = "Formularios,Tipo,Items,Editar,Eliminar"; // Titulo de las columnas de la tabla
    
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C) 
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $d->getGeneral("select a.*,case when b.id > 0 then count(b.id) else 0 end as numIte,
                                            case a.tipo 
                                               when 1 then 'DESARROLLO' 
                                               when 2 then 'VERIFICACION HOJA DE VIDA'
                                               when 3 then 'VERIFICACION REFERENCIAS LABORALES'
                                               when 4 then 'VERIFICACION PERFIL CARGO' end as nomTip 
                                            from t_form a 
                                            left join t_form_i b on b.idForm=a.id
                                            group by a.id"),            
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
      $form->get("tipo")->setValueOptions(array('1'=>'DESARROLLO',
                                                '2'=>'VERIFICACION HOJA DE VIDA',
                                                '3'=>'VERIFICACION REFERENCIAS LABORALES',
                                                '4'=>'VERIFICACION PERFIL CARGO'));       
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
                $u    = new Formch($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
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
            $u=new Formch($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            $a = $datos['nombre'];
            $b = $datos['tipo'];
            // Valores guardados
            $form->get("nombre")->setAttribute("value","$a"); 
            $form->get("tipo")->setAttribute("value","$b"); 
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
            $u=new Formch($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }          
   }
   
   //----------------------------------------------------------------------------------------------------------
   // FUNCIONES ADICIONALES GUARDADO DE ITEMS   
     
   // Listado de items de la etapa **************************************************************************************
   public function listiAction()
   {
      $form = new Formulario("form");
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value",$id);   
      if($this->getRequest()->isPost()) 
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
                $u    = new Formchi($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u->actRegistro($data);
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'i/'.$id);
            }
        }
      } 
      $form->get("id")->setAttribute("value","$id"); 
      $form->get("ubicacion")->setValueOptions(array('1'=>'Encabezado'));       
      $form->get("tipo")->setValueOptions(array('1'=>'Titulo','2'=>'Texto','3'=>'Lista','4'=>'Casilla de verificación','5'=>'Fecha'));       
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $u = new Formchi($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)
      $d = new AlbumTable($this->dbAdapter);      
      $datos = $d->getGeneral1("Select nombre from t_form where id=".$id);
      $valores=array
      (
           "titulo"    =>  'Ítems del formulario',
           "datos"     =>  $d->getGeneral("Select *, case tipo when 1 then 'Titulo' 
                                           when 2 then 'Texto' 
                                           when 3 then 'Lista'
                                           when 4 then 'Casilla de verificación'
                                           when 5 then 'Fecha' end as tipIfor from t_form_i where idForm=".$id),            
           "ttablas"   =>  'Ítems, Tipo, Detalle,  Eliminar',
           'url'       =>  $this->getRequest()->getBaseUrl(),
           "form"      =>  $form,          
           "lin"       =>  $this->lin,
           "id"        =>  $id,
           "nomFor"    =>  $datos['nombre'],
       );                
       return new ViewModel($valores);        
   } // Fin listar registros items
   // Eliminar dato ********************************************************************************************
   public function listidAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Formchi($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'i');
          }          
   }// Fin eliminar datos
   
}
