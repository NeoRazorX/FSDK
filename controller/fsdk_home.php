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
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'FSDK', 'admin');
   }
   
   protected function private_core()
   {
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
               }
               else
               {
                  $this->new_error_msg('Instala el plugin facturacion_base.');
               }
               break;
         }
      }
      
      $this->tablas = $this->db->list_tables();
   }
}
