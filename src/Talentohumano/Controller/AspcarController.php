<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Aspcar; // (C)
use Talentohumano\Model\Entity\Aspcari; // (C)
use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos

class AspcarController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/aspcar/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Aspectos de cargos"; // Titulo listado
    private $tfor = "Actualización aspecto de cargo"; // Titulo formulario
    private $ttab = "Aspecto de cargo, Nivel de aspecto,Tipo,Items,Editar,Eliminar"; // Titulo de las columnas de la tabla
        
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d=new AlbumTable($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $d->getGeneral("select a.*, b.nombre as nomNasp,case when b.id > 0 then count(b.id) else 0 end as numIte,
                                            case a.tipo  
					    when 1 then 'INTERPRETACION'
					    when 2 then 'LISTA DE CHEQUEO'
	                                    when 3 then 'EVALUACION POR COMPETENCIAS' end as tipNasp 
                                            from t_asp_cargo a 
                                            inner join t_nivelasp b on b.id=a.idNasp 
					    left join t_asp_cargo_i c on c.idAsp=a.id 
					    group by a.id 
					    order by a.nombre"),            
            "ttablas"   =>  $this->ttab,
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
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
      $form->get("tipo")->setValueOptions(array('1'=>'INTERPRETACION','2'=>'LISTA DE CHEQUEO','3'=>'EVALUACION POR COMPETENCIAS'));       
      // Niveles de aspectos
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d = New AlbumTable($this->dbAdapter);      
      $datos = $d->getNasp();
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }      
      $form->get("idNasp")->setValueOptions($arreglo);  
      
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
                $u    = new Aspcar($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u->actRegistro($data);
                $this->flashMessenger()->addMessage('');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }
        return new ViewModel($valores);
        
    }else{              
      if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Aspcar($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            $a = $datos['nombre'];
            $b = $datos['tipo'];
            $c = $datos['idNasp'];
            // Valores guardados
            $form->get("nombre")->setAttribute("value","$a"); 
            $form->get("tipo")->setAttribute("value","$b"); 
            // Niveles de aspectos
            $d = New AlbumTable($this->dbAdapter);      
            $datos = $d->getNasp();
            $arreglo[0]='( GENERAL )';
            foreach ($datos as $dat){
               $idc=$dat['id'];$nom=$dat['nombre'];
               $arreglo[$idc]= $nom;
            }      
            $form->get("idNasp")->setValueOptions($arreglo);              
            $form->get("idNasp")->setAttribute("value","$c"); 
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
            $u=new Aspcar($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }
   //----------------------------------------------------------------------------------------------------------

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
                $u    = new Aspcari($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u->actRegistro($data);
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }
      } 
      $form->get("id")->setAttribute("value","$id"); 
      $form->get("ubicacion")->setValueOptions(array('1'=>'Tomar esta plantilla','2'=>'Tomarlas en el cargo'));       
      $form->get("tipo")->setValueOptions(array('1'=>'Interpretación','2'=>'Lista','3'=>'A-B-C-D-E','4'=>'1-2-3-4-5'));       
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $u=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)
      $valores=array
      (
           "titulo"    =>  'Ítems del aspecto',
           "datos"     =>  $u->getGeneral("Select *, case tipo when 1 then 'interpretación'  
                                           when 2 then 'Lista' 
                                           when 3 then 'A-B-C-D-E' 
                                           when 4 then '1-2-3-4-5' end as tipoN,
					   case defi when 1 then 'Tomar esta plantilla'
					   when 2 then 'Tomarlas en el cargo' end as defNasp  
                                           from t_asp_cargo_i where idAsp=".$id),            
           "ttablas"   =>  'Nombre , Tipo, Definición, Opciones,  Eliminar',
           'url'       =>  $this->getRequest()->getBaseUrl(),
           "form"      =>  $form,
           "id"        =>  $id,
           "lin"       =>  $this->lin
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
            $u=new Aspcari($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'i');
          }          
   }// Fin eliminar datos
   
   // Guardar opciones de los items de titulos en letras
   public function listoAction() 
   { 
      $form = new Formulario("form");
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value",$id);                            

      // ------------------------ Fin valores del formulario 
      $datos = 0;
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {

                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Aspcar($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u    = new Aspcari($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u->actRegistroO($data);                
                //$u->actRegistro($data);
                $this->flashMessenger()->addMessage('');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'o/'.$data->id);
        }                
    }else{              
      if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Aspcari($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
          }            
      }
      $valores=array
      (
           "titulo"  => $this->tfor,
           "form"    => $form,
           "datos"   => $datos, 
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           "lin"     => $this->lin
      );       
      return new ViewModel($valores);      
   } // Fin actualizar datos 
   
}

