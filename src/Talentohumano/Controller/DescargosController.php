<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Descargos; // (C)
use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos

class DescargosController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
        
    private $lin  = "/talentohumano/descargos/list"; // Variable lin de acceso  0 (C)
    private $tlis = "LLamado de atención"; // Titulo listado
    private $tfor = "LLamado de atención"; // Titulo formulario
    private $ttab = "Fecha, Fecha Apr., Tipo de llamado, Citación, Descargos , Estado, Pdf, Editar,Eliminar"; // Titulo de las columnas de la tabla

    // Listado de registros ********************************************************************************************
    public function listAction()
    {            
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)      
                
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $u->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $u->getDescargos(" a.estado in ('0','1') "), // listado de vacantes            
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
      // Tipos de descargas
      $datos = $d->getTdescargos("");  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idCar")->setValueOptions($arreglo2);
      // Tipos de descargas
      $arreglo2 = '';
      $datos = $d->getEmp("");  
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['CedEmp'].' - '.$dat['nombre'].' '.$dat['apellido'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idEmp")->setValueOptions($arreglo2);      
      $form->get("idEmpM")->setValueOptions($arreglo2);      
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
      //
      $valores=array
      (
           "titulo"  => $this->tfor,           
           "form"    => $form,
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           "lin"     => $this->lin,          
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
            $form->setValidationGroup('id'); // ------------------------------------- 2 CAMPOS A VALDIAR DEL FORMULARIO  (C)            
            // Fin validacion de formulario ---------------------------
            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Descargos($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                // INICIO DE TRANSACCIONES
                $connection = null;
                try {
                    $connection = $this->dbAdapter->getDriver()->getConnection();
   	            $connection->beginTransaction();                                
                    $idI = $u->actRegistro($data);            
                    if ($idI > 0) // Si es mayor que 0 fue un nuevo documento
                        $id = $idI;
                    // Eliminar registros de empleados afectados por automaticos  
                    $d->modGeneral("Delete from t_descargos_i where idDes=".$id);                 
                    $i=0;
                    foreach ($data->idEmpM as $dato){
                        $idEmpM = $data->idEmpM[$i];$i++;           
                        $d->modGeneral("insert into t_descargos_i ( idDes, idEmp) values(".$id.",".$idEmpM." )");                                                                                   
                    }                                          
                    $connection->commit();
                    $this->flashMessenger()->addMessage('');
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);               
                }// Fin try casth   
                catch (\Exception $e) {
    	            if ($connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface) {
     	               $connection->rollback();
                       echo $e;
 	            }	
 	            /* Other error handling */
                }// FIN TRANSACCION                                        
            }
        }
        return new ViewModel($valores);
        
      }else{              
       if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Descargos($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            // Valores guardados
            $form->get("comen")->setAttribute("value",$datos['comen']); 
            $form->get("comenN")->setAttribute("value",$datos['suceso']); 
            $form->get("fecDoc")->setAttribute("value",$datos['fecCita']);             
            $form->get("hora")->setAttribute("value",$datos['horaCita']);             
            $form->get("estado")->setAttribute("value",$datos['estado']); 
            $form->get("idCar")->setAttribute("value",$datos['idTdes']);   
            $form->get("idEmp")->setAttribute("value",$datos['idEmp']);   
            $form->get("fechaIni")->setAttribute("value",$datos['fecSuc']);   
            $form->get("hora2")->setAttribute("value",$datos['horSuc']);               
            
            $d = New AlbumTable($this->dbAdapter);            
            $datos = $d->getGeneral('Select * from t_descargos_i where idDes='.$id);// Invitados a capacitaciones
            $arreglo='';            
            foreach ($datos as $dat){
              $arreglo[]=$dat['idEmp'];
            }
            $form->get("idEmpM")->setValue($arreglo);                                   
         }     
         if ($this->lin!='')// Importante para la seguridad de ingreso al modulo
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
            $u=new Descargos($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }          
   }
   
   // Hsitorial de descargos ********************************************************************************************
   public function listhAction() 
   {
      $form = new Formulario("form");
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value",$id);                       
      // Sedes
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d = New AlbumTable($this->dbAdapter);      
      // Evaluadores del descargo
      $datos = $d->getEvaDescar("");  
      $arreglo2 = '';
      foreach ($datos as $dat){
          $idc=$dat['idEmp'];$nom = $dat['CedEmp'].' - '.$dat['nombre'].' '.$dat['apellido'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idEmp")->setValueOptions($arreglo2);
      
      // Empleados involucrados
      $datos = $d->getGeneral("select b.id, b.CedEmp, b.nombre, b.apellido from t_descargos_i a 
                                  inner join a_empleados b on b.id = a.idEmp 
                                  where a.idDes = ".$id);  
      $arreglo2 = '';
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom = $dat['CedEmp'].' - '.$dat['nombre'].' '.$dat['apellido'];
          $arreglo2[$idc]= $nom;
      }      
      $form->get("idEmp2")->setValueOptions($arreglo2);            
      
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
      //
      $valores=array
      (
           "titulo"  => "Descargo",           
           "form"    => $form,
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           "lin"     => $this->lin,          
      );              
      $id = (int) $this->params()->fromRoute('id', 0);
      return new ViewModel($valores);          
   }   
   //----------------------------------------------------------------------------------------------------------
   // Dialogo de descargo
   public function listhdAction()
   {
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);          
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter);      
        
        if ( $data->resp != '' )// Registro de respuiesta        
            $datos = $d->modGeneral("insert into t_descargos_d "
                . "(idDes, idEmp, idEmpR, pregunta, respuesta) "
                . "values(".$data->id.",".$data->idEmp.",".$data->idEmp2.",'".$data->preg."','".$data->resp."' )");
      }
      $valores=array
      (
           "titulo"  => "Descargo",           
           'url'     => $this->getRequest()->getBaseUrl(),
           'datos'   => $d->getGeneral("select a.*, b.nombre, b.apellido, c.nombre as nombreR, 
                                           c.apellido as apellidoR,
                                           substring( a.fecha, 11, 10 ) as hora 
                                           from t_descargos_d a 
                                           inner join a_empleados b on b.id = a.idEmp
                                           inner join a_empleados c on c.id = a.idEmpR
                                           where a.idDes=".$data->id),
           "lin"     => $this->lin,          
      );                    
      $view = new ViewModel($valores);        
      $this->layout('layout/blancoC'); // Layout del login
      return $view;                    
   } // Fin listar registros orden          
   // Eliminar dialogo de descargo
   public function listhddAction()
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $d = New AlbumTable($this->dbAdapter);      
            $datos = $d->getGeneral1("Select idDes from t_descargos_d where id=".$id);
            $d->modGeneral("delete from t_descargos_d where id=".$id);                        
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'h/'.$datos['idDes']);
          }      
      
   } // Fin listar registros orden             
}
