<?php
/** STANDAR MAESTROS NISSI  */
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;

use Principal\Form\Formulario;     // Componentes generales de todos los formularios
use Principal\Model\ValFormulario; // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos

use Talentohumano\Model\Entity\Matdota;
use Talentohumano\Model\Entity\MatdotaT;

class MatdotaController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/matdota/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Dotaciones"; // Titulo listado
    private $tfor = "Actualización de dotaciones"; // Titulo formulario
    private $ttab = "id,Nombre,Tipo,Modificar,Eliminar"; // Titulo de las columnas de la tabla
    
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = new AlbumTable($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $d->getGeneral("select *, case tipo when 1 then 'Hombre' 
                                                  when 2 then 'Mujer'
						  when 3 then 'Unisex' end as nomTip  
						  from t_mat_dota "),            
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
      
      // Lineas
      $datos = $d->getLinDot("");  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom = $dat['nombre'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idLin")->setValueOptions($arreglo2);                 

      // Tallas
      $datos = $d->getTallasDot("");  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom = $dat['nombre'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("tipoM")->setValueOptions($arreglo2);                       
      
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
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
                $u    = new Matdota($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                if ($data->id==0)
                   $id = $u->actRegistro($data); // Trae el ultimo id de insercion en nuevo registro              
                else 
                {
                   $u->actRegistro($data);             
                   $id = $data->id;
                }
                // Guardar conceptos hijos
                $e = new MatdotaT($this->dbAdapter);
                // Eliminar registros conceptos hijos de esta nomina
                $d->modGeneral("Delete from t_mat_dota_tll where idMdot=".$id); 
                $i=0;
                foreach ($data->tipoM as $dato){
                  $idConcM = $data->tipoM[$i];  $i++; 
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
            $u=new Matdota($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            // Valores guardados
            $form->get("nombre")->setAttribute("value",$datos['nombre']); 
            $form->get("idLin")->setAttribute("value",$datos['idLin']); 
            $form->get("tipo2")->setAttribute("value",$datos['tipo']);             
            $form->get("tipo")->setAttribute("value",$datos['tipo']);  
            // Tallas dotacione
            $d = New AlbumTable($this->dbAdapter);            
            $datos = $d->getGeneral('select b.* from t_mat_dota_tll a 
                                        inner join  t_tallas b on b.id = a.idTalla
                                        where a.idMdot = '.$id);// Tipos de nomina afectadas por este automatico
            $arreglo='';            
            foreach ($datos as $dat){
              $arreglo[]=$dat['id'];
            }                
            $form->get("tipoM")->setValue($arreglo);                       
            
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
            $u=new Matdota($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }
   //----------------------------------------------------------------------------------------------------------
        
}
