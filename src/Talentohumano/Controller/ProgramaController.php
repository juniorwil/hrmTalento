<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos
use Talentohumano\Model\Entity\ProgramaE; // Programacion evento
use Talentohumano\Model\Entity\ProgramaC; // Programacion capacitacion


class ProgramaController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
        
    private $lin  = "/talentohumano/programa/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Programación de eventos y capacitaciones"; // Titulo listado
    private $tfor = "Documento de solicitud"; // Titulo formulario
    private $ttab = "Fecha, Fecha Apr., Sede solicitante, Cargo solicitado, Estado, Pdf, Editar,Eliminar"; // Titulo de las columnas de la tabla

    // Listado de registros ********************************************************************************************
    public function listAction()
    {            
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)      
        $form = new Formulario("form");
 
        // Tipos de eventos
        $datos = $u->getTeventos("");  
        foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'] ;
          $arreglo2[$idc]= $nom;
        }      
        $form->get("tipo")->setValueOptions($arreglo2);
        //
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $u->getPermisos($this->lin), // Permisos de usuarios
            "datosE"    =>  $u->getGeneral(" select *, case when fechaI=fechaF then '.' else '' end as f from t_eventos "), // listado de eventos
            "datosCp"   =>  $u->getGeneral(" select *, case when fechaI=fechaF then '.' else '' end as f"
                    . "                      from t_sol_cap where estado = 1 and fechaI > '2001-01-01'  "), // listado de capacitaciones pogramadas
            "datosC"    =>  $u->getGeneral(" select * from t_sol_cap where estado = 1 and fechaI is null "), // listado de capacitaciones sin pogramar
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin,
            "form"      =>  $form,            
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
      $valores=array
      (
           "titulo"  => $this->tfor,           
           "form"    => $form,
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           "lin"     => $this->lin,          
      );       
      // ------------------------ Fin valores del formulario 
        return new ViewModel($valores);
        
   } // Fin actualizar datos 

    // Guardar documento eventos********************************************************************************************
    public function listgAction()
    {            
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)      
        $form = new Formulario("form");
        
        if($this->getRequest()->isPost()) // Actulizar datos
        {    
             $f=new ProgramaE($this->dbAdapter);
             $request = $this->getRequest();
             if ($request->isPost()) {        
                $data = $this->request->getPost();
                $f->actRegistro($data);         
             }
        }
   } // Fin listar guardar registros 
   
    // Guardar programacion de capacitaciones ********************************************************************************************
    public function listgcAction()
    {            
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)      
        $form = new Formulario("form");
        
        if($this->getRequest()->isPost()) // Actulizar datos
        {    
             $f=new ProgramaC($this->dbAdapter);
             $request = $this->getRequest();
             if ($request->isPost()) {        
                $data = $this->request->getPost();
                $f->actRegistro($data);         
             }
        }
   } // Fin listar guardar registros    
      
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
        
}
