<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\Cargos; // (C)
use Talentohumano\Model\Entity\CargosS; // Escalara salariar del cargo
use Talentohumano\Model\Entity\CargosA; // Aspectos en cargos

use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos

class CargosController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/cargos/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Cargos de la compañia"; // Titulo listado
    private $tfor = "Actualización cargo"; // Titulo formulario
    private $ttab = "Id, Codigo, Cargos,Denominación,Aspectos,Pdf,Editar,Eliminar"; // Titulo de las columnas de la tabla
        
    // Listado de registros ********************************************************************************************
    public function listAction()
    {
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new Cargos($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)
        $d = New AlbumTable($this->dbAdapter);  
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
      // Lista de cargos
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $d = New AlbumTable($this->dbAdapter);      
      $arreglo[0]='( NO TIENE )';
      $datos = $d->getCargos();// Listado de cargos
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }      
      $form->get("idCar")->setValueOptions($arreglo);  
      $arreglo = '';
      $datos = $d->getNcargos();
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }      
      $form->get("idNcar")->setValueOptions($arreglo);  
      $arreglo = '';
      $datos = $d->getCencos();// Listado de centros de costos
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }     
      $form->get("idDep")->setValueOptions($arreglo);        
      $arreglo = '';
      $datos = $d->getSedes();// Listado de sedes
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }     
      $form->get("idSed")->setValueOptions($arreglo);              
      $arreglo = '';
      $datos = $d->getDepar();
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }      
      $form->get("idGdot")->setValueOptions($arreglo);        
      $arreglo = '';
      $datos = $d->getGdot();// Grupo de dotaciones
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }      
      $form->get("idGdot")->setValueOptions($arreglo);        
      $arreglo = '';
      $datos = $d->getNasp();// Nivel de aspecto del cargo
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=$dat['nombre'];
          $arreglo[$idc]= $nom;
      }      
      $form->get("idNasp")->setValueOptions($arreglo);        
      // Tipos de salarios
      $arreglo = '';
      $datos = $d->getSalarios(''); 
      $arreglo='';
      foreach ($datos as $dat){
          $idc=$dat['id'];$nom=number_format($dat['salario']).' (COD: '.$dat['codigo'].')';
          $arreglo[$idc]= $nom;
      }           
      $form->get("idTnomm")->setValueOptions($arreglo);                   
      // Fin valor de formularios
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
            $form->setValidationGroup('nombre','numero'); // ------------------------------------- 2 CAMPOS A VALDIAR DEL FORMULARIO  (C)            
            // Fin validacion de formulario ---------------------------
            if ($form->isValid()) {
                $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
                $u    = new Cargos($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                if ($data->id==0)
                   $id = $u->actRegistro($data); // Trae el ultimo id de insercion en nuevo registro              
                else 
                {
                   $u->actRegistro($data);             
                   $id = $data->id;
                }
                // Guardar tipos de nominas afectado por automaticos
                $f = new CargosS($this->dbAdapter);
                // Eliminar registros de tipos de nomina afectados por automaticos  
                $d->modGeneral("Delete from t_cargos_sa where idCar=".$id);                 
                $i=0;
                foreach ($data->idTnomm as $dato){
                  $idSal = $data->idTnomm[$i];$i++;           
                  $f->actRegistro($idSal,$id);                
                }                
                $this->flashMessenger()->addMessage('');                
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'a/'.$data->id);
            }
        }
        return new ViewModel($valores);
        
    }else{              
      if ($id > 0) // Cuando ya hay un registro asociado
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Cargos($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
            $datos = $u->getRegistroId($id);
            $a = $datos['nombre'];
            $b = $datos['deno'];
            $c = $datos['idCar_a'];
            $d = $datos['plazas'];
            $e = $datos['respo'];
            $f = $datos['mision'];
            $g = $datos['idNcar'];

            $i = $datos['idGdot'];
            $j = $datos['idNasp'];
            // Valores guardados
            $form->get("nombre")->setAttribute("value","$a"); 
            $form->get("deno")->setAttribute("value","$b"); 
            $form->get("numero")->setAttribute("value",$d); // Plazas
            $form->get("respo")->setAttribute("value","$e"); 
            $form->get("mision")->setAttribute("value","$f"); 
            $form->get("idSed")->setAttribute("value",$datos['idSed']);                         
            $form->get("idDep")->setAttribute("value",$datos['idCcos']);                         
            // Jefe directo
            $d = New AlbumTable($this->dbAdapter);      
            $datos = $d->getCargos();
            $form->get("idCar")->setAttribute("value","$c"); 
            $form->get("idNcar")->setAttribute("value","$g");       

            $form->get("idGdot")->setAttribute("value","$i");                         
            $form->get("idNasp")->setAttribute("value","$j");        
            // Escalas salariales
            $datos = $d->getSalCargos(' and idCar='.$id);
            $arreglo='';            
            foreach ($datos as $dat){
              $arreglo[]=$dat['idSal'];
            }                
            $form->get("idTnomm")->setValue($arreglo);                       
            return new ViewModel($valores);
         }            
         
      }
   } // Fin actualizar datos 
   
   // Eliminar dato ********************************************************************************************
   public function listdAction() 
   {
      $id = (int) $this->params()->fromRoute('id', 0);
      if ($id > 0)
         {
            $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
            $u=new Cargos($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)         
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
                $u    = new Lcheqi($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $u->actRegistro($data,$id);
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin);
            }
        }
      } 
     // $form->get("id")->setAttribute("value","$id"); 
      $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
      $u = New AlbumTable($this->dbAdapter); // ---------------------------------------------------------- 1 FUNCION DENTRO DEL MODELO (C)
      $con="Select idNasp, nombre from t_cargos where id=".$id;// Consula personalizada
      $resul=$u->getGeneral($con);
      foreach ($resul as $dat){
       $id2 = $dat['idNasp'];
      }
      // ---
      $valores=array
      (
           "titulo"    =>  'Ítems de aspectos del cargo '.$dat['nombre'],
           "datos"     =>  $u->getAsp("where idNasp=$id2"),                       
           "ttablas"   =>  'Aspectos, tipo, Opciones',
           'url'       =>  $this->getRequest()->getBaseUrl(),
           "form"      =>  $form,
           "idCar"     =>  $id,
           "lin"       =>  $this->lin
       );                
       return new ViewModel($valores);        
   } // Fin listar registros items   

   // Guardar opciones de los items de titulos en letras
   public function listoAction() 
   { 
       $form = new Formulario("form");
       //  valores iniciales formulario   (C)
       $id = $this->params()->fromRoute('id', 0);
       
       $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');      
       $d = new AlbumTable($this->dbAdapter);
       $pos    = strpos($id, ".");      
       $idIasp = substr($id, 0 , $pos ); // Id item aspectos  
       $idCar  = substr($id, $pos+1 , 100 ); // id Cargo
       $form->get("id")->setAttribute("value",$idIasp);                            
       $form->get("id2")->setAttribute("value",$idCar);        
       
       if ($id==0)
       { 
           $data = $this->request->getPost();
           $idIasp = $data->id; 
       } 
       $datos = $d->getGeneral("select a.*, b.nombre as nomAsp,c.texto, c.a, c.b, c.c, c.d, c.e, a.tipo  
                                        from t_asp_cargo_i a 
                                        inner join t_asp_cargo b on b.id=a.idAsp 
                                        left join t_cargos_a c on c.idIasp=a.id 
                                        where a.idAsp=".$idIasp);       
       
       // ------------------------ Fin valores del formulario 
       if($this->getRequest()->isPost()) // Actulizar datos
       {
            $request = $this->getRequest();
            if ($request->isPost()) { 
                
                $u    = new CargosA($this->dbAdapter);// ------------------------------------------------- 3 FUNCION DENTRO DEL MODELO (C)  
                $data = $this->request->getPost();
                $d->modGeneral("Delete from t_cargos_a where idAsp=".$data->id);
                //print_r($data);
                foreach ($datos as $dato){
                  $idLc = $dato['id'];
                  $nombre = '';
                  if ($dato['tipo'] == 1)// Texto                      
                  {
                     $texto = '$data->text'.$idLc;
                     eval("\$nombre = $texto;");                                                       
                  }           
                  $a = 0;$b = 0;$c = 0;$d = 0;$e = 0;
                  if ($dato['tipo'] == 2)// Lista de opciones                      
                  {
                     $texto = '$data->comenNa'.$idLc;
                     eval("\$a = $texto;");                                 
                     $texto = '$data->comenNb'.$idLc;
                     eval("\$b = $texto;");                                                     
                     $texto = '$data->comenNc'.$idLc;
                     eval("\$c = $texto;");                                                                         
                     $texto = '$data->comenNd'.$idLc;
                     eval("\$d = $texto;");                                                                                             
                     $texto = '$data->comenNe'.$idLc;
                     eval("\$e = $texto;");                                                                                                             
                  }
                  $u->actRegistro( $data->id2, $data->id, $dato['id'],$nombre,  $a, $b, $c, $d, $e);
                }
                $this->flashMessenger()->addMessage('');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'o/'.$data->id.".".$data->id2);
            }                
        }else{              
            if ($id > 0) // Cuando ya hay un registro asociado
            {
               $u=new CargosA($this->dbAdapter); // ---------------------------------------------------------- 4 FUNCION DENTRO DEL MODELO (C)          
               //$datos = $u->getRegistroId($id);
            }            
        }               
        $valores=array
        (
           "titulo"  => $this->tfor,
           "form"    => $form,
           "datos"   => $datos, 
           'url'     => $this->getRequest()->getBaseUrl(),
           'id'      => $id,
           'idCar'   => $idCar,
           "ttablas"   =>  'Aspecto, Item, Opciones',
           "lin"     => $this->lin
        );       
        return new ViewModel($valores);                
   } // Fin actualizar datos    
   
   
}
