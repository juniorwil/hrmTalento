<?php
/** STANDAR MAESTROS NISSI  */
// (C): Cambiar en el controlador 
namespace Talentohumano\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Form\Annotation\AnnotationBuilder;

use Talentohumano\Model\Entity\SelPer; // (C)
use Principal\Form\Formulario;         // Componentes generales de todos los formularios
use Principal\Model\ValFormulario;     // Validaciones de entradas de datos
use Principal\Model\AlbumTable;        // Libreria de datos

class SelperController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    private $lin  = "/talentohumano/selper/list"; // Variable lin de acceso  0 (C)
    private $tlis = "Selección de nuevo personal"; // Titulo listado
    private $tfor = "Inicio del proceso"; // Titulo formulario
    private $ttab = "Documento, Fecha, Fecha Apr., Sede solicitante, Centro de costos, Cargo solicitado, Estado, Aspirantes"; // Titulo de las columnas de la tabla
        
    // Listado de registros ********************************************************************************************
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

    public function listiAction() // Listar aspirantes a cargos  
    {        
        $form = new Formulario("form");
        $id = (int) $this->params()->fromRoute('id', 0);
        $form->get("id")->setAttribute("value","$id");
        
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d = New AlbumTable($this->dbAdapter); 
        // Buscar el id del cargo 
        $datos = $d->getSolcon(" a.estado in ('0','1') and a.id=".$id);

        foreach ($datos as $dat){
           $nom=$dat['idCar'];
           $arreglo[1]= $nom;
        }
        $con=' and b.idCar='.$arreglo[1]." ";
//        $da=$d->getVaca($con);        
        $valores=array
        (
            "titulo"    =>  $this->tfor,
            'url'       =>  $this->getRequest()->getBaseUrl(),
            "datos"     =>  $d->getVaca($con), // Se filtran a los posibles aspirantes al cargo
            "ttablas"   =>  'Aspirante, Pdf ,Cargo, Postular',
            "form"      =>  $form,
            "id"        =>  $id,
            "lin"       =>  $this->lin
        );                
        return new ViewModel($valores);
        
    } // Fin listar registros 
 
   public function listaAction() // Guardar postulados
   { 
      $request = $this->getRequest();       
      //  valores iniciales formulario   (C)
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $data = $this->request->getPost();
        //print_r($data);
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $d=new AlbumTable($this->dbAdapter);
        $u=new SelPer($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 

        // Buscar el id del cargo 
        $datos = $d->getSolconG(" a.estado in ('0','1') and a.id=".$data->id);
        $con=' and b.idCar='.$datos['idCar'];        
        $datos = $d->getVaca($con);        

        // INICIO DE TRANSACCIONES
        $connection = null;
        try {
            $connection = $this->dbAdapter->getDriver()->getConnection();
   	    $connection->beginTransaction();        
          foreach ($datos as $dato){
            $idLc = $dato['id'];
            if ($dato['idHoj']==0) // Nuevo postulado
            {
                $texto = '$data->ch'.$idLc;
                eval("\$post = $texto;");                  
                if ($post>0)
                {
                    $u->actRegistro($data->id, $idLc);  
                    $d->modGeneral("update t_hoja_vida set estado=1 where id=".$idLc);
                }
               
               $this->flashMessenger()->addMessage('');               
            }
          }                
          $connection->commit();
        }// Fin try casth   
        catch (\Exception $e) {
    	    if ($connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface) {
     	        $connection->rollback();
                echo $e;
 	    }	
 	    /* Other error handling */
        }// FIN TRANSACCION                              
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().$this->lin.'i/'.$data->id);
        //$view = new ViewModel();        
        //return $view;              
      }   
   }
   public function listdAction() // Guardar postulados
   { 
      //  valores iniciales formulario   (C)
      $id = (int) $this->params()->fromRoute('id', 0);    
      if($this->getRequest()->isPost()) // Actulizar datos
      {
        $request = $this->getRequest();
        $data = $this->request->getPost();
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        $u=new SelPer($this->dbAdapter);  // ---------------------------------------------------------- 5 FUNCION DENTRO DEL MODELO (C)                 
        $u->delRegistro($data);
      }   
   }   
   //----------------------------------------------------------------------------------------------------------
        
}
