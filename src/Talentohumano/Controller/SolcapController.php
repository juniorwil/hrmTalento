<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Solcap;     // (C)
use Talentohumano\Model\Entity\SolcapI;     // Centros de costos invitados

use Principal\Form\Formulario;      // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;  // Validaciones de entradas de datos
use Principal\Model\AlbumTable;     // Libreria de datos
use Principal\Model\EspFunc;

class SolcapController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/solcap/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Solicitud de capacitaciones"; // Titulo listado
    private $tfor = "Documento de solicitud de capacitación"; // Titulo formulario
    private $ttab = "Area de capacitación,Fecha, Fecha aprobación,Estado,Invitados, Programación,Editar,Eliminar"; // Titulo de las columnas de la tabla

    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new AlbumTable($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $u->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $u->getGeneral("select a.*, b.nombre as nomCcos, c.nombre as nomArea, 
                                            count(d.id) as numEmp  
                                            from t_sol_cap a
                                            inner join n_cencostos b on b.id=a.idCcos
                                            inner join t_areas_capa c on c.id=a.idArea
                                            left join t_sol_cap_i_e d on d.idSol = a.id   
                                            group by a.id 
                                            order by a.fecDoc desc "),            
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin,        
            'url'     => $this->getRequest()->getBaseUrl(),    
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
      // Areas
      $arreglo='';
      $datos = $d->getAreas('');
      foreach ($datos as $dat)
      {
        $idc=$dat['id'];$nom=$dat['nombre'];
        $arreglo[$idc]= $nom;
      }      
      $form->get("idArea")->setValueOptions($arreglo);                    
      // Tipos de capacitaciones
      $arreglo='';
      $datos = $d->getTcapa('');
      foreach ($datos as $dat)
      {
        $idc=$dat['id'];$nom=$dat['nombre'];
        $arreglo[$idc]= $nom;
      }      
      $form->get("tipo")->setValueOptions($arreglo);                          
      // Entidades
      $arreglo='';
      $datos = $d->getEntidades();
      foreach ($datos as $dat)
      {
        $idc=$dat['id'];$nom=$dat['nombre'];
        $arreglo[$idc]= $nom;
      }      
      $form->get("idEnt")->setValueOptions($arreglo);                                
      // Centros de costos
      $arreglo='';
      $datos = $d->getCencos('');
      foreach ($datos as $dat)
      {
        $idc=$dat['id'];$nom=$dat['nombre'];
        $arreglo[$idc]= $nom;
      }      
      $form->get("idCencos")->setValueOptions($arreglo);              
      $form->get("idCcosM")->setValueOptions($arreglo);              
      // Estado
      $daPer = $d->getPermisos($this->lin); // Permisos de esta opcion
      $val=array
       (
            "0"  => 'Revisión',
            "1"  => 'Aprobado'              
        );                  
      $form->get("estado")->setValueOptions($val);
      
      $valores=array
      (
           "titulo"  => $this->tfor,
           "form"    => $form,
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
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
            $form->setValidationGroup('id'); // ------------------------------------- 2 CAMPOS A VALDIAR DEL FORMULARIO  (C)            
            // Fin validacion de formulario ---------------------------
            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Solcap($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                // INICIO DE TRANSACCIONES
                $connection = null;
                try {
                    $connection = $this->dbAdapter->getDriver()->getConnection();
   	            $connection->beginTransaction();                
                    $idI = $u->actRegistro($data);            
                    if ($idI > 0) // Si es mayor que 0 fue un nuevo documento
                        $id = $idI;
                            
                    // Guardar centros de costos 
                    $f = new SolcapI($this->dbAdapter);                
                    // Eliminar registros de tipos de nomina afectados por automaticos  
                    $d->modGeneral("Delete from t_sol_cap_i where idSol=".$id);                 
                    $i=0;
                    foreach ($data->idCcosM as $dato){
                       $idCcos = $data->idCcosM[$i];$i++;           
                       $f->actRegistro($idCcos,$id);               
                       //echo $idCcos.' '.$id;
                       // Guardar invitados de los centros de costos
                       $d->modGeneral("insert into t_sol_cap_i_e ( idSol, idCcos, idEmp ) 
                                        ( select a.id, d.idCcos, d.id as idEmp  
                                          from t_sol_cap a
		                          inner join t_sol_cap_i b on b.idSol = a.id  
                                          inner join n_cencostos c on c.id = b.idCcos 
                                          inner join a_empleados d on d.idCcos = c.id and d.estado = 0 and 
		                          not exists (SELECT null from t_sol_cap_i_e e where e.idSol = a.id and e.idCcos = d.idCcos and e.idEmp = d.id )
		                          and a.id = ".$id." and b.idCcos= ".$idCcos." )");                                                                                   
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
            $u=new Solcap($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            // Valores guardados
            $form->get("comenN")->setAttribute("value",$datos['nombre']); 
            $form->get("comen")->setAttribute("value",$datos['comen']); 
            $form->get("idCencos")->setAttribute("value",$datos['idCcos']); 
            $form->get("idArea")->setAttribute("value",$datos['idArea']); 
            $form->get("tipo")->setAttribute("value",$datos['idTcap']); 
            $form->get("idEnt")->setAttribute("value",$datos['idEnt']); 
            $form->get("estado")->setAttribute("value",$datos['estado']); 
            $form->get("numero")->setAttribute("value",$datos['costo']); 
            // 
            $d = New AlbumTable($this->dbAdapter);            
            $datos = $d->getInvCap(' and idSol='.$id);// Invitados a capacitaciones
            $arreglo='';            
            foreach ($datos as $dat){
              $arreglo[]=$dat['idCcos'];
            }
            $form->get("idCcosM")->setValue($arreglo);                       
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
            $u = new Solcap($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $d = new AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)                      
            $d->modGeneral("delete from t_sol_cap_i_e where idSol = ".$id);                        
            $d->modGeneral("delete from t_sol_cap_i where idSol = ".$id);            
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }          
   }      
   
   // VALIDACION DEL PERIODO PARA GUARDADO DE DATOS
   public function listgAction() 
   {
      $form = new Formulario("form");  
      $request = $this->getRequest();
      if ($request->isPost()) {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new AlbumTable($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $data = $this->request->getPost();       
            $datos = $u->getGeneral1("select idGrup from a_empleados where id=".$data->idEmp);            
            $idGrup = $datos['idGrup'];
            $datos = $u->getGeneral1("select a.idTnom, b.idTcal from n_tip_prestamo a 
                        inner join n_tip_nom b on b.id=a.idTnom
                        where a.id=".$data->idTpres);
            // Buscar datos del periodo
            $datos = $u->getCalenIniFin2($idGrup, $datos['idTcal'], $datos['idTnom']); 
            $arreglo = '';
            foreach ($datos as $dat){
                $idc=$dat['id'];$nom=$dat['fechaI'].' - '.$dat['fechaF'];
                $arreglo[$idc]= $nom;
                break; 
            }  
            // Comprar el periodo que se intenta guardar
            $date   = new \DateTime(); 
            $fecSis = $date->format('Y-m-d');        
            $sw = 0;
            // Fecha del sistema
            $fechaI = $dat['fechaI'];
            $valido = 0;
            if ($fecSis < $fechaI ) // Si es menor que la fecha del sistema no debe guardar el documento
                $valido = 1;
            
            $valores = array(
               "verPer" => $valido,
               "form"   => $form, 
            );                    
            $view = new ViewModel($valores);        
            $this->layout("layout/blancoC");
            return $view;
      }      
   }      

   
   // Invitados *********************************************************************************************
   public function listeAction() 
   { 
      $form = new Formulario("form");
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value",$id);                       

      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d = New AlbumTable($this->dbAdapter);      
      $f = New EspFunc($this->dbAdapter);      
      
      $request = $this->getRequest();
      if ($request->isPost()) { // Guardar datos de invitaciones
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $data = $this->request->getPost();             
            // Buscar el id del cargo 
            $datos = $d->getGeneral("select * from t_sol_cap_i_e where idSol = ".$data->id);
            foreach ($datos as $dato){
               $idLc = $dato['idEmp'];
               $texto = '$data->inv'.$idLc;
               eval("\$estado = $texto;");                  
               //$d->modGeneral("update t_sol_cap_i_e set invitado='.$estado.' where id = ".$dato['id']);                                             
            }                
            $id = $data->id; 
               // Enviar correo
               $htmlBody = 'dos';
               $textBody = 'tres';
               $subject  = '4';
               $from     = 'wil';
               $to       = 'wilsonmet8@gmail.com';        
               $f->sendMail($htmlBody, $textBody, $subject, $from, $to);                          
        }                                
            
      $valores=array
      (
           "titulo"  => "Empleados invitados",
           "form"    => $form,
           "datCc"   => $d->getGeneral("select distinct b.nombre, b.id from t_sol_cap_i a 
                                           inner join n_cencostos b on b.id = a.idCcos 
                                           where a.idSol = ".$id),          
           "datos"   => $d->getGeneral("select b.id, b.nombre as nomCcos, c.CedEmp, 
                                           c.nombre, c.apellido, d.nombre as nomCar, c.id as idEmp, e.invitado    
                                           from t_sol_cap_i a 
                                           inner join n_cencostos b on b.id = a.idCcos 
                                           inner join a_empleados c on c.idCcos = b.id
                                           inner join t_cargos d on d.id = c.idcar  
                                           left join t_sol_cap_i_e e on e.idSol = a.idSol and e.idEmp = c.id 
                                           where a.idSol = ".$id." order by b.id, c.nombre "),
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           "lin"     => $this->lin
      );       
      // ------------------------ Fin valores del formulario 
      return new ViewModel($valores);

   } // Fin actualizar datos    
   
   // Programación *********************************************************************************************
   public function listpAction() 
   { 
      $form = new Formulario("form");
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);
      $form->get("id")->setAttribute("value",$id);                       

      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d = New AlbumTable($this->dbAdapter);      
      
      $request = $this->getRequest();
      if ($request->isPost()) { // Guardar datos de invitaciones
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $data = $this->request->getPost();             
            // Buscar el id del cargo 
            $datos = $d->getGeneral("select * from t_sol_cap_i_e where idSol = ".$data->id);
            foreach ($datos as $dato){
               $idLc = $dato['idEmp'];
               $texto = '$data->inv'.$idLc;
               eval("\$estado = $texto;");                  
               $d->modGeneral("update t_sol_cap_i_e set invitado='.$estado.' where id = ".$dato['id']);               
            }                
            $id = $data->id; 
        }                                
            
      $valores=array
      (
           "titulo"  => "Programación capacitación",
           "form"    => $form,
           "datCc"   => $d->getGeneral("select distinct b.nombre, b.id from t_sol_cap_i a 
                                           inner join n_cencostos b on b.id = a.idCcos 
                                           where a.idSol = ".$id),          
           "datos"   => $d->getGeneral("select b.id, b.nombre as nomCcos, c.CedEmp, 
                                           c.nombre, c.apellido, d.nombre as nomCar, c.id as idEmp, e.invitado    
                                           from t_sol_cap_i a 
                                           inner join n_cencostos b on b.id = a.idCcos 
                                           inner join a_empleados c on c.idCcos = b.id
                                           inner join t_cargos d on d.id = c.idcar  
                                           left join t_sol_cap_i_e e on e.idSol = a.idSol and e.idEmp = c.id 
                                           where a.idSol = ".$id." order by b.id, c.nombre "),
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           "lin"     => $this->lin
      );       
      // ------------------------ Fin valores del formulario 
            $view = new ViewModel($valores);        
//            $this->layout("layout/blancoC");
            return $view;

   } // Fin actualizar datos       
   // Programación *********************************************************************************************
   public function listmailAction() 
   {    
          
       
   }
}
