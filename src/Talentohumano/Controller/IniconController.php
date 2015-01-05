<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Inicon; // (C)
use Talentohumano\Model\Entity\IniconC; // (C)
use Talentohumano\Model\Entity\IniconA; // (C)
use Talentohumano\Model\Entity\IniconF; // Archivo adjunto
use Talentohumano\Model\Entity\IniconForm; // Formularios
use Talentohumano\Model\Entity\IniconE; // Guardado datos del nuevo empleado
use Talentohumano\Model\Entity\IniconHl; // Verificacion hojas de vida laborales
use Talentohumano\Model\Entity\IniconHla; // Verificacion hojas de vida laborales ampliadas
use Talentohumano\Model\Entity\IniconHr; // Verificacion hojas de vida refe personales
use Talentohumano\Model\Entity\IniconHe; // Verificacion hojas de vida estudios realizados
use Talentohumano\Model\Entity\IniconVc; // Verificacion del perfil del cargo

use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Form\FormChequ;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos



class IniconController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/inicon/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Procesos de contrataciones"; // Titulo listado
    private $tfor = "Proceso de contratación"; // Titulo formulario
    private $ttab = "Documento,Fecha, Fecha Apr., Sede,Centro de costo, Cargo, Aspirantes";

    public function listAction()
    {
       $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
       $d = New AlbumTable($this->dbAdapter); 
       $valores=array
       (
           "titulo"    =>  $this->tlis,
           "datos"     =>  $d->getSolcon(" a.estado in ('1') "), // listado de vacantes                       
           "datPos"    =>  $d->getGeneral("select a.id, c.nombre, c.apellido 
                                 from t_sol_con a
                                     inner join t_lista_cheq b on b.idSol = a.id 
                                     inner join t_hoja_vida c on c.id = b.idHoj order by a.id, c.id"),                       
           "ttablas"   =>  $this->ttab,
           "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
           "lin"       =>  $this->lin
       );                
       return new ViewModel($valores);        
    } // Fin listar registros     
    // Documentos de solicitud ********************************************************************************************   
    public function listsAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        $valores=array
        (
            "titulo"    =>  $this->tlis,
            "daPer"     =>  $d->getPermisos($this->lin), // Permisos de usuarios
            "datos"     =>  $d->getAspi(" a.idSol=".$id." and a.estado in ('0','1','2')"), // listado de vacantes            
            "datosT"    =>  $d->getEtcehqSt(" a.idSol=".$id." and etapa=1 "), // Total sumas
            "datosTc"   =>  $d->getEtcehqStc(" a.idSol=".$id." and etapa=1 and b.estado > 0 "), // Total sumas calificadas
            "datosT2"   =>  $d->getEtcehqSt(" a.idSol=".$id." and etapa=2 "), // Total sumas
            "datosTc2"  =>  $d->getEtcehqStc(" a.idSol=".$id." and etapa=2 and b.estado > 0 "), // Total sumas calificadas            
            "ttablas"   =>  "Aspirante, En proceso de contratación, En administración de la contratación, Pdf , Estado",
            "lin"       =>  $this->lin,            
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
        );                
        return new ViewModel($valores);        
    } // Fin listar registros 

    public function listiAction() // Listar aspirantes a cargos  
    {        
        $form  = new Formulario("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");        
        //----
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter);  

         //
         $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
         $d = New AlbumTable($this->dbAdapter); 
         $con=' f.tipo=1 and a.id='.$id;
         // Insertar registros que no estan en la lista
         $con2 = 'insert into t_lista_cheq_d (idCheq, idEtaI, etapa ) (
                  select a.id, g.id, f.tipo 
                  from t_lista_cheq a inner join t_sol_con b
                  on a.idSol=b.id inner join t_cargos c on b.idCar=c.id
                  inner join t_nivel_cargo d on d.id=c.idNcar
                  inner join t_nivel_cargo_o e on e.idNcar=d.id
                  inner join t_etapas_con f on e.idEtapa=f.id
                  inner join t_etapas_con_i g on g.idEtacon=f.id 
                  WHERE not exists (SELECT null from t_lista_cheq_d where a.idHoj=idHoj and a.id=idCheq and g.id=idEtaI and f.tipo=etapa ) and '.$con.'
                )' ;     
         $d->modGeneral($con2);        
         // Estado del documento
         $datos = $d->getGeneral1("Select idSol, estado from t_lista_cheq where id=".$id);
         $idSol = $datos['idSol'];
         switch ($datos['estado']) {
             case 0: // Revision 
                 $val=array
                 (
                    "0"  => 'Revisión',
                    "1"  => 'Continua en el proceso',
                    "2"  => 'No sigue en el proceso'
                 );
                 break;
             case 1: // Con
                 $val=array
                 (
                    "1"  => 'Continua en el proceso',
                 );
                 break;
             case 2: // No sigo en el proceso
                 $val=array
                 (
                    "2"  => 'No sigue en el proceso'
                 );
                 break;
         }             
         $form->get("estado")->setValueOptions($val);
         
         $form->get("estado")->setAttribute("value",$datos['estado']); 
        
         // Buscar las etapas de la contratacion        
        
         $valores=array
         (
            "titulo"    =>  $this->tfor,
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datPos"    =>  $d->getGeneral("select a.*,b.fecDoc,d.nombre as nomCar, e.nombre as nomSede
                                            ,c.cedula,  c.nombre, c.apellido, a.contratado, a.empleado, b.vacantes    
                                            from t_lista_cheq a inner join t_sol_con b
                                            inner join t_hoja_vida c on c.id=a.idHoj           
                                            inner join t_cargos d on d.id=b.idCar  
                                            inner join t_sedes e on e.id=d.idSed
                                            inner join t_hoja_vida_c f on f.idHoj=c.id and f.idCar=b.idCar 
			                    inner join t_lista_cheq_d g on g.idCheq=a.id 
                                            where g.idCheq=".$id),
            "datos"     =>  $d->getEtcehq($con), // extrae las etapas de contratacion de un determinado cargo            
            "datAdj"    =>  $d->getAdjCheq($id),
            "datVref"   =>  $d->getGeneral("select b.* from t_lista_cheq_d a 
                                            inner join t_lista_cheq_hl b on b.idDcheq = a.id   
                                            where a.idCheq =".$id), 
            "datVlab"   =>  $d->getGeneral("select b.* from t_lista_cheq_d a 
                                            inner join t_lista_cheq_vc b on b.idDcheq = a.id   
                                            where a.idCheq =".$id),              
            "datGen"    =>  $d->getConfiguraG(" where id=1"), // Obtener datos de configuracion general                 
            "ttablas"   =>  'Detalle, Descripción, Estado, Adjunto',
            "form"      =>  $form,
            "idCheq"    =>  $id, 
            "idSol"     =>  $idSol,
            "flashMessages" => $this->flashMessenger()->getMessages(), // Mensaje de guardado
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin listar registros     

    // Lista de chequeo proceso de contratacion etapa1
    public function listaAction() 
    { 
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);  
      
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Inicon($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $u->actRegistro($data->id, ' ', $data->estado);
        // Actualizar estado del documento
        $c = new IniconC($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $f = new AlbumTable($this->dbAdapter);
        $datGen = $f->getConfiguraG(" where id=1"); // Obtener datos de configuracion general        
        $rutaP = $datGen['ruta']; // Ruta padre
        
        $datos = $f->getGeneral("Select * from t_lista_cheq_d where etapa=1 and idCheq=".$data->id);
        foreach ($datos as $dato){
            $idLc = $dato['id'];
            $texto = '$data->texto'.$idLc;
            eval("\$texto = $texto;");             
            $estado = '$data->estado'.$idLc;
            eval("\$estado = $estado;"); 
            $c->actRegistro($idLc, $texto, $estado);                 
            // GUARDAR IMAGANES
            $v='adjunto'.$idLc;
            $File    = $this->params()->fromFiles($v);            
            if ($File['name']!='') // Tiene adjuntos
            {                 
                 $adapter = new \Zend\File\Transfer\Adapter\Http();

                 $ruta = $rutaP."/Datos/Talentohumano/Contratacion/".$data->id;
                 // Verificar carpeta de lista de cheqeo
                 $mode = 0777;
                 if (!is_dir($ruta) && !mkdir($ruta, $mode)) {
                     return false;
                 }                 
                 // Crear sub$rutaPcarpeta
                 $ruta = $rutaP."/Datos/Talentohumano/Contratacion/".$data->id."/".$idLc;                 
                 // Verificar carpeta de lista de cheqeo
                 $mode = 0777;
                 if (!is_dir($ruta) && !mkdir($ruta, $mode)) {
                     return false;
                 }                                  
                 //                 
                 $adapter->setDestination($ruta);
                 if ($adapter->receive($File['name'])) {                        
                    // Guardar en tabla
                    $u=new IniconF($this->dbAdapter);
                    $datos = $u->actRegistro($data->id , $idLc, $File['name']);   
                 }                  
            }
        }    
        //
        $this->flashMessenger()->addMessage('');
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'i/'.$data->id);
      }     
   }

    // Borrar adjunto de archivo proceso 
    public function listidaAction() 
    {        
        $id = (int) $this->params()->fromRoute('id', 0);  
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $f = new AlbumTable($this->dbAdapter);
        $datGen = $f->getConfiguraG(" where id=1"); // Obtener datos de configuracion general        
        $rutaP = $datGen['ruta']; // Ruta padre
        
        $datos = $f->getGeneral1("Select idCheq, idDcheq, nombre from t_lista_cheq_d_a where id=".$id);        
        //echo 'dd'.$datos['idCheq'];
        $ruta = $rutaP."/Datos/Talentohumano/Contratacion/".$datos["idCheq"]."/".$datos['idDcheq']."/".$datos['nombre'];
        if (unlink($ruta))
        {
            $f->modGeneral('delete from t_lista_cheq_d_a where id='.$id);
        }       
        //return new ViewModel();
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'i/'.$datos["idCheq"]);
    }    
    // Borrar adjunto de archivo administracion
    public function listidadAction() 
    {        
        $id = (int) $this->params()->fromRoute('id', 0);  
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $f = new AlbumTable($this->dbAdapter);
        $datGen = $f->getConfiguraG(" where id=1"); // Obtener datos de configuracion general        
        $rutaP = $datGen['ruta']; // Ruta padre
        
        $datos = $f->getGeneral1("Select idCheq, idDcheq, nombre from t_lista_cheq_d_a where id=".$id);        
        //echo 'dd'.$datos['idCheq'];
        $ruta = $rutaP."/Datos/Talentohumano/Contratacion/".$datos["idCheq"]."/".$datos['idDcheq']."/".$datos['nombre'];
        if (unlink($ruta))
        {
            $f->modGeneral('delete from t_lista_cheq_d_a where id='.$id);
        }       
        //return new ViewModel();
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'ad/'.$datos["idCheq"]);
    }    
    
   // Lista de chequeo proceso de contratacion etapa2
    public function lista2Action() 
    { 
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);  
      
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Inicon($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        //print_r($data);
        $u->actRegistro($data->id, ' ', $data->estado);
        // Actualizar estado del documento
        $c = new IniconC($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $f = new AlbumTable($this->dbAdapter);
        $datGen = $f->getConfiguraG(" where id=1"); // Obtener datos de configuracion general        
        $rutaP = $datGen['ruta']; // Ruta padre
        
        $datos = $f->getGeneral("Select * from t_lista_cheq_d where etapa=2 and idCheq=".$data->id);
        foreach ($datos as $dato){
            $idLc = $dato['id'];
            $texto = '$data->texto'.$idLc;
            eval("\$texto = $texto;");             
            $estado = '$data->estado'.$idLc;
            eval("\$estado = $estado;"); 
            $c->actRegistro($idLc, $texto, $estado);                 
            // GUARDAR IMAGANES
            $v='adjunto'.$idLc;
            $File    = $this->params()->fromFiles($v);            
            if ($File['name']!='') // Tiene adjuntos
            {                 
                 $adapter = new \Zend\File\Transfer\Adapter\Http();
                 //$ruta = dirname(__DIR__);                 
                 $ruta = $rutaP."/Datos/Talentohumano/Contratacion/".$data->id;
                 // Verificar carpeta de lista de cheqeo
                 $mode = 0777;
                 if (!is_dir($ruta) && !mkdir($ruta, $mode)) {
                     return false;
                 }                 
                 // Crear subcarpeta
                 $ruta = $rutaP."/Datos/Talentohumano/Contratacion/".$data->id."/".$idLc;
                 // Verificar carpeta de lista de cheqeo
                 $mode = 0777;
                 if (!is_dir($ruta) && !mkdir($ruta, $mode)) {
                     return false;
                 }                                  
                 //                 
                 $adapter->setDestination($ruta);
                 if ($adapter->receive($File['name'])) {                        
                    // Guardar en tabla
                    $u=new IniconF($this->dbAdapter);
                    $datos = $u->actRegistro($data->id , $idLc, $File['name']);   
                 }                  
            }
        }    
        //
        $this->flashMessenger()->addMessage('');
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'ad/'.$data->id);
      }     
   }
   
   // Cambiar estados del proceso
   public function listeAction()
   {        
     $data = $this->request->getPost();
     $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
     $d = New AlbumTable($this->dbAdapter); 
     $con = 'update t_lista_cheq set estado=2 where id='.$data['id'];     
     $d->modGeneral($con); // listado de vacantes            
     return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
    } // Fin listar registros    
    //----------------------------------------------------------------------------------------------------------
    // Guardar datos basicos del empleado
   public function listgeAction()
   {    
      $form = new Formulario("form"); 
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        //print_r($data);
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter);         
        $datosE = $d->getGeneral1("select a.id as idHoj, a.cedula, a.nombre, a.apellido, 
                                  a.TelEmp, a.DirEmp, a.email, a.SexEmp, 
                                  a.FecNac, c.idCar, c.id as idSol, c.idCcos, c.idEsal, 
                                  case when c.salario > 0 then c.salario else e.salario end as salario       
                                  from t_hoja_vida a 
                                  inner join t_lista_cheq b on b.idHoj=a.id 
                                  inner join t_sol_con c on c.id=b.idSol 
                                  inner join t_cargos d on d.id=c.idCar 
                                  inner join n_salarios e on e.id = c.idEsal 
                                  where b.id=".$data->id);
        $idSol = $datosE['idSol'];
        $idHoj = $datosE['idHoj'];
        $idCcos = $datosE['idCcos'];
        $salario = $datosE['salario'];        
        $cedula = $datosE['cedula'];        

        $u = New IniconE($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 

        // INICIO DE TRANSACCIONES
        $connection = null;
        try 
        {
            $connection = $this->dbAdapter->getDriver()->getConnection();
            $connection->beginTransaction();                

            $u->actRegistro($data, $datosE, $idCcos);
            $d = New AlbumTable($this->dbAdapter); 

            $datEmp = $d->getGeneral1("select id from a_empleados a where a.CedEmp = ".$cedula);
            $idEmp = $datEmp['id'];

            $d->modGeneral( 'update t_lista_cheq set empleado=1 where id='.$data->id );                                             
            $d->modGeneral( 'update a_empleados set sueldo='.$salario.' where idHoj='.$idHoj );                                                    
            

            // Insertar en libro de contratos
            $mes =  $data->ano;       
            $datTcon = $d->getGeneral1("select meses, tipo from a_tipcon a where a.id = ".$data->tipo2); // Buscar para ver si tiene meses fijos
            if ( $datTcon['meses'] > 0 )
                 $mes =  $datTcon['meses'] ;       
            if ( $datTcon['tipo'] != 1 )      
            {
                $d->modGeneral("insert into n_emp_contratos ( idEmp, fechaI, fechaF )
                  values(".$idEmp.",'".$data->fecDoc."', ( DATE_ADD( '".$data->fecDoc."' , interval ".$mes." month) )   )" );
            }
            else{ // Contrato indefinido
                $d->modGeneral("insert into n_emp_contratos ( idEmp, fechaI, fechaF )
                  values(".$idEmp.",'".$data->fecDoc."', '2999-01-01'   )" );                
            }

            $connection->commit();                    
            $this->flashMessenger()->addMessage('');
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'s/'.$idSol);         
        }// Fin try casth   
        catch (\Exception $e) {
            if ($connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface) {
                $connection->rollback();
                echo $e;
            }   
                /* Other error handling */
        }// FIN TRANSACCION                                                
                
        //        
      }     
    } // Fin listar registros        
    
    
    
    // FORMULARIOS 
    public function listfAction() // Verificacion del perfil
    {        
        $form  = new Formulario("form");
        $formc = new FormChequ("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");
        //----
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        $con=' f.tipo=1 and a.id='.$id;
        if($this->getRequest()->isPost()) // Actulizar datos
        {  
            $request = $this->getRequest();
            $data = $this->request->getPost();           
            $id = $data->id;

            // Buscar nivel del cargo 
            $dat = $d->getGeneral1("select c.idNasp from t_lista_cheq a inner join t_lista_cheq_d d on a.id=d.idCheq
                            inner join t_sol_con b on a.idSol=b.id
                            inner join t_cargos c on b.idCar=c.id
                            where d.id=".$id);
            //print_r($dat);
            $idn = $dat['idNasp'];           
            
            $dato = $d->getGeneral1("Select distinct idCheq, idEtai from t_lista_cheq_d where id=".$id);

            $con=' and a.id = '.$dato['idEtai'];
            $datos  = $d->getIformI( $con , $id );            
            // Guardar           
            $u = New IniconForm($this->dbAdapter);                       
            
            $d->modGeneral("Delete from t_lista_cheq_f where idDcheq=".$id); 
            foreach ($datos as $dato){
                $idLc = $dato['idIform'];
                
                $lista = 0;
                $val = '$data->lista'.$idLc; //  VALORES DE LISTA
                eval("\$tex = $val;");                             
                if ( $tex != NULL)
                    $lista = $tex;
                $texto = '';
                $val = '$data->res'.$idLc; //  TEXTO
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $texto = $tex;   
                $a=0;
                $val = '$data->ch'.$idLc; // Check de verificacion
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $a = $tex;                   

                $u->actRegistro($data->id ,$idLc ,$lista, $texto, $a);               

           }
           return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'f/'.$data->id);
           
        }               
        
        // Buscar nivel del cargo 
        $da = $d->getGeneral("select c.idNasp from t_lista_cheq a inner join t_lista_cheq_d d on a.id=d.idCheq
                            inner join t_sol_con b on a.idSol=b.id
                            inner join t_cargos c on b.idCar=c.id
                            where d.id=".$id);
        $idn=0; 
        foreach ($da as $dat){
          $idn = $dat['idNasp'];
        }
        $dato = $d->getGeneral1("Select distinct idCheq, idEtai from t_lista_cheq_d where id=".$id);
        $valores=array
        (
            "titulo"    =>  $this->tfor,
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datos"     =>  $d->getIformI(' and a.id = '.$dato['idEtai'], $id ), 
            "datSol"    =>  $d->getDatSol($dato['idCheq']),
            "ttablas"   =>  'Detalle, Calificación, Acción',
            "form"      =>  $form,
            "formc"     =>  $formc,
            "idCheq"    =>  $dato['idCheq'],
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin listar registros     
    
    // VERIFICACION DE REFERENCIAS HOJA DE VIDA
    public function listvpAction()  
    {        
        $form  = new Formulario("form");
        $formc = new FormChequ("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");      
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        // Referencias laborales
        $datExp = $d->getGeneral("select d.*, e.descripcion, e.estado 
                                           from t_lista_cheq_d a 
                                           inner join t_lista_cheq b on b.id = a.idCheq 
                                           inner join t_hoja_vida c on c.id=b.idHoj
                                           inner join t_hoja_vida_l d on d.idHoj=c.id 
                                           left join t_lista_cheq_hl e on e.idLhoj = d.id 
                                           where a.id=".$id);
        // Estudios realziados
        $datEst = $d->getGeneral("select d.*, e.nombre as nomNest, f.descripcion, f.estado  
                                           from t_lista_cheq_d a 
                                           inner join t_lista_cheq b on b.id = a.idCheq  
                                           inner join t_hoja_vida c on c.id=b.idHoj 
                                           inner join t_hoja_vida_e d on d.idHoj=c.id 
                                           inner join t_nivel_estudios e on e.id=d.idNest 
                                           left join t_lista_cheq_he f on f.idLhoj = d.id 
                                           where a.id=".$id);
        $datRef = $d->getGeneral("select d.*, f.descripcion, f.estado 
                                           from t_lista_cheq_d a 
                                           inner join t_lista_cheq b on b.id = a.idCheq 
                                           inner join t_hoja_vida c on c.id=b.idHoj
                                           inner join t_hoja_vida_r d on d.idHoj=c.id 
                                           left join t_lista_cheq_hr f on f.idLhoj = d.id 
                                           where a.id=".$id);
        // Guardar
        if($this->getRequest()->isPost()) // Actulizar datos
        {  
           $d->modGeneral("Delete from t_lista_cheq_hl where idDcheq=".$id); 
           $d->modGeneral("Delete from t_lista_cheq_hr where idDcheq=".$id);
           $d->modGeneral("Delete from t_lista_cheq_he where idDcheq=".$id); 
           
           $request = $this->getRequest();
           $data = $this->request->getPost();        
           $u = New IniconHl($this->dbAdapter);
           // Experiencia laboral
           foreach ($datExp as $dato){
                $idLc = $dato['id'];
                $texto = '$data->textoL'.$idLc;
                eval("\$texto = $texto;");             
                $estado = '$data->estadoL'.$idLc;
                eval("\$estado = $estado;");                             
               // echo $texto;
                $u->actRegistro($data->id ,$idLc ,$texto, $estado);               
           }
           $u = New IniconHe($this->dbAdapter);
           // Referencias personales
           foreach ($datEst as $dato){
                $idLc = $dato['id'];
                $texto = '$data->textoE'.$idLc;
                eval("\$texto = $texto;");             
                $estado = '$data->estadoE'.$idLc;
                eval("\$estado = $estado;");                             
               // echo $texto;
                $u->actRegistro($data->id ,$idLc ,$texto, $estado);               
           }           
           $u = New IniconHr($this->dbAdapter);
           // Estudios personales
           foreach ($datRef as $dato){
                $idLc = $dato['id'];
                $texto = '$data->textoR'.$idLc;
                eval("\$texto = $texto;");             
                $estado = '$data->estadoR'.$idLc;
                eval("\$estado = $estado;");                             
               // echo $texto;
                $u->actRegistro($data->id ,$idLc ,$texto, $estado);               
           }                      
        }
        //----                
        $dato = $d->getGeneral1("Select distinct idCheq from t_lista_cheq_d where id=".$id);
        $valores=array
        (
            "titulo"    =>  "Verificación de la hoja de vida",
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datSol"    =>  $d->getDatSol($dato['idCheq']),
            "datExp"    =>  $datExp,
            "datEst"    =>  $datEst,            
            "datRef"    =>  $datRef,            
            "ttablas"   =>  'Detalle, Calificación, Acción',
            "form"      =>  $form,
            "id"        =>  $id,
            "idCheq"    =>  $dato['idCheq'],
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin verificacion de referencias    

    // VERIFICACION DE REFERENCIAS LABORALES
    public function listvrAction()  
    {        
        $form  = new Formulario("form");
        $formc = new FormChequ("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");      
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        // Referencias laborales
        $datExp = $d->getGeneral("select d.*, e.descripcion, e.estado,
                                           e.cargo as carInf , e.funciones as funInf, e.motivo, e.des, e.contra,
                                           e.descripcion, e.nomInf, e.carInf, e.telInf  
                                           from t_lista_cheq_d a 
                                           inner join t_lista_cheq b on b.id = a.idCheq 
                                           inner join t_hoja_vida c on c.id=b.idHoj
                                           inner join t_hoja_vida_l d on d.idHoj=c.id 
                                           left join t_lista_cheq_hl e on e.idLhoj = d.id 
                                           where a.id=".$id);

        // Guardar
        if($this->getRequest()->isPost()) // Actulizar datos
        {  
           $d->modGeneral("Delete from t_lista_cheq_hl where idDcheq=".$id); 
           
           $request = $this->getRequest();
           $data = $this->request->getPost();        
           $u = New IniconHla($this->dbAdapter);
           // Experiencia laboral
           foreach ($datExp as $dato){
                $idLc = $dato['id'];
                $texto = '$data->cargo'.$idLc;
                eval("\$cargo = $texto;");             
                $estado = '$data->funcion'.$idLc;
                eval("\$funcion = $estado;");    
                $estado = '$data->motivo'.$idLc;
                eval("\$motivo = $estado;");    
                $estado = '$data->desemp'.$idLc;
                eval("\$des = $estado;");                                                             
                $estado = '$data->contra'.$idLc;
                eval("\$contra = $estado;");    
                $estado = '$data->observa'.$idLc;
                eval("\$observa = $estado;");             
                // Datos del informante
                $estado = '$data->nomInf'.$idLc;
                eval("\$nombre = $estado;");    
                $estado = '$data->carInf'.$idLc;
                eval("\$carInf = $estado;");                                             
                $estado = '$data->telefono'.$idLc;
                eval("\$telInf = $estado;");                                                             
               // echo $texto;
                $u->actRegistro($data->id ,$idLc ,$observa, $estado, $cargo, $funcion, $motivo, $des, $contra, $nombre, $carInf, $telInf );               
           }
           $u = New IniconHe($this->dbAdapter);
        }
        //----                
        $dato = $d->getGeneral1("Select distinct idCheq from t_lista_cheq_d where id=".$id);
        $valores=array
        (
            "titulo"    =>  "Verificación referencias laborales",
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datSol"    =>  $d->getDatSol($dato['idCheq']),
            "datExp"    =>  $datExp,
            "ttablas"   =>  'Detalle, Detalle, Acción',
            "form"      =>  $form,
            "id"        =>  $id,
            "idCheq"    =>  $dato['idCheq'],
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin verificacion de referencias laborales       
    
    // VERIFICACION DEL PERFIL DEL CARGO
    public function listvcAction() // Verificacion del perfil
    {        
        $form  = new Formulario("form");
        $formc = new FormChequ("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");
        //----
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        
        if($this->getRequest()->isPost()) // Actulizar datos
        {  
            $request = $this->getRequest();
            $data = $this->request->getPost();           
            $id = $data->id;
           //print_r($data);
            $d->modGeneral("Delete from t_lista_cheq_vc where idDcheq=".$data->id); 

            $con=' f.tipo=1 and a.id='.$id;
            // Buscar nivel del cargo 
            $da = $d->getGeneral("select c.idNasp, d.idCheq from t_lista_cheq a inner join t_lista_cheq_d d on a.id=d.idCheq
                            inner join t_sol_con b on a.idSol=b.id
                            inner join t_cargos c on b.idCar=c.id
                            where d.id=".$id);
            $idn=0; 
            foreach ($da as $dat){
               $idn = $dat['idNasp'];
            }           
           $u = New IniconVc($this->dbAdapter);
           $datos  = $d->getNaspN(' and b.idNasp='.$idn, $dat['idCheq'] );
           // Guardar
           $d->modGeneral("delete from t_lista_cheq_vc where idDcheq=".$data->id);
           foreach ($datos as $dato){
                $idLc = $dato['id'];
                
                $lista = 0;
                $val = '$data->lista'.$idLc; //  VALORES DE LISTA
                eval("\$tex = $val;");                             
                if ( $tex != NULL)
                    $lista = $tex;
                
                $texto = '';
                $val = '$data->res'.$idLc; //  VALORES DE LISTA
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $texto = $tex;   
                $a=0;
                $val = '$data->a'.$idLc; 
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $a = $tex;                   
                $b=0;
                $val = '$data->b'.$idLc; 
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $b = $tex;                                   
                $c=0;
                $val = '$data->c'.$idLc; 
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $c = $tex;                                                   
                $d=0;
                $val = '$data->d'.$idLc; 
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $d = $tex;                                                                   
                $e=0; 
                $val = '$data->e'.$idLc; 
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $e = $tex;                                                                   
                $estado=0; 
                $val = '$data->estado'.$idLc; 
                eval("\$tex = $val;");                       
                if ( $tex != NULL)
                     $estado = $tex;                                                                   
                $u->actRegistro($data->id ,$idLc ,$lista, $texto, $a, $b, $c, $d, $e, $estado);               

           }
           return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'vc/'.$data->id);
           
        }       
        
           $con=' f.tipo=1 and a.id='.$id;
           // Buscar nivel del cargo 
           $da = $d->getGeneral("select c.idNasp from t_lista_cheq a inner join t_lista_cheq_d d on a.id=d.idCheq
                            inner join t_sol_con b on a.idSol=b.id
                            inner join t_cargos c on b.idCar=c.id
                            where d.id=".$id);        
        foreach ($da as $dat){
          $idn = $dat['idNasp'];
        }
        $dato = $d->getGeneral1("Select distinct idCheq from t_lista_cheq_d where id=".$id);
        $valores=array
        (
            "titulo"    =>  "Verificación de los aspectos del cargo",
            "datSol"    =>  $d->getDatSol($dato['idCheq']),
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datos"     =>  $d->getNaspN(' and b.idNasp='.$idn, $id), 
            "ttablas"   =>  'Aspectos, Perfil requerido, Perfil aspirante ,Estado',
            "form"      =>  $form,
            "formc"     =>  $formc,
            "idCheq"    =>  $dato['idCheq'],
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin PERFIL DEL CARGO
    
    // Administracion de la contratacion
    public function listadAction()  
    {        
        $form  = new Formulario("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");        
        //----
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter);          
        
        $form->get("tipo")->setValueOptions(array("1"=>"Contratado","2"=>"No contratado"));
        //
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        $con=' f.tipo=2 and a.id='.$id;
        // Insertar registros que no estan en la lista
        $con2 = 'insert into t_lista_cheq_d (idCheq, idEtaI, etapa ) (
select a.id, g.id, f.tipo 
       from t_lista_cheq a inner join t_sol_con b
       on a.idSol=b.id inner join t_cargos c on b.idCar=c.id
       inner join t_nivel_cargo d on d.id=c.idNcar
       inner join t_nivel_cargo_o e on e.idNcar=d.id
       inner join t_etapas_con f on e.idEtapa=f.id
       inner join t_etapas_con_i g on g.idEtacon=f.id 
       WHERE not exists (SELECT null from t_lista_cheq_d where a.idHoj=idHoj and a.id=idCheq and g.id=idEtaI and f.tipo=etapa ) and '.$con.'
       )' ;     
       $d->modGeneral($con2);        
       // Estado del documento
       $datos = $d->getGeneral1("Select a.idSol,  a.estado, a.contratado, b.idCar, count(c.CedEmp) as emp
                                  from t_lista_cheq a
                                  inner join t_sol_con b on b.id=a.idSol 
                                  left join a_empleados c on c.IdHoj=a.idHoj 
                                  where a.id=".$id);       

       $form->get("estado")->setAttribute("value",$datos['estado']); 
       $form->get("tipo")->setAttribute("value",$datos['contratado']); 
       $contratado = $datos['contratado'];
       $empCreado = $datos['emp'];        
       $idSol = $datos['idSol'];
       $idCar = $datos['idCar'];
       // Saber si esta contratado 
       $datCon = $d->getGeneral1("select a.vacantes, count(b.contratado) as contra, a.salario, c.id, c.tipo, c.meses  
                                 from t_sol_con a 
                                 inner join t_lista_cheq b on b.idSol = a.id 
                                 inner join a_tipcon c on c.id = a.idTcon 
                                 where b.contratado>0 and a.id=".$idSol);                            
       $finContra = 0;    
       $salario   = $datCon['salario']; // Si el salario esta por empleado
       $tipCon    = $datCon['tipo']; // 0 = Indefinido, 1 = a un año , 3 = Definir meses de trabajo
       $meses     = $datCon['meses']; // Meses fijos amarrador al tipo de contrato
       $idTcon    = $datCon['id']; // id tipo de contrato

       if ($datCon['vacantes']==$datCon['contra'])
           $finContra = 1;      

       $val=array
        (
            "1"  => 'Continua en el proceso',            
            "3"  => 'Terminar proceso'
        );      
       $form->get("estado")->setValueOptions($val);              
        
       // Salario
       $arreglo='';
       $datos = $d->getEsalCargo(' and a.idCar='.$idCar); 
       foreach ($datos as $dat){
          $idc=$dat['id'];$nom=number_format($dat['salario']);
          $arreglo[$idc]= $nom;
       }     
       if (!empty($datos))
          $form->get("idEsal")->setValueOptions($arreglo);                                                        
      // Tipo de contrato
      $arreglo='';
      $datos = $d->getTipcont(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("tipo2")->setValueOptions($arreglo);                                                                                     
       
       // Fondos prestacionales ---------------
       // Salud
       $arreglo='';
       $datos = $d->getFondos('1 and estado=0'); 
       foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
       }              
       $form->get("idSal")->setValueOptions($arreglo);                                                 

       // Pension
       $arreglo='';
       $datos = $d->getFondos('2 and estado=0'); 
       foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
       }              
       $form->get("idPen")->setValueOptions($arreglo);                                                       
       // Arp
       $arreglo='';
      $datos = $d->getFondos('3 and estado=0'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idArp")->setValueOptions($arreglo);                                                             
      // Cesntias
      $arreglo='';
      $datos = $d->getFondos('4 and estado=0'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCes")->setValueOptions($arreglo);                                                             
      // Caja de compensacion
      $arreglo= '';
      $datos = $d->getFondos('5 and estado=0'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCaja")->setValueOptions($arreglo);                                                                   
      // Fondos aportes voluntarios
      $arreglo= '';
      $arreglo[1]='No aplica';
      $datos = $d->getFondos('2 and estado=0'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idFav")->setValueOptions($arreglo);                                                                         
      // Fondos aportes AFC
      $arreglo= '';
      $arreglo[1]='No aplica';
      $datos = $d->getFondos('2 and estado=0'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idFafc")->setValueOptions($arreglo);                                                            
      // Fin datos de fondos prestacionales
      // Centro de costos
      $arreglo='';
      $datos = $d->getCencos(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCencos")->setValueOptions($arreglo);                                      
      // Tipo de empleado
      $arreglo='';
      $datos = $d->getTemp(''); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }        
      $form->get("idTemp")->setValueOptions($arreglo);      
      // Grupo de nomina
      $arreglo='';
      $datos = $d->getGrupo2(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }          
      if ( $arreglo != '' )     
           $form->get("idGrupo")->setValueOptions($arreglo);                               
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
      // 
      $form->get("id2")->setAttribute("value",$tipCon);
      if ( $meses > 0 ) // Si el tipo de contratacion esta definido los meses del tipo de contratacion 
         $form->get("ano")->setAttribute("value",$meses);
      if ( $salario > 0)
         $form->get("id3")->setAttribute("value",0); // Oculto para validar si maneja escala salarial o no
      else
         $form->get("id3")->setAttribute("value",1); // Oculto para validar si maneja escala salarial o no        

        $valores=array
        (
            "titulo"    =>  "Administración de la contratación",
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datos"     =>  $d->getEtcehq($con), // extrae las etapas de contratacion de un determinado cargo
            "datSol"    =>  $d->getDatSol($id),
            "datAdj"    =>  $d->getAdjCheq($id),
            "contratado"=>  $contratado , // Empleado contratado
            "empCreado" =>  $empCreado, // Empleado creado en amestro de empleados
            "datGen"    =>  $d->getConfiguraG(" where id=1"), // Obtener datos de configuracion general                 
            "ttablas"   =>  'Detalle, Descripción, Estado, Adjunto ',
            "form"      =>  $form,
            "idSol"     =>  $idSol,
            "id"        =>  $id,
            "finConta"  =>  $finContra, // Si fue contratad o no
            "salario"   =>  $salario, // Salrio fijo, si es 0 es por escala salarial
            "tipCon"    =>  $tipCon,// 0 = Indefinido, 1 = a un año , 3 = Definir meses de trabajo
            "idTcon"    =>  $idTcon, // id tipo de contrato
            "meses"     =>  $meses,// Meses fijos amarrador al tipo de contrato
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin administracion de la contratacion     
    
    // Datos empleados
    public function listaeAction()  
    {        
        $form  = new Formulario("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");        
        //----
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter);  

        $val=array
        (
            "0"  => 'Revisión',
            "1"  => 'Continua en el proceso',
            "2"  => 'No sigue en el proceso'
        );
      
       $form->get("estado")->setValueOptions($val);
       $form->get("tipo")->setValueOptions(array("1"=>"Contratado","2"=>"No contratado"));
       //
       $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
       $d = New AlbumTable($this->dbAdapter); 
       $con=' f.tipo=2 and a.id='.$id;
       // Insertar registros que no estan en la lista
       $con2 = 'insert into t_lista_cheq_d (idHoj,  idCheq, idEtaI, etapa ) (
select a.idHoj,a.id, g.id, f.tipo 
       from t_lista_cheq a inner join t_sol_con b
       on a.idSol=b.id inner join t_cargos c on b.idCar=c.id
       inner join t_nivel_cargo d on d.id=c.idNcar
       inner join t_nivel_cargo_o e on e.idNcar=d.id
       inner join t_etapas_con f on e.idEtapa=f.id
       inner join t_etapas_con_i g on g.idEtacon=f.id 
       WHERE not exists (SELECT null from t_lista_cheq_d where a.idHoj=idHoj and a.id=idCheq and g.id=idEtaI and f.tipo=etapa ) and '.$con.'
       )' ;     
       $d->modGeneral($con2);        
       // Estado del documento
       $datos = $d->getGeneral1("Select idSol, estado, contratado from t_lista_cheq where id=".$id);
       $form->get("estado")->setAttribute("value",$datos['estado']); 
       $form->get("tipo")->setAttribute("value",$datos['contratado']); 
       $contratado = $datos['contratado'];
       $idSol = $datos['idSol'];
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
      $arreglo= '';
      $datos = $d->getFondos('5'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCaja")->setValueOptions($arreglo);                                                                   
      // Fondos aportes voluntarios
      $arreglo= '';
      $datos = $d->getFondos('2'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idFav")->setValueOptions($arreglo);                                                                         
      // Fondos aportes AFC
      $arreglo= '';
      $datos = $d->getFondos('2'); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idFafc")->setValueOptions($arreglo);                                                            
      // Fin datos de fondos prestacionales
      // Centro de costos
      $arreglo='';
      $datos = $d->getCencos(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idCencos")->setValueOptions($arreglo);                                      
      // Tipo de empleado
      $arreglo='';
      $datos = $d->getTemp(''); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }        
      $form->get("idTemp")->setValueOptions($arreglo);      
      // Grupo de nomina
      $arreglo='';
      $datos = $d->getGrupo(); 
      foreach ($datos as $dat){
         $idc=$dat['id'];$nom=$dat['nombre'];
         $arreglo[$idc]= $nom;
      }              
      $form->get("idGrupo")->setValueOptions($arreglo);                               
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
      // 
        $valores=array
        (
            "titulo"    =>  "Administración de la contratación",
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datos"     =>  $d->getEtcehq($con), // extrae las etapas de contratacion de un determinado cargo
            "contratado"=>  $contratado ,
            "ttablas"   =>  'Detalle, Descripción, Estado, Adjunto, Acción',
            "form"      =>  $form,
            "idSol"     =>  $idSol,
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin llenado de datos empleado
    
   // Cambiar estados de la administracion
   public function listadgAction()
   {        
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);  
      
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();

        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Inicon($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        // Actualizar estado del documento
        $u=new IniconA($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $u->actRegistro($data);     
        $this->flashMessenger()->addMessage('');
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'ad/'.$data->id);
      }     
    } // Fin adminsitracion
}
