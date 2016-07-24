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
               
            case 'servicioscli':
               if( class_exists('servicio_cliente') )
               {
                  $num = $gdp->servicioscli();
                  $this->new_message('Generados '.$num.' '.FS_SERVICIOS.' .');
                  $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                  
                  $this->url_recarga = $this->url().'&gdp=servicioscli';
               }
               else
               {
                  $this->new_error_msg('Instala el plugin servicios.');
               }
               break;
         }
      }
      else if( isset($_POST['generarplugin']) )
      {
      	if( !is_writable("plugins") )
         {
            $this->new_message('No tienes permisos de escritura en la carpeta plugins.');
         }
      	else if( $this->generar_plugin($_POST['nombre'], $_POST['descripcion']) )
      	{
            $this->new_message("Se generó el plugin ".$_POST['nombre']." en el directorio plugins."
                    . " Puedes activarlo desde el <a href='index.php?page=admin_home#plugins'>panel de control</a>.");
      	}
      	else
      	{
            $this->new_error_msg("Hubo un problema al generar el plugin ".$_POST['nombre'].": Revise los logs de su servidor web.");
      	}
      }
      
      $this->tablas = $this->db->list_tables();
   }
   
   private function generar_plugin($nombre, $descripcion)
   {
      if( !$this->crea_estructura($nombre) )
      {
         return FALSE;
      }
      else if( !$this->genera_ficheros($nombre, $descripcion) )
      {
         return FALSE;
      }
      else
         return TRUE;
   }
   
   private function crea_estructura($nombre)
   {
      $ok = FALSE;
      
      // creamos el dir del plugin
   	if( mkdir("plugins/".$nombre) )
   	{
         $ok = TRUE;
         
         // creamos los directorios
   		$dirs = array("controller", "view", "model");
   		foreach($dirs as $dir)
   		{
            if( !mkdir("plugins/".$nombre."/".$dir) )
            {
               $ok = FALSE;
            }
   		}
   	}
      
      return $ok;
   }
   
   private function genera_ficheros($nombre, $descripcion)
   {
      $descripcion .= "\n<br/>Accesible desde Admin &gt; ".$nombre;
      
      if( !file_put_contents("plugins/".$nombre."/description", $descripcion) )
      {
         return FALSE;
      }
      if( !file_put_contents("plugins/".$nombre."/facturascripts.ini", "version = 1") )
      {
         return FALSE;
      }
      else
      {
         $textcontroller = str_replace( "holamundo", $nombre, file_get_contents(__DIR__.'/../tmpls/controller.php') );
         $textvista = file_get_contents(__DIR__.'/../tmpls/view.html');
         
         if( !file_put_contents("plugins/".$nombre."/controller/".$nombre.".php", $textcontroller) )
         {
   			return FALSE;
         }
         else if( !file_put_contents("plugins/".$nombre."/view/".$nombre.".html", $textvista) )
         {
   			return FALSE;
         }
         else
            return TRUE;
      }
   }
}
