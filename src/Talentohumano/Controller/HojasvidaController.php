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
use Talentohumano\Model\Entity\Hojasvida; // (C)
use Talentohumano\Model\Entity\HojasvidaC; // Cargos postulante
use Talentohumano\Model\Entity\HojasvidaE; // Estudios realizados
use Talentohumano\Model\Entity\HojasvidaL; // Experiencia laboral
use Talentohumano\Model\Entity\HojasvidaR; // Referencia personal

use Principal\Model\NominaFunc;        

class HojasvidaController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/hojasvida/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Banco de hojas de vida"; // Titulo listado
    private $tfor = "Actualización de hojas de vida"; // Titulo formulario
    private $ttab = "Cedula, Nombres, Apellidos, Pdf, Editar,Eliminar"; // Titulo de las columnas de la tabla
    
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d=new AlbumTable($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de esta opcion
            "datArb"    =>  $d->getGeneral("select a.id as idSed, a.nombre as nomSed, b.nombre as nomCar, b.id as idCar, 
                                            count(c.id) as numHoj 
                                            from t_sedes a 
                                            inner join t_cargos b on b.idSed=a.id
                                            left join t_hoja_vida_c c on c.idCar=b.id
                                            group by a.id, b.id
                                            order by a.id , b.id"), 
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin,
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
        );                       
        return new ViewModel($valores);
        
    } // Fin listar registros 

    
    public function listeAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);   
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d=new AlbumTable($this->dbAdapter);
        
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de esta opcion
            "datos"     =>  $d->getHojasVida(" and a.idCar=".$id),            
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin,
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
        );                
        $view = new ViewModel($valores);        
        $this->layout('layout/blancoI'); // Layout del login
        return $view;      
        
    } // Fin listar registros     
 
   // Editar y nuevos datos *********************************************************************************************
   public function listaAction() 
   { 
      $form = new Formulario("form");
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value",$id);                       
      // Niveles de aspectos
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d=new AlbumTable($this->dbAdapter);
      // Cargo
      $arreglo='';
      $datos = $d->getCargos(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCarM")->setValueOptions($arreglo);                         

      // Nivel de estudios
      $arreglo='';
      $datos = $d->getNestudios(""); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idNest")->setValueOptions($arreglo);                               
      $empleado='';
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Zona de validacion del fomrulario  --------------------
            $album = new ValFormulario();
            $form->setInputFilter($album->getInputFilter());            
            $form->setData($request->getPost());           
            $form->setValidationGroup('numero'); // ------------------------------------- 2 CAMPOS A VALDIAR DEL FORMULARIO  (C)            
            // Fin validacion de formulario ---------------------------
            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Hojasvida($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                if ($data->id==0)
                   $id = $u->actRegistro($data); // Trae el ultimo id de insercion en nuevo registro              
                else 
                {
                   $u->actRegistro($data);             
                   $id = $data->id;
                }             
                // Guardar cargos del postulado
                $u    = new HojasvidaC($this->dbAdapter);
                $d->modGeneral("Delete from t_hoja_vida_c where idHoj=".$id); 
                $i=0;
                foreach ($data->idCarM as $dato){
                  $idCarM = $data->idCarM[$i];  $i++; 
                  $u->actRegistro($idCarM,$id);                
                }                
                $this->flashMessenger()->addMessage('');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'a/'.$id);
            }
        }       
        
    }else{              
      if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Hojasvida($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            // Valores guardados
            $form->get("cedula")->setAttribute("value",$datos['cedula']); 
            $form->get("nombre")->setAttribute("value",$datos['nombre']); 
            $form->get("apellido1")->setAttribute("value",$datos['apellido']); 
            $form->get("dir")->setAttribute("value",$datos['DirEmp']); 
            $form->get("numero")->setAttribute("value",$datos['TelEmp']); 
            $form->get("sexo")->setAttribute("value",$datos['SexEmp']); 
            $form->get("fecDoc")->setAttribute("value",$datos['FecNac']); 
            $form->get("email")->setAttribute("value",$datos['email']);             
            $form->get("estCivil")->setAttribute("value",$datos['estCivil']);             
            $empleado = $datos['nombre'].' '.$datos['apellido']; 
            // Cargos del postulado
            $d = New AlbumTable($this->dbAdapter);            
            $datos = $d->getCarHoj(' and idHoj='.$id);// Tipos de nomina afectadas por este automatico
            $arreglo='';            
            foreach ($datos as $dat){
              $arreglo[]=$dat['idCar'];
            }                
            $form->get("idCarM")->setValue($arreglo);                       
            
         }        
      }
      $valores=array
      (
          "titulo"  => $this->tfor,
          "form"    => $form,
          'url'     => $this->getRequest()->getBaseUrl(),
          'id'      => $id,
          'datos'   => $datos,  
          'datEst'  => $d->getGeneral("select b.*, c.nombre as nomNest from t_hoja_vida a 
                                       inner join t_hoja_vida_e b on b.idHoj = a.id
                                       inner join t_nivel_estudios c on c.id=b.idNest
                                       where a.id=".$id), 
          'datExp'  => $d->getGeneral("select * from t_hoja_vida_l where idHoj=".$id),           
          'datRep'  => $d->getGeneral("select * from t_hoja_vida_r where idHoj=".$id),           
          "empleado"=> $empleado, 
          "lin"     => $this->lin
      );                
      return new ViewModel($valores);      
   } // Fin actualizar datos 

   // Nivel de estudios
   public function listheAction() 
   { 
      $id = (int) $this->params()->fromRoute('id', 0);
      
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->request->getPost();
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u    = new HojasvidaE($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
            //print_r($data);    
            $u->actRegistro($data);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin."a/".$data->id);
        }   
      }      
      
   } // Fin nivel de estudios
   // Borrar nivel de estudios
   public function listhedAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new HojasvidaE($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                     
            $d=new AlbumTable($this->dbAdapter);
            $datos = $d->getGeneral1("select idHoj from t_hoja_vida_e where id=".$id);
            $u->delRegistro($id);            
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin."a/".$datos['idHoj']);
          }
          
   }
   
   // experiencia laboral
   public function listhlAction() 
   { 
      $id = (int) $this->params()->fromRoute('id', 0);
      
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->request->getPost();
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u    = new HojasvidaL($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
            //print_r($data);    
            $u->actRegistro($data);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin."a/".$data->id);
        }   
      }      
      
   } // Fin experiencia laboral
   // Borrar experiencia laboral
   public function listhldAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new HojasvidaL($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                     
            $d=new AlbumTable($this->dbAdapter);
            $datos = $d->getGeneral1("select idHoj from t_hoja_vida_l where id=".$id);
            $u->delRegistro($id);            
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin."a/".$datos['idHoj']);
          }          
   }   

   // Referencia personal
   public function listhrAction() 
   { 
      $id = (int) $this->params()->fromRoute('id', 0);
      
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->request->getPost();
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u    = new HojasvidaR($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
            //print_r($data);    
            $u->actRegistro($data);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin."a/".$data->id);
        }   
      }      
      
   } // Fin referencia personal
   // Borrar referencia personal
   public function listhrdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new HojasvidaR($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                     
            $d=new AlbumTable($this->dbAdapter);
            $datos = $d->getGeneral1("select idHoj from t_hoja_vida_r where id=".$id);
            $u->delRegistro($id);            
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin."a/".$datos['idHoj']);
          }
          
   }      
   // Eliminar dato ********************************************************************************************
   public function listdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $d=new AlbumTable($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u=new Hojasvida($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $d->modGeneral("delete from t_hoja_vida_r where idHoj=".$id);
            $d->modGeneral("delete from t_hoja_vida_l where idHoj=".$id);
            $d->modGeneral("delete from t_hoja_vida_e where idHoj=".$id);
            $d->modGeneral("delete from t_hoja_vida_c where idHoj=".$id);
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }

}
