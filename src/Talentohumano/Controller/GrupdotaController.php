<?php
/** STANDAR MAESTROS NISSI  */
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Talentohumano\Model\Entity\Grupdota;
use Talentohumano\Model\Entity\GrupdotaI; // Dotaciones

use Principal\Form\Formulario;     // Componentes generales de todos los formularios
use Principal\Model\ValFormulario; // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos


class GrupdotaController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/grupdota/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Grupos de dotaciones"; // Titulo listado
    private $tfor = "Actualización de grupos de dotaciones"; // Titulo formulario
    private $ttab = "id,Nombre,Modificar,Eliminar"; // Titulo de las columnas de la tabla
    
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u = new Grupdota($this->dbAdapter);
        $d = new AlbumTable($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $u->getRegistro(),            
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
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d=new AlbumTable($this->dbAdapter);
      // Calendario de nomina
      $form->get("tipo")->setValueOptions(array("1"=>"Hombre","2"=>"Mujer","3"=>"Unisex" ));                                           
      
      $datos = $d->getInvDot('');// Inventario de dotaciones
      $arreglo='';
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom = $dat['nombre'].' ( '.$dat['tipNom'].' )';
          $arreglo[$idc]= $nom;
      }           
      $form->get("idConcM")->setValueOptions($arreglo);                         
      
      
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
                $u    = new Grupdota($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                if ($data->id==0)
                   $id = $u->actRegistro($data); // Trae el ultimo id de insercion en nuevo registro              
                else 
                {
                   $u->actRegistro($data);             
                   $id = $data->id;
                }
                // Guardar inventarios de dotaciones
                $e = new GrupdotaI($this->dbAdapter);
                // Eliminar dotacion guardada
                $d->modGeneral("Delete from t_grup_dota_m where idGdot=".$id); 
                $i=0;                
                foreach ($data->idConcM as $dato){
                  $idConcM = $data->idConcM[$i];  $i++; 
                  $e->actRegistro($idConcM,$id);                
                }                
                $this->flashMessenger()->addMessage('');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }
        return new ViewModel($valores);
        
    }else{              
      if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Grupdota($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            // Dotaciones guardados
            $form->get("nombre")->setAttribute("value",$datos['nombre']); 
            $form->get("numero")->setAttribute("value",$datos['numero']);             
            //
            $d = New GrupdotaI($this->dbAdapter);            
            $datos = $d->getRegistroId($id);// Dotaciones 

            $arreglo='';            
            foreach ($datos as $dat){
              $arreglo[]=$dat['idDot'];
            }                
            if ($arreglo!='')
               $form->get("idConcM")->setValue($arreglo);                                   
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
            $u=new Grupdota($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }
   //----------------------------------------------------------------------------------------------------------
        
}
