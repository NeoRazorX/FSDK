<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved.
 */

require_once __DIR__.'/../lib/generar_datos_prueba.php';

/**
 * Description of fsdk_home
 *
 * @author carlos
 */
class fsdk_home extends fs_controller
{
   public $tablas;
   public $url_recarga;
   
   /* generación de plugin */
   public $rights_ok = false;
   public $plugin_generado = false;
   public $plugin_error = false;
   public $msgplugin;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'FSDK', 'admin');
   }
   
   protected function private_core()
   {
      $this->url_recarga = FALSE;
      
      if( isset($_GET['gdp']) )
      {
         $gdp = new generar_datos_prueba($this->db, $this->empresa);
         switch($_GET['gdp']) {
            case 'fabricantes':
               if( class_exists('fabricante') )
               {
                  $num = $gdp->fabricantes();
                  $this->new_message('Generados '.$num.' fabricantes.');
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
            
            case 'familias':
               if( class_exists('familia') )
               {
                  $num = $gdp->familias();
                  $this->new_message('Generados '.$num.' familias.');
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
            
            case 'articulos':
               if( class_exists('articulo') )
               {
                  $num = $gdp->articulos();
                  $this->new_message('Generados '.$num.' artículos.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=articulos';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
            
            case 'clientes':
               if( class_exists('cliente') )
               {
                  $num = $gdp->clientes();
                  $this->new_message('Generados '.$num.' clientes.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=clientes';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
            
            case 'agentes':
               $num = $gdp->agentes();
               $this->new_message('Generados '.$num.' empleados.');
               break;
            
            case 'proveedores':
               if( class_exists('proveedor') )
               {
                  $num = $gdp->proveedores();
                  $this->new_message('Generados '.$num.' proveedores.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=proveedores';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
            
            case 'presupuestoscli':
               if( class_exists('presupuesto_cliente') )
               {
                  $num = $gdp->presupuestoscli();
                  $this->new_message('Generados '.$num.' '.FS_PRESUPUESTOS.' de venta.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=presupuestoscli';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin presupuestos_y_pedidos.');
               }
               break;
            
            case 'pedidosprov':
               if( class_exists('pedido_proveedor') )
               {
                  $num = $gdp->pedidosprov();
                  $this->new_message('Generados '.$num.' '.FS_PEDIDOS.' de compra.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=pedidosprov';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin presupuestos_y_pedidos.');
               }
               break;
            
            case 'pedidoscli':
               if( class_exists('pedido_cliente') )
               {
                  $num = $gdp->pedidoscli();
                  $this->new_message('Generados '.$num.' '.FS_PEDIDOS.' de venta.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=pedidoscli';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin presupuestos_y_pedidos.');
               }
               break;
            
            case 'albaranesprov':
               if( class_exists('albaran_proveedor') )
               {
                  $num = $gdp->albaranesprov();
                  $this->new_message('Generados '.$num.' '.FS_ALBARANES.' de compra.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=albaranesprov';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
            
            case 'albaranescli':
               if( class_exists('albaran_cliente') )
               {
                  $num = $gdp->albaranescli();
                  $this->new_message('Generados '.$num.' '.FS_ALBARANES.' de venta.');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=albaranescli';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
         }
      }
      
      $this->tablas = $this->db->list_tables();
            
      $this->rights_ok = is_writable("plugins");
      
      if( isset($_POST['generarplugin']) )
      {
      	$this->plugin_generado = true;
      	
      	$nombre = "holamundo";
      	if (isset($_POST['nombreplugin']))
      		$nombre = $_POST['nombreplugin'];
      	
      	$desc = "";
      	if (isset($_POST['descripcionplugin']))
      		$desc = $_POST['descripcionplugin'];
      	
      	$modelo = "modelo";
      	if (isset($_POST['nombremodelo']))
      		$modelo = $_POST['nombremodelo'];
      		
      	if($this->_generarPlugin($nombre, $desc, $modelo))
      	{      		
      		$this->plugin_error = false;
      		$this->msgplugin = "Se generó el plugin ".$nombre. " en el directorio plugins ";
      	}
      	else
      	{
      		$this->plugin_error = true;
      		$this->msgplugin = "Hubo un problema al generar el plugin ".$nombre. ": Revise los logs de su servidor web.";
      	}
      		
      }
      
   }
   
   private function _generarPlugin($nombre, $descripcion, $modelo)
   {
       if ($this->rights_ok)
       {
       		if (!$this->_creaEstructura($nombre))
       			return false;
       		if (!$this->_generaficheros($nombre, $descripcion, $modelo))
       			return false;
       }
       else
       	return false;
       
       return true;
   }
   
   private function _creaEstructura($nombre)
   {
   		// creamos el dir del plugin
   		if (mkdir("plugins/".$nombre))
   		{
   			// creamos los directorios
   			$dirs = array("controller", "view", "model");
   			foreach ($dirs as $dir)
   			{
   				if (!mkdir("plugins/".$nombre."/".$dir))
   					return false;
   			}
   		}
   		else
   			return false;

   		return true;
   }
   
   private function _generaficheros($nombre, $descripcion, $modelo)
   {
   		if (!file_put_contents("plugins/".$nombre."/description", $descripcion))
   			return false;
   		if (!file_put_contents("plugins/".$nombre."/facturascripts.ini", 
   								"version = 1\r\nversion_url = ''\r\nupdate_url = ''\r\n"))
   			return false;
   		/* CONTROLLER */
   		$textcontroller = str_replace("holamundo", $nombre, file_get_contents (__DIR__.'/../tmpls/controller.php'));
   		if (!file_put_contents("plugins/".$nombre."/controller/".$nombre.".php", $textcontroller))
   			return false;
   		/* VISTA */
   		$textvista = file_get_contents (__DIR__.'/../tmpls/view.html');
   		if (!file_put_contents("plugins/".$nombre."/view/".$nombre.".html", $textvista))
   			return false;
   		/* MODELO */
   		$textmodelo = str_replace("holamundo", $nombre, file_get_contents (__DIR__.'/../tmpls/model.php'));
   		if (!file_put_contents("plugins/".$nombre."/model/".$modelo.".php", $textmodelo))
   			return false;
   		
   		return true;
   }
}
