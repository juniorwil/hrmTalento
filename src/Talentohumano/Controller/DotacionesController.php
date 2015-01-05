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
use Talentohumano\Model\Entity\Dotaciones; // (C)


use Principal\Model\NominaFunc;        


class DotacionesController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/dotaciones/list"; // Variable lin de acceso  0 (C)
    private $tlis = "entrega de dotaciones"; // Titulo listado
    private $tfor = "Actualización de empleado"; // Titulo formulario
    private $ttab = "Cedula, Empleado, Cargo, Ultima dot., Dotación, Entregar"; // Titulo de las columnas de la tabla
    
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d=new AlbumTable($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de esta opcion
            "datArb"    =>  $d->getGeneral("select distinct a.id as idSed, a.nombre as nomSed, 
                                            b.nombre as nomCcos, b.id as idCcos, 
					    count(c.id) as numDot ,
					    count(d.id) as numEmp 														  
                                            from t_sedes a 
                                            inner join n_cencostos b on b.idSed=a.id
                                            inner join t_cargos c on c.idCcos=b.id                                            
                                            left join a_empleados d on d.idCcos=b.id  
                                            where b.estado=0 and c.idGdot>1 # 1 porque no aplica a dotacion 
                                            group by a.nombre , b.nombre 
                                            order by a.nombre , b.nombre"), 
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin,
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
        );                
        
//        $datos2  = $d->getGeneral("select a.id,  a.fecIng    
//                                from a_empleados a left join t_cargos b on a.idCar=b.id
//                                inner join n_cencostos c on a.idCcos=c.id order by a.nombre,a.apellido");
//         foreach ($datos2 as $dat){
//             $id = $dat['id'];
//             // Armar cuadro de vacaciones para todos
//             $f = new NominaFunc($this->dbAdapter);      
//             $fecha = $dat['fecIng'];
//             for ($i=0;$i<=35;$i++)
//             {
//               $dat =  $f->getPeriodo($fecha,365);
//               //echo $dat['fechaI'].' - '.$dat['fechaF'].'<br />';
//               $fecha  = $dat['fechaF'];
//               $fechai = $dat['fechaI'];
//               $fechaf = $dat['fechaF'];
//               $d ->modGeneral("insert into n_vacaciones (idEmp, fechaI, fechaF, estado) values(".$id.",'".$fechai."','".$fechaf."',1)");
//             }
//             // Fin armar vacaciones             
//         }

        return new ViewModel($valores);
        
    } // Fin listar registros 

    
    public function listeAction()
    {
        $form = new Formulario("form");
        $id = (int) $this->params()->fromRoute('id', 0);   
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d=new AlbumTable($this->dbAdapter);
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de esta opcion
            "datos"     =>  $d->getEmpM(" and c.id=".$id." and b.idGdot>0"),            
            "ttablas"   =>  $this->ttab,
            "lin"       =>  $this->lin,
            "form"      => $form,
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
        );                
        $view = new ViewModel($valores);        
        $this->layout('layout/blancoI'); // Layout del login
        return $view;      
        
    } // Fin listar registros     
 
    // Agregar materiales al elemento
    public function listgAction() 
    {    
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u    = new Dotaciones($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
            $data = $this->request->getPost();
            $u->actRegistro($data);
            // Marcar dotacion entregada a empleado
            $d    = new AlbumTable($this->dbAdapter);
          } 
       }            
    }       
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
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
      $form->get("idCar")->setValueOptions($arreglo);                         
      // Centro de costos
      $arreglo='';
      $datos = $d->getCencos(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCencos")->setValueOptions($arreglo);                               
      // Grupo de nomina
      $arreglo='';
      $datos = $d->getGrupo(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idGrupo")->setValueOptions($arreglo);                               
      // Calendario de nomina
      $arreglo='';
      $datos = $d->getCalen(''); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCal")->setValueOptions($arreglo);                                     
      // Automaticos de nomina
      $arreglo='';
      $datos = $d->getTautoma(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idTau")->setValueOptions($arreglo);                                           
      $form->get("idTau2")->setValueOptions($arreglo);                                           
      $form->get("idTau3")->setValueOptions($arreglo);                                           
      $form->get("idTau4")->setValueOptions($arreglo);                                           
      // Prefijos contables
      $arreglo='';
      $datos = $d->getPrefcont(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idPrej")->setValueOptions($arreglo);                                                                         
      
      // Tipo de contrato
      $arreglo='';
      $datos = $d->getTipcont(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("tipo")->setValueOptions($arreglo);                                                                                     
      // Fondos prestacionales ---------------
      // Salud
      $arreglo='';
      $datos = $d->getFondos('1'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idSal")->setValueOptions($arreglo);                                                 
      // Pension
      $arreglo='';
      $datos = $d->getFondos('2'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idPen")->setValueOptions($arreglo);                                                       
      // Arp
      $arreglo='';
      $datos = $d->getFondos('3'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idArp")->setValueOptions($arreglo);                                                             
      // Cesntias
      $arreglo='';
      $datos = $d->getFondos('4'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCes")->setValueOptions($arreglo);                                                             
      // Caja de compensacion
      $arreglo[0]= 'No disponible';
      $datos = $d->getFondos('5'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCaja")->setValueOptions($arreglo);                                                                   
      // Fondos aportes voluntarios
      $arreglo[0]= 'No disponible';
      $datos = $d->getFondos('2'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idFav")->setValueOptions($arreglo);                                                                         
      // Fondos aportes AFC
      $arreglo[0]= 'No disponible';
      $datos = $d->getFondos('2'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idFafc")->setValueOptions($arreglo);                                                                               
           
      // Tipo de empleado
      $arreglo='';
      $datos = $d->getTemp(''); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idTemp")->setValueOptions($arreglo);                                                                                     
      // crear libro de vacaciones 
      $f = new NominaFunc($this->dbAdapter);

      // ------------------------ Fin valores del formulario 
      $datos="";      
      $form->get("sueldo")->setAttribute("readOnly", true ); 
      
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
                $u    = new Empleados($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                //print_r($data);

                $u->actRegistro($data);                
                //print_r($post); 
                $this->flashMessenger()->addMessage('');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }       
        
    }else{              
      if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Empleados($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            // Valores guardados
            $form->get("cedula")->setAttribute("value",$datos['CedEmp']); 
            $form->get("nombre")->setAttribute("value",$datos['nombre']); 
            $form->get("apellido1")->setAttribute("value",$datos['apellido']); 
            $form->get("dir")->setAttribute("value",$datos['DirEmp']); 
            $form->get("numero")->setAttribute("value",$datos['TelEmp']); 
            $form->get("sexo")->setAttribute("value",$datos['SexEmp']); 
            $form->get("fecDoc")->setAttribute("value",$datos['FecNac']); 
            $form->get("email")->setAttribute("value",$datos['email']);             
            // Fondos
            $form->get("idSal")->setAttribute("value",$datos['idFsal']); 
            $form->get("idPen")->setAttribute("value",$datos['idFpen']); 
            $form->get("idCes")->setAttribute("value",$datos['idFces']);              
            $form->get("idArp")->setAttribute("value",$datos['idFarp']); 
            $form->get("idCaja")->setAttribute("value",$datos['idCaja']); 
            $form->get("idFav")->setAttribute("value",$datos['idFav']);              
            $form->get("idFafc")->setAttribute("value",$datos['idFafc']);              
            
            // Contractuales            
            $form->get("tipo")->setAttribute("value",$datos['IdTcon']);                              
            $form->get("fecIng")->setAttribute("value",$datos['fecIng']);                              
            $form->get("fecPvac")->setAttribute("value",$datos['fecUlVac']);                              
            $form->get("idTemp")->setAttribute("value",$datos['idTemp']);                              
            
            // Clasificaciones
            $form->get("sueldo")->setAttribute("value",$datos['sueldo'] ); 
            $form->get("idCar")->setAttribute("value",$datos['idCar']); 
            $form->get("idCencos")->setAttribute("value",$datos['idCcos']); 
            $form->get("idGrupo")->setAttribute("value",$datos['idGrup']); 
            $form->get("idCal")->setAttribute("value",$datos['idCal']);                     
            $form->get("idTau")->setAttribute("value",$datos['idTau']);                              
            $form->get("idTau2")->setAttribute("value",$datos['idTau2']);                              
            $form->get("idTau3")->setAttribute("value",$datos['idTau3']);                              
            $form->get("idTau4")->setAttribute("value",$datos['idTau4']);                              
            $form->get("idPrej")->setAttribute("value",$datos['idPref']);                              
            $datos = $datos['nombre'].' '.$datos['apellido']; 
            
         }        
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
   } // Fin actualizar datos 
   
   // Eliminar dato ********************************************************************************************
   public function listdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Empleados($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
            $u->delRegistro($id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
          }
          
   }

}
