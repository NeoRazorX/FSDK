<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved.
 */

require_model('albaran_cliente.php');
require_model('albaran_proveedor.php');
require_model('agente.php');
require_model('almacen.php');
require_model('articulo.php');
require_model('cliente.php');
require_model('cuenta_banco_cliente.php');
require_model('cuenta_banco_proveedor.php');
require_model('divisa.php');
require_model('ejercicio.php');
require_model('familia.php');
require_model('fabricante.php');
require_model('forma_pago.php');
require_model('impuesto.php');
require_model('pais.php');
require_model('pedido_cliente.php');
require_model('pedido_proveedor.php');
require_model('presupuesto_cliente.php');
require_model('proveedor.php');
require_model('serie.php');

/**
 * Description of generar_datos_prueba
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class generar_datos_prueba
{
   private $agentes;
   private $almacenes;
   private $db;
   private $divisas;
   private $ejercicio;
   private $empresa;
   private $formas_pago;
   private $impuestos;
   private $paises;
   private $series;
   
   /**
    * Constructor. Inicializamos todo lo necesario.
    * @param fs_db2 $db
    * @param empresa $empresa
    */
   public function __construct(&$db, &$empresa)
   {
      $this->db = $db;
      $this->empresa = $empresa;
      
      $this->agentes = $this->random_agentes();
      
      $almacen = new almacen();
      $this->almacenes = $almacen->all();
      shuffle($this->almacenes);
      
      $divisa = new divisa();
      $this->divisas = $divisa->all();
      shuffle($this->divisas);
      
      $this->ejercicio = new ejercicio();
      
      $fp = new forma_pago();
      $this->formas_pago = $fp->all();
      shuffle($this->formas_pago);
      
      if( class_exists('impuesto') )
      {
         $imp = new impuesto();
         $this->impuestos = $imp->all();
         shuffle($this->impuestos);
      }
      else
      {
         $this->impuestos = array();
      }
      
      $pais = new pais();
      $this->paises = $pais->all();
      shuffle($this->paises);
      
      $serie = new serie();
      $this->series = $serie->all();
      shuffle($this->series);
   }
   
   public function fabricantes()
   {
      $num = 0;
      
      while($num < 50)
      {
         $fabri = new fabricante();
         $fabri->nombre = $this->empresa();
         $fabri->codfabricante = $this->txt2codigo($fabri->nombre);
         
         if( $fabri->save() )
         {
            $num++;
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function familias()
   {
      $num = 0;
      $codfamilia = NULL;
      
      while($num < 50)
      {
         $fam = new familia();
         $fam->descripcion = $this->empresa();
         $fam->codfamilia = $this->txt2codigo($fam->descripcion);
         
         if( mt_rand(0, 4) == 0 )
         {
            $fam->madre = $codfamilia;
         }
         
         if( $fam->save() )
         {
            $codfamilia = $fam->codfamilia;
            $num++;
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function txt2codigo($txt, $len = 8)
   {
      $txt = str_replace( array(' ','-','_','&','ó',':'), array('','','','','O',''), strtoupper($txt));
      
      if( strlen($txt) < $len )
      {
         return $txt;
      }
      else
      {
         return substr($txt, 0, $len);
      }
   }
   
   public function articulos()
   {
      $num = 0;
      
      $fab = new fabricante();
      $fabricantes = $fab->all();
      
      $fam = new familia();
      $familias = $fam->all();
      
      while($num < 50)
      {
         if( mt_rand(0, 2) == 0 )
         {
            shuffle($fabricantes);
            shuffle($familias);
            shuffle($this->impuestos);
         }
         
         $art = new articulo();
         $art->codfabricante = $fabricantes[0]->codfabricante;
         $art->codfamilia = $familias[0]->codfamilia;
         $art->descripcion = $this->descripcion();
         $art->codimpuesto = $this->impuestos[0]->codimpuesto;
         $art->set_pvp_iva( mt_rand(1, 200)*0.3 );
         $art->stockmin = mt_rand(0, 10);
         $art->stockmax = mt_rand($art->stockmin+1, 1000);
         
         $opcion = mt_rand(0, 2);
         if($opcion == 0)
         {
            $art->referencia = $art->get_new_referencia();
         }
         else if($opcion == 1)
         {
            $aux = explode(':', $art->descripcion);
            if($aux)
            {
               $art->referencia = $this->txt2codigo($aux[0], 18);
            }
            else
            {
               $art->referencia = $art->get_new_referencia();
            }
         }
         else
         {
            $art->referencia = $this->random_string(10);
         }
         
         if( mt_rand(0, 3) == 0 )
         {
            $art->publico = TRUE;
         }
         
         if( mt_rand(0, 9) == 0 )
         {
            $art->bloqueado = TRUE;
         }
         
         if( mt_rand(0, 9) == 0 )
         {
            $art->nostock = TRUE;
         }
         
         if( mt_rand(0, 9) == 0 )
         {
            $art->secompra = FALSE;
         }
         
         if( mt_rand(0, 9) == 0 )
         {
            $art->sevende = FALSE;
         }
         
         if( $art->save() )
         {
            $num++;
            
            shuffle($this->almacenes);
            if( mt_rand(0, 2) == 0 )
            {
               $art->sum_stock( $this->almacenes[0]->codalmacen, mt_rand(0, 1000) );
            }
            else
            {
               $art->sum_stock( $this->almacenes[0]->codalmacen, mt_rand(0, 20) );
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   private function descripcion()
   {
      $prefijos = array(
          'Jet', 'Jex', 'Max', 'Pro', 'FX', 'Neo', 'Maxi', 'Extreme', 'Sub',
          'Ultra', 'Minga', 'Hiper', 'Giga', 'Mega', 'Super', 'Fusion'
      );
      shuffle($prefijos);
      
      $nombres = array(
          'Motor', 'Engine', 'Generator', 'Tool', 'Oviode', 'Box', 'Proton', 'Neutro',
          'Radeon', 'GeForce', 'nForce', 'Labtech', 'Station'
      );
      shuffle($nombres);
      
      $sufijos = array(
          'II', '3', 'XL', 'XXL', 'SE', 'GT', 'GTX', 'Pro', 'NX', 'XP', 'OS', 'Nitro'
      );
      shuffle($sufijos);
      
      $descripciones1 = array(
          'Una alcachofa', 'Un motor', 'Una targeta gráfica (GPU)', 'Un procesador',
          'Un coche', 'Un dispositivo tecnológico', 'Un magnetofón', 'Un palo', 'un cubo de basura'
      );
      shuffle($descripciones1);
      
      $descripciones2 = array(
          '64 núcleos', 'chasis de fibra de carbono', '8 cilindros en V', 'frenos de berilio',
          '16 ejes', 'pantalla Super AMOLED', '1024 stream processors', 'un núcleo híbrido',
          '32 pistones digitales', 'tecnología digitrónica 4.1', 'cuernos metálicos', 'un palo',
          'memoria HBM', 'taladro matricial', 'Wifi 4G', 'faros de xenon', 'un ambientador de pino',
          'un posavasos', 'malignas intenciones', 'la virginidad intacta', 'malware', 'linux',
          'Windows Vista', 'propiedades psicotrópicas', 'spyware', 'reproductor 4k'
      );
      shuffle($descripciones2);
      
      $texto = $prefijos[0].' '.$nombres[0].' '.$sufijos[0];
      
      switch( mt_rand(0, 4) )
      {
         case 0:
            break;
         
         case 1:
            $texto .= ': '.$descripciones1[0].' con '.$descripciones2[0];
            break;
         
         case 2:
            $texto .= ': '.$descripciones1[0].' con '.$descripciones2[0].', '.$descripciones2[1].', '.$descripciones2[2].' y '.$descripciones2[3].'.';
            break;
         
         case 3:
            $texto .= ': '.$descripciones1[0]."\n- ".$descripciones2[0]."\n- ".$descripciones2[1]."\n- ".$descripciones2[2]."\n- ".$descripciones2[3].'.';
            break;
         
         default:
            $texto .= ': '.$descripciones1[0].' con '.$descripciones2[0].', '.$descripciones2[1].' y '.$descripciones2[2].'.';
            break;
      }
      
      return $texto;
   }
   
   public function agentes()
   {
      $num = 0;
      
      while($num < 50)
      {
         $agente = new agente();
         $agente->f_nacimiento = date( mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(1970, 1997) );
         $agente->f_alta = date( mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016) );
         
         if( mt_rand(0, 24) == 0 )
         {
            $agente->f_baja = date('d-m-Y');
         }
         
         $agente->dnicif = mt_rand(0, 99999999);
         if( mt_rand(0, 14) == 0 )
         {
            $agente->dnicif = '';
         }
         
         $agente->nombre = $this->nombre();
         $agente->apellidos = $this->apellidos();
         $agente->provincia = $this->provincia();
         $agente->ciudad = $this->ciudad();
         $agente->direccion = $this->direccion();
         
         if( mt_rand(0, 1) == 0 )
         {
            $agente->telefono = mt_rand(555555555, 999999999);
         }
         
         if( mt_rand(0, 2) > 0 )
         {
            $agente->email = $this->email();
         }
         
         $agente->codagente = $agente->get_new_codigo();
         if( $agente->save() )
         {
            $num++;
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function clientes()
   {
      $num = 0;
      
      while($num < 50)
      {
         $cliente = new cliente();
         $cliente->fechaalta = date( mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016) );
         
         if( mt_rand(0, 24) == 0 )
         {
            $cliente->debaja = TRUE;
            $cliente->fechabaja = date('d-m-Y');
         }
         
         $cliente->cifnif = mt_rand(0, 99999999);
         if( mt_rand(0, 14) == 0 )
         {
            $cliente->cifnif = '';
         }
         
         $opcion = mt_rand(0, 2);
         $cliente->nombre = $cliente->razonsocial = $this->nombre().' '.$this->apellidos();
         if($opcion == 0)
         {
            $cliente->nombre = $cliente->razonsocial = $this->empresa();
            $cliente->personafisica = FALSE;
         }
         else if($opcion == 1)
         {
            $cliente->razonsocial = $this->empresa();
            $cliente->personafisica = FALSE;
         }
         
         $opcion = mt_rand(0, 2);
         if($opcion == 0)
         {
            $cliente->telefono1 = mt_rand(555555555, 999999999);
         }
         else if($opcion == 1)
         {
            $cliente->telefono1 = mt_rand(555555555, 999999999);
            $cliente->telefono2 = mt_rand(555555555, 999999999);
         }
         else
         {
            $cliente->telefono2 = mt_rand(555555555, 999999999);
         }
         
         if( mt_rand(0, 2) > 0 )
         {
            $cliente->email = $this->email();
         }
         
         if( mt_rand(0, 3) == 0 )
         {
            $cliente->regimeniva = 'Exento';
         }
         
         $cliente->codcliente = $cliente->get_new_codigo();
         if( $cliente->save() )
         {
            $num++;
            
            /// añadimos direcciones
            $num_dirs = mt_rand(0, 3);
            while($num_dirs > 0)
            {
               $dir = new direccion_cliente();
               $dir->codcliente = $cliente->codcliente;
               $dir->codpais = $this->empresa->codpais;
               
               if( mt_rand(0, 2) == 0 )
               {
                  $dir->codpais = $this->paises[0]->codpais;
               }
               
               $dir->provincia = $this->provincia();
               $dir->ciudad = $this->ciudad();
               $dir->direccion = $this->direccion();
               $dir->codpostal = mt_rand(1234, 99999);
               $dir->save();
               $num_dirs--;
            }
            
            /// Añadimos cuentas bancarias
            $num_cuentas = mt_rand(0, 3);
            while($num_cuentas > 0)
            {
               $cuenta = new cuenta_banco_cliente();
               $cuenta->codcliente = $cliente->codcliente;
               $cuenta->descripcion = 'Banco '.mt_rand(1, 999);
               $cuenta->iban = 'ES'.mt_rand(10, 99).' '.mt_rand(1000, 9999).' '.mt_rand(1000, 9999).' '
                       .mt_rand(1000, 9999).' '.mt_rand(1000, 9999).' '.mt_rand(1000, 9999);
               $cuenta->swift = $this->random_string(8);
               
               $opcion = mt_rand(0, 2);
               if($opcion == 0)
               {
                  $cuenta->swift = '';
               }
               else if($opcion == 1)
               {
                  $cuenta->iban = '';
               }
               
               if( mt_rand(0, 1) == 0 )
               {
                  $cuenta->fmandato = $cliente->fechaalta;
               }
               
               $cuenta->save();
               $num_cuentas--;
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function proveedores()
   {
      $num = 0;
      
      while($num < 50)
      {
         $proveedor = new proveedor();
         $proveedor->cifnif = mt_rand(0, 99999999);
         
         $opcion = mt_rand(0, 4);
         $proveedor->nombre = $proveedor->razonsocial = $this->empresa();
         $proveedor->personafisica = FALSE;
         if($opcion == 0)
         {
            $proveedor->nombre = $this->nombre().' '.$this->apellidos();
            $proveedor->personafisica = TRUE;
         }
         else if($opcion == 1)
         {
            $proveedor->nombre = $proveedor->razonsocial = $this->empresa();
            $proveedor->acreedor = TRUE;
         }
         
         $opcion = mt_rand(0, 2);
         if($opcion == 0)
         {
            $proveedor->telefono1 = mt_rand(555555555, 999999999);
         }
         else if($opcion == 1)
         {
            $proveedor->telefono1 = mt_rand(555555555, 999999999);
            $proveedor->telefono2 = mt_rand(555555555, 999999999);
         }
         else
         {
            $proveedor->telefono2 = mt_rand(555555555, 999999999);
         }
         
         if( mt_rand(0, 2) > 0 )
         {
            $proveedor->email = $this->email();
         }
         
         if( mt_rand(0, 3) == 0 )
         {
            $proveedor->regimeniva = 'Exento';
         }
         
         $proveedor->codproveedor = $proveedor->get_new_codigo();
         if( $proveedor->save() )
         {
            $num++;
            
            /// añadimos direcciones
            $num_dirs = mt_rand(0, 3);
            while($num_dirs)
            {
               $dir = new direccion_proveedor();
               $dir->codproveedor = $proveedor->codproveedor;
               $dir->codpais = $this->empresa->codpais;
               
               if( mt_rand(0, 2) == 0 )
               {
                  $dir->codpais = $this->paises[0]->codpais;
               }
               
               $dir->provincia = $this->provincia();
               $dir->ciudad = $this->ciudad();
               $dir->direccion = $this->direccion();
               $dir->codpostal = mt_rand(1234, 99999);
               $dir->save();
               $num_dirs--;
            }
            
            /// Añadimos cuentas bancarias
            $num_cuentas = mt_rand(0, 3);
            while($num_cuentas > 0)
            {
               $cuenta = new cuenta_banco_proveedor();
               $cuenta->codproveedor = $proveedor->codproveedor;
               $cuenta->descripcion = 'Banco '.mt_rand(1, 999);
               $cuenta->iban = 'ES'.mt_rand(10, 99).' '.mt_rand(1000, 9999).' '.mt_rand(1000, 9999).' '
                       .mt_rand(1000, 9999).' '.mt_rand(1000, 9999).' '.mt_rand(1000, 9999);
               $cuenta->swift = $this->random_string(8);
               
               $opcion = mt_rand(0, 2);
               if($opcion == 0)
               {
                  $cuenta->swift = '';
               }
               else if($opcion == 1)
               {
                  $cuenta->iban = '';
               }
               
               $cuenta->save();
               $num_cuentas--;
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   private function nombre()
   {
      $nombres = array(
          'Carlos', 'Pepe', 'Wilson', 'Petra', 'Madonna', 'Justin',
          'Emiliana', 'Jo', 'Penélope', 'Mia', 'Wynona', 'Antonio',
          'Joe', 'Cristiano', 'Mohamed', 'John', 'Ali', 'Pastor',
          'Barak', 'Sadam', 'Donald', 'Jorge', 'Joel', 'Pedro', 'Mariano',
          'Albert', 'Alberto', 'Gorka', 'Cecilia', 'Carmena', 'Pichita',
          'Alicia', 'Laura', 'Riola', 'Wilson', 'Jaume', 'David'
      );
      
      shuffle($nombres);
      return $nombres[0];
   }
   
   private function apellidos()
   {
      $apellidos = array(
          'García', 'Gómez', 'Ronaldo', 'Suarez', 'Wilson', 'Pacheco',
          'Escobar', 'Mendoza', 'Pérez', 'Cruz', 'Lee', 'Smith', 'Humilde',
          'Hijo de Dios', 'Petrov', 'Maximiliano', 'Nieve', 'Snow', 'Trump',
          'Obama', 'Ali', 'Stark', 'Sanz', 'Rajoy', 'Sánchez', 'Iglesias',
          'Rivera', 'Tumor', 'Lanister', 'Suarez', 'Aznar', 'Botella', 'Errejón'
      );
      
      shuffle($apellidos);
      return $apellidos[0].' '.$apellidos[1];
   }
   
   private function empresa()
   {
      $nombres = array(
          'Tech', 'Motor', 'Pasión', 'Future', 'Max', 'Massive', 'Industrial',
          'Plastic', 'Pro', 'Micro', 'System', 'Light', 'Magic', 'Fake', 'Techno',
          'Miracle', 'NX', 'Smoke', 'Steam', 'Power', 'FX', 'Fusion', 'Bastion',
          'Investments', 'Solutions', 'Neo', 'Ming', 'Tube', 'Pear', 'Apple',
          'Dolphin', 'Chrome', 'Cat', 'Hat', 'Linux', 'Soft', 'Mobile', 'Phone',
          'XL', 'Open', 'Thunder', 'Zero', 'Scorpio', 'Zelda', '10', 'V', 'Q', 'X'
      );
      
      $separador = array(
          '-', ' & ', ' ', '_', '', '/', '*'
      );
      
      $tipo = array(
          'S.L.', 'S.A.', 'Inc.', 'LTD', 'Corp.'
      );
      
      shuffle($nombres);
      shuffle($separador);
      shuffle($tipo);
      return $nombres[0].$separador[0].$nombres[1].' '.$tipo[0];
   }
   
   private function email()
   {
      $nicks = array(
          'neo', 'carlos', 'moko', 'snake', 'pikachu', 'pliskin', 'ocelot', 'samurai',
          'ninja', 'penetrator', 'info', 'compras', 'ventas', 'administracion', 'contacto',
          'contact', 'invoices', 'mail'
      );
      
      shuffle($nicks);
      return $nicks[0].'.'.mt_rand(2, 9999).'@facturascripts.com';
   }
   
   private function provincia()
   {
      $nombres = array(
          'Alicante', 'Valencia', 'Andalucía', 'Madrid', 'Pichita',
          'La meseta', 'Black mesa', 'Antioquía', 'Genérica', 'Medellín'
      );
      
      shuffle($nombres);
      return $nombres[0];
   }
   
   private function ciudad()
   {
      $nombres = array(
          'Alicante', 'Valencia', 'Madrid', 'Elche', 'Torrevieja',
          'Quito', 'Lima', 'Bejar', 'Medellín', 'Totoras', 'Ferrol',
          'Labastida', 'Magaluf'
      );
      
      shuffle($nombres);
      return $nombres[0];
   }
   
   private function direccion()
   {
      $tipos = array(
          'Calle', 'Avenida', 'Polígono', 'Carretera'
      );
      $nombres = array(
          'Infante', 'Principal', 'Falsa', '58', '74',
          'Pacheco', 'Baleares', 'Del Pacífico', 'Rue'
      );
      
      shuffle($tipos);
      shuffle($nombres);
      
      if( mt_rand(0, 2) == 0 )
      {
         return $tipos[0].' '.$nombres[0].', nº'.mt_rand(1, 199).', puerta '.mt_rand(1, 99);
      }
      else
      {
         return $tipos[0].' '.$nombres[0].', '.mt_rand(1, 99);
      }
   }
   
   public function albaranescli()
   {
      $num = 0;
      $clientes = $this->random_clientes();
      
      while($num < 25)
      {
         $alb = new albaran_cliente();
         $alb->fecha = mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016);
         $alb->hora = mt_rand(10, 20).':'.mt_rand(10, 59).':'.mt_rand(10, 59);
         $alb->codalmacen = $this->empresa->codalmacen;
         $alb->coddivisa = $this->empresa->coddivisa;
         $alb->codpago = $this->empresa->codpago;
         $alb->codserie = $this->empresa->codserie;
         
         if( mt_rand(0, 2) == 0 )
         {
            $alb->codagente = $this->agentes[0]->codagente;
            $alb->codalmacen = $this->almacenes[0]->codalmacen;
            $alb->codpago = $this->formas_pago[0]->codpago;
            $alb->coddivisa = $this->divisas[0]->coddivisa;
            $alb->tasaconv = $this->divisas[0]->tasaconv;
            
            if($this->series[0]->codserie != 'R')
            {
               $alb->codserie = $this->series[0]->codserie;
               $alb->irpf = $this->series[0]->irpf;
            }
            
            $alb->observaciones = $this->observaciones($alb->fecha);
            $alb->numero2 = mt_rand(10, 99999);
         }
         
         $eje = $this->ejercicio->get_by_fecha($alb->fecha);
         if($eje)
         {
            $alb->codejercicio = $eje->codejercicio;
            
            $alb->codcliente = $clientes[$num]->codcliente;
            $alb->nombrecliente = $clientes[$num]->razonsocial;
            $alb->cifnif = $clientes[$num]->cifnif;
            foreach($clientes[$num]->get_direcciones() as $dir)
            {
               $alb->codpais = $dir->codpais;
               $alb->provincia = $dir->provincia;
               $alb->ciudad = $dir->ciudad;
               $alb->direccion = $dir->direccion;
               $alb->codpostal = $dir->codpostal;
               
               if($dir->domfacturacion)
               {
                  break;
               }
            }
            
            if( $alb->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = mt_rand(1, 10);
               if( mt_rand(0, 3) == 0 )
               {
                  $numlineas = mt_rand(1, 200);
               }
               
               while($numlineas > 0)
               {
                  $lin = new linea_albaran_cliente();
                  $lin->idalbaran = $alb->idalbaran;
                  
                  $lin->cantidad = mt_rand(1, 3);
                  if( mt_rand(0, 3) == 0 )
                  {
                     $lin->cantidad = mt_rand(1, 19);
                  }
                  
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = round( mt_rand(1, 99)*0.3, FS_NF0_ART );
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( mt_rand(0, 9) == 0 )
                  {
                     $lin->recargo = $this->impuestos[0]->recargo;
                  }
                  
                  if( isset($articulos[$numlineas]) )
                  {
                     if($articulos[$numlineas]->sevende)
                     {
                        $lin->referencia = $articulos[$numlineas]->referencia;
                        $lin->descripcion = $articulos[$numlineas]->descripcion;
                        $lin->pvpunitario = $articulos[$numlineas]->pvp;
                        $lin->codimpuesto = $articulos[$numlineas]->codimpuesto;
                        $lin->iva = $articulos[$numlineas]->get_iva();
                        $lin->recargo = 0;
                     }
                  }
                  
                  $lin->irpf = $alb->irpf;
                  
                  if($clientes[$num]->regimeniva == 'Exento')
                  {
                     $lin->codimpuesto = NULL;
                     $lin->iva = 0;
                     $lin->recargo = 0;
                     $alb->irpf = $lin->irpf = 0;
                  }
                  
                  if( mt_rand(0, 4) == 0 )
                  {
                     $lin->dtopor = mt_rand(0, 99);
                  }
                  
                  $lin->pvpsindto = ($lin->pvpunitario * $lin->cantidad);
                  $lin->pvptotal = $lin->pvpunitario * $lin->cantidad * (100 - $lin->dtopor) / 100;
                  
                  if( $lin->save() )
                  {
                     $alb->neto += $lin->pvptotal;
                     $alb->totaliva += ($lin->pvptotal * $lin->iva/100);
                     $alb->totalirpf += ($lin->pvptotal * $lin->irpf/100);
                     $alb->totalrecargo += ($lin->pvptotal * $lin->recargo/100);
                  }
                  
                  $numlineas--;
               }
               
               /// redondeamos
               $alb->neto = round($alb->neto, FS_NF0);
               $alb->totaliva = round($alb->totaliva, FS_NF0);
               $alb->totalirpf = round($alb->totalirpf, FS_NF0);
               $alb->totalrecargo = round($alb->totalrecargo, FS_NF0);
               $alb->total = $alb->neto + $alb->totaliva - $alb->totalirpf + $alb->totalrecargo;
               $alb->save();
               
               $num++;
            }
            else
            {
               break;
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function albaranesprov()
   {
      $num = 0;
      $proveedores = $this->random_proveedores();
      
      while($num < 25)
      {
         $alb = new albaran_proveedor();
         $alb->fecha = mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016);
         $alb->hora = mt_rand(10, 20).':'.mt_rand(10, 59).':'.mt_rand(10, 59);
         $alb->codalmacen = $this->empresa->codalmacen;
         $alb->coddivisa = $this->empresa->coddivisa;
         $alb->codpago = $this->empresa->codpago;
         $alb->codserie = $this->empresa->codserie;
         
         if( mt_rand(0, 2) == 0 )
         {
            $alb->codagente = $this->agentes[0]->codagente;
            $alb->codalmacen = $this->almacenes[0]->codalmacen;
            $alb->codpago = $this->formas_pago[0]->codpago;
            $alb->coddivisa = $this->divisas[0]->coddivisa;
            $alb->tasaconv = $this->divisas[0]->tasaconv_compra;
            
            if($this->series[0]->codserie != 'R')
            {
               $alb->codserie = $this->series[0]->codserie;
               $alb->irpf = $this->series[0]->irpf;
            }
            
            $alb->observaciones = $this->observaciones($alb->fecha);
            $alb->numproveedor = mt_rand(10, 99999);
         }
         
         $eje = $this->ejercicio->get_by_fecha($alb->fecha);
         if($eje)
         {
            $alb->codejercicio = $eje->codejercicio;
            
            $alb->codproveedor = $proveedores[$num]->codproveedor;
            $alb->nombre = $proveedores[$num]->razonsocial;
            $alb->cifnif = $proveedores[$num]->cifnif;
            
            if( $alb->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = mt_rand(1, 10);
               if( mt_rand(0, 3) == 0 )
               {
                  $numlineas = mt_rand(1, 200);
               }
               
               while($numlineas > 0)
               {
                  $lin = new linea_albaran_proveedor();
                  $lin->idalbaran = $alb->idalbaran;
                  
                  $lin->cantidad = mt_rand(1, 3);
                  if( mt_rand(0, 3) == 0 )
                  {
                     $lin->cantidad = mt_rand(1, 19);
                  }
                  
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = round( mt_rand(1, 99)*0.3, FS_NF0_ART );
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( mt_rand(0, 9) == 0 )
                  {
                     $lin->recargo = $this->impuestos[0]->recargo;
                  }
                  
                  if( isset($articulos[$numlineas]) )
                  {
                     if($articulos[$numlineas]->sevende)
                     {
                        $lin->referencia = $articulos[$numlineas]->referencia;
                        $lin->descripcion = $articulos[$numlineas]->descripcion;
                        $lin->pvpunitario = $articulos[$numlineas]->pvp;
                        $lin->codimpuesto = $articulos[$numlineas]->codimpuesto;
                        $lin->iva = $articulos[$numlineas]->get_iva();
                        $lin->recargo = 0;
                     }
                  }
                  
                  $lin->irpf = $alb->irpf;
                  
                  if($proveedores[$num]->regimeniva == 'Exento')
                  {
                     $lin->codimpuesto = NULL;
                     $lin->iva = 0;
                     $lin->recargo = 0;
                     $alb->irpf = $lin->irpf = 0;
                  }
                  
                  if( mt_rand(0, 4) == 0 )
                  {
                     $lin->dtopor = mt_rand(0, 99);
                  }
                  
                  $lin->pvpsindto = ($lin->pvpunitario * $lin->cantidad);
                  $lin->pvptotal = $lin->pvpunitario * $lin->cantidad * (100 - $lin->dtopor) / 100;
                  
                  if( $lin->save() )
                  {
                     $alb->neto += $lin->pvptotal;
                     $alb->totaliva += ($lin->pvptotal * $lin->iva/100);
                     $alb->totalirpf += ($lin->pvptotal * $lin->irpf/100);
                     $alb->totalrecargo += ($lin->pvptotal * $lin->recargo/100);
                  }
                  
                  $numlineas--;
               }
               
               /// redondeamos
               $alb->neto = round($alb->neto, FS_NF0);
               $alb->totaliva = round($alb->totaliva, FS_NF0);
               $alb->totalirpf = round($alb->totalirpf, FS_NF0);
               $alb->totalrecargo = round($alb->totalrecargo, FS_NF0);
               $alb->total = $alb->neto + $alb->totaliva - $alb->totalirpf + $alb->totalrecargo;
               $alb->save();
               
               $num++;
            }
            else
            {
               break;
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function pedidoscli()
   {
      $num = 0;
      $clientes = $this->random_clientes();
      
      while($num < 25)
      {
         $ped = new pedido_cliente();
         $ped->fecha = mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016);
         $ped->hora = mt_rand(10, 20).':'.mt_rand(10, 59).':'.mt_rand(10, 59);
         $ped->codalmacen = $this->empresa->codalmacen;
         $ped->coddivisa = $this->empresa->coddivisa;
         $ped->codpago = $this->empresa->codpago;
         $ped->codserie = $this->empresa->codserie;
         
         if( mt_rand(0, 2) == 0 )
         {
            $ped->codagente = $this->agentes[0]->codagente;
            $ped->codalmacen = $this->almacenes[0]->codalmacen;
            $ped->codpago = $this->formas_pago[0]->codpago;
            $ped->coddivisa = $this->divisas[0]->coddivisa;
            $ped->tasaconv = $this->divisas[0]->tasaconv;
            
            if($this->series[0]->codserie != 'R')
            {
               $ped->codserie = $this->series[0]->codserie;
               $ped->irpf = $this->series[0]->irpf;
            }
            
            $ped->observaciones = $this->observaciones($ped->fecha);
            $ped->numero2 = mt_rand(10, 99999);
         }
         
         if( mt_rand(0, 5) == 0 )
         {
            $ped->status = 2;
         }
         
         $eje = $this->ejercicio->get_by_fecha($ped->fecha);
         if($eje)
         {
            $ped->codejercicio = $eje->codejercicio;
            
            $ped->codcliente = $clientes[$num]->codcliente;
            $ped->nombrecliente = $clientes[$num]->razonsocial;
            $ped->cifnif = $clientes[$num]->cifnif;
            foreach($clientes[$num]->get_direcciones() as $dir)
            {
               $ped->codpais = $dir->codpais;
               $ped->provincia = $dir->provincia;
               $ped->ciudad = $dir->ciudad;
               $ped->direccion = $dir->direccion;
               $ped->codpostal = $dir->codpostal;
               
               if($dir->domenvio)
               {
                  break;
               }
            }
            
            if( $ped->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = mt_rand(1, 10);
               if( mt_rand(0, 3) == 0 )
               {
                  $numlineas = mt_rand(1, 200);
               }
               
               while($numlineas > 0)
               {
                  $lin = new linea_pedido_cliente();
                  $lin->idpedido = $ped->idpedido;
                  
                  $lin->cantidad = mt_rand(1, 3);
                  if( mt_rand(0, 3) == 0 )
                  {
                     $lin->cantidad = mt_rand(1, 19);
                  }
                  
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = round( mt_rand(1, 99)*0.3, FS_NF0_ART );
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( mt_rand(0, 9) == 0 )
                  {
                     $lin->recargo = $this->impuestos[0]->recargo;
                  }
                  
                  if( isset($articulos[$numlineas]) )
                  {
                     if($articulos[$numlineas]->sevende)
                     {
                        $lin->referencia = $articulos[$numlineas]->referencia;
                        $lin->descripcion = $articulos[$numlineas]->descripcion;
                        $lin->pvpunitario = $articulos[$numlineas]->pvp;
                        $lin->codimpuesto = $articulos[$numlineas]->codimpuesto;
                        $lin->iva = $articulos[$numlineas]->get_iva();
                        $lin->recargo = 0;
                     }
                  }
                  
                  $lin->irpf = $ped->irpf;
                  
                  if($clientes[$num]->regimeniva == 'Exento')
                  {
                     $lin->codimpuesto = NULL;
                     $lin->iva = 0;
                     $lin->recargo = 0;
                     $ped->irpf = $lin->irpf = 0;
                  }
                  
                  if( mt_rand(0, 4) == 0 )
                  {
                     $lin->dtopor = mt_rand(0, 99);
                  }
                  
                  $lin->pvpsindto = ($lin->pvpunitario * $lin->cantidad);
                  $lin->pvptotal = $lin->pvpunitario * $lin->cantidad * (100 - $lin->dtopor) / 100;
                  
                  if( $lin->save() )
                  {
                     $ped->neto += $lin->pvptotal;
                     $ped->totaliva += ($lin->pvptotal * $lin->iva/100);
                     $ped->totalirpf += ($lin->pvptotal * $lin->irpf/100);
                     $ped->totalrecargo += ($lin->pvptotal * $lin->recargo/100);
                  }
                  
                  $numlineas--;
               }
               
               /// redondeamos
               $ped->neto = round($ped->neto, FS_NF0);
               $ped->totaliva = round($ped->totaliva, FS_NF0);
               $ped->totalirpf = round($ped->totalirpf, FS_NF0);
               $ped->totalrecargo = round($ped->totalrecargo, FS_NF0);
               $ped->total = $ped->neto + $ped->totaliva - $ped->totalirpf + $ped->totalrecargo;
               $ped->save();
               
               $num++;
            }
            else
            {
               break;
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function pedidosprov()
   {
      $num = 0;
      $proveedores = $this->random_proveedores();
      
      while($num < 25)
      {
         $ped = new pedido_proveedor();
         $ped->fecha = mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016);
         $ped->hora = mt_rand(10, 20).':'.mt_rand(10, 59).':'.mt_rand(10, 59);
         $ped->codalmacen = $this->empresa->codalmacen;
         $ped->coddivisa = $this->empresa->coddivisa;
         $ped->codpago = $this->empresa->codpago;
         $ped->codserie = $this->empresa->codserie;
         
         if( mt_rand(0, 2) == 0 )
         {
            $ped->codagente = $this->agentes[0]->codagente;
            $ped->codalmacen = $this->almacenes[0]->codalmacen;
            $ped->codpago = $this->formas_pago[0]->codpago;
            $ped->coddivisa = $this->divisas[0]->coddivisa;
            $ped->tasaconv = $this->divisas[0]->tasaconv_compra;
            
            if($this->series[0]->codserie != 'R')
            {
               $ped->codserie = $this->series[0]->codserie;
               $ped->irpf = $this->series[0]->irpf;
            }
            
            $ped->observaciones = $this->observaciones($ped->fecha);
            $ped->numproveedor = mt_rand(10, 99999);
         }
         
         $eje = $this->ejercicio->get_by_fecha($ped->fecha);
         if($eje)
         {
            $ped->codejercicio = $eje->codejercicio;
            
            $ped->codproveedor = $proveedores[$num]->codproveedor;
            $ped->nombre = $proveedores[$num]->razonsocial;
            $ped->cifnif = $proveedores[$num]->cifnif;
            
            if( $ped->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = mt_rand(1, 10);
               if( mt_rand(0, 3) == 0 )
               {
                  $numlineas = mt_rand(1, 200);
               }
               
               while($numlineas > 0)
               {
                  $lin = new linea_pedido_proveedor();
                  $lin->idpedido = $ped->idpedido;
                  
                  $lin->cantidad = mt_rand(1, 3);
                  if( mt_rand(0, 3) == 0 )
                  {
                     $lin->cantidad = mt_rand(1, 19);
                  }
                  
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = round( mt_rand(1, 99)*0.3, FS_NF0_ART );
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( mt_rand(0, 9) == 0 )
                  {
                     $lin->recargo = $this->impuestos[0]->recargo;
                  }
                  
                  if( isset($articulos[$numlineas]) )
                  {
                     if($articulos[$numlineas]->sevende)
                     {
                        $lin->referencia = $articulos[$numlineas]->referencia;
                        $lin->descripcion = $articulos[$numlineas]->descripcion;
                        $lin->pvpunitario = $articulos[$numlineas]->pvp;
                        $lin->codimpuesto = $articulos[$numlineas]->codimpuesto;
                        $lin->iva = $articulos[$numlineas]->get_iva();
                        $lin->recargo = 0;
                     }
                  }
                  
                  $lin->irpf = $ped->irpf;
                  
                  if($proveedores[$num]->regimeniva == 'Exento')
                  {
                     $lin->codimpuesto = NULL;
                     $lin->iva = 0;
                     $lin->recargo = 0;
                     $ped->irpf = $lin->irpf = 0;
                  }
                  
                  if( mt_rand(0, 4) == 0 )
                  {
                     $lin->dtopor = mt_rand(0, 99);
                  }
                  
                  $lin->pvpsindto = ($lin->pvpunitario * $lin->cantidad);
                  $lin->pvptotal = $lin->pvpunitario * $lin->cantidad * (100 - $lin->dtopor) / 100;
                  
                  if( $lin->save() )
                  {
                     $ped->neto += $lin->pvptotal;
                     $ped->totaliva += ($lin->pvptotal * $lin->iva/100);
                     $ped->totalirpf += ($lin->pvptotal * $lin->irpf/100);
                     $ped->totalrecargo += ($lin->pvptotal * $lin->recargo/100);
                  }
                  
                  $numlineas--;
               }
               
               /// redondeamos
               $ped->neto = round($ped->neto, FS_NF0);
               $ped->totaliva = round($ped->totaliva, FS_NF0);
               $ped->totalirpf = round($ped->totalirpf, FS_NF0);
               $ped->totalrecargo = round($ped->totalrecargo, FS_NF0);
               $ped->total = $ped->neto + $ped->totaliva - $ped->totalirpf + $ped->totalrecargo;
               $ped->save();
               
               $num++;
            }
            else
            {
               break;
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   public function presupuestoscli()
   {
      $num = 0;
      $clientes = $this->random_clientes();
      
      while($num < 25)
      {
         $presu = new presupuesto_cliente();
         $presu->fecha = mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016);
         $presu->hora = mt_rand(10, 20).':'.mt_rand(10, 59).':'.mt_rand(10, 59);
         $presu->codalmacen = $this->empresa->codalmacen;
         $presu->coddivisa = $this->empresa->coddivisa;
         $presu->codpago = $this->empresa->codpago;
         $presu->codserie = $this->empresa->codserie;
         
         if( mt_rand(0, 2) == 0 )
         {
            $presu->codagente = $this->agentes[0]->codagente;
            $presu->codalmacen = $this->almacenes[0]->codalmacen;
            $presu->codpago = $this->formas_pago[0]->codpago;
            $presu->coddivisa = $this->divisas[0]->coddivisa;
            $presu->tasaconv = $this->divisas[0]->tasaconv;
            
            if($this->series[0]->codserie != 'R')
            {
               $presu->codserie = $this->series[0]->codserie;
               $presu->irpf = $this->series[0]->irpf;
            }
            
            $presu->observaciones = $this->observaciones($presu->fecha);
            $presu->numero2 = mt_rand(10, 99999);
         }
         
         $eje = $this->ejercicio->get_by_fecha($presu->fecha);
         if($eje)
         {
            $presu->codejercicio = $eje->codejercicio;
            
            $presu->finoferta = date('d-m-Y', strtotime($presu->fecha.' +'.mt_rand(1, 18).' months'));
            
            $presu->codcliente = $clientes[$num]->codcliente;
            $presu->nombrecliente = $clientes[$num]->razonsocial;
            $presu->cifnif = $clientes[$num]->cifnif;
            foreach($clientes[$num]->get_direcciones() as $dir)
            {
               $presu->codpais = $dir->codpais;
               $presu->provincia = $dir->provincia;
               $presu->ciudad = $dir->ciudad;
               $presu->direccion = $dir->direccion;
               $presu->codpostal = $dir->codpostal;
               
               if($dir->domfacturacion)
               {
                  break;
               }
            }
            
            if( $presu->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = mt_rand(1, 10);
               if( mt_rand(0, 3) == 0 )
               {
                  $numlineas = mt_rand(1, 200);
               }
               
               while($numlineas > 0)
               {
                  $lin = new linea_presupuesto_cliente();
                  $lin->idpresupuesto = $presu->idpresupuesto;
                  
                  $lin->cantidad = mt_rand(1, 3);
                  if( mt_rand(0, 3) == 0 )
                  {
                     $lin->cantidad = mt_rand(1, 19);
                  }
                  
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = round( mt_rand(1, 99)*0.3, FS_NF0_ART );
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( mt_rand(0, 9) == 0 )
                  {
                     $lin->recargo = $this->impuestos[0]->recargo;
                  }
                  
                  if( isset($articulos[$numlineas]) )
                  {
                     if($articulos[$numlineas]->sevende)
                     {
                        $lin->referencia = $articulos[$numlineas]->referencia;
                        $lin->descripcion = $articulos[$numlineas]->descripcion;
                        $lin->pvpunitario = $articulos[$numlineas]->pvp;
                        $lin->codimpuesto = $articulos[$numlineas]->codimpuesto;
                        $lin->iva = $articulos[$numlineas]->get_iva();
                        $lin->recargo = 0;
                     }
                  }
                  
                  $lin->irpf = $presu->irpf;
                  
                  if($clientes[$num]->regimeniva == 'Exento')
                  {
                     $lin->codimpuesto = NULL;
                     $lin->iva = 0;
                     $lin->recargo = 0;
                     $presu->irpf = $lin->irpf = 0;
                  }
                  
                  if( mt_rand(0, 4) == 0 )
                  {
                     $lin->dtopor = mt_rand(0, 99);
                  }
                  
                  $lin->pvpsindto = ($lin->pvpunitario * $lin->cantidad);
                  $lin->pvptotal = $lin->pvpunitario * $lin->cantidad * (100 - $lin->dtopor) / 100;
                  
                  if( $lin->save() )
                  {
                     $presu->neto += $lin->pvptotal;
                     $presu->totaliva += ($lin->pvptotal * $lin->iva/100);
                     $presu->totalirpf += ($lin->pvptotal * $lin->irpf/100);
                     $presu->totalrecargo += ($lin->pvptotal * $lin->recargo/100);
                  }
                  
                  $numlineas--;
               }
               
               /// redondeamos
               $presu->neto = round($presu->neto, FS_NF0);
               $presu->totaliva = round($presu->totaliva, FS_NF0);
               $presu->totalirpf = round($presu->totalirpf, FS_NF0);
               $presu->totalrecargo = round($presu->totalrecargo, FS_NF0);
               $presu->total = $presu->neto + $presu->totaliva - $presu->totalirpf + $presu->totalrecargo;
               $presu->save();
               
               $num++;
            }
            else
            {
               break;
            }
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   private function observaciones($fecha = FALSE)
   {
      $observaciones = array(
          'Pagado', 'Faltan piezas', 'No se corresponde con lo solicitado.',
          'Muy caro', 'Muy barato', 'Mala calidad',
          'La parte contratante de la primera parte será la parte contratante de la primera parte.'
      );
      shuffle($observaciones);
      
      if($fecha AND mt_rand(0, 2) == 0)
      {
         $semana = date("D", strtotime($fecha));
         $semanaArray = array( "Mon" => "lunes", "Tue" => "martes", "Wed" => "miércoles", "Thu" => "jueves", "Fri" => "viernes", "Sat" => "sábado", "Sun" => "domingo", );
         $title = urlencode(sprintf('{{Plantilla:Frase-%s}}', $semanaArray[$semana]));
         $sock = @fopen("http://es.wikiquote.org/w/api.php?action=parse&format=php&text=$title","r");
         if(!$sock)
         {
            return $observaciones[0];
         }
         else
         {
            # Hacemos la peticion al servidor
            $array__ = unserialize(stream_get_contents($sock));
            $texto_final = strip_tags($array__["parse"]["text"]["*"]);
            $texto_final = str_replace("\n\n\n\n", "\n" ,$texto_final);
            
            return $texto_final;
         }
      }
      else
      {
         return $observaciones[0];
      }
   }
   
   /**
    * Devuelve un string aleatorio de longitud $length
    * @param type $length la longitud del string
    * @return type la cadena aleatoria
    */
   private function random_string($length = 30)
   {
      return mb_substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"),
              0, $length);
   }
   
   /**
    * Devuelve un array con clientes aleatorios.
    * @param type $recursivo
    * @return \cliente
    */
   private function random_clientes($recursivo = TRUE)
   {
      $lista = array();
      
      $sql = "SELECT * FROM clientes ORDER BY random()";
      if( strtolower(FS_DB_TYPE) == 'mysql' )
      {
         $sql = "SELECT * FROM clientes ORDER BY RAND()";
      }
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new cliente($d);
         }
      }
      else if($recursivo)
      {
         $this->clientes();
         $lista = $this->random_clientes(FALSE);
      }
      
      return $lista;
   }
   
   /**
    * Devuelve un array con proveedores aleatorios.
    * @param type $recursivo
    * @return \proveedor
    */
   private function random_proveedores($recursivo = TRUE)
   {
      $lista = array();
      
      $sql = "SELECT * FROM proveedores ORDER BY random()";
      if( strtolower(FS_DB_TYPE) == 'mysql' )
      {
         $sql = "SELECT * FROM proveedores ORDER BY RAND()";
      }
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new proveedor($d);
         }
      }
      else if($recursivo)
      {
         $this->proveedores();
         return $this->random_proveedores(FALSE);
      }
      
      return $lista;
   }
   
   /**
    * Devuelve un array con empleados aleatorios.
    * @param type $recursivo
    * @return \agente
    */
   private function random_agentes($recursivo = TRUE)
   {
      $lista = array();
      
      $sql = "SELECT * FROM agentes ORDER BY random()";
      if( strtolower(FS_DB_TYPE) == 'mysql' )
      {
         $sql = "SELECT * FROM agentes ORDER BY RAND()";
      }
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new agente($d);
         }
      }
      else if($recursivo)
      {
         $this->agentes();
         return $this->random_agentes(FALSE);
      }
      
      return $lista;
   }
   
   /**
    * Devuelve un array con artículos aleatorios.
    * @param type $recursivo
    * @return \articulo
    */
   private function random_articulos($recursivo = TRUE)
   {
      $lista = array();
      
      $sql = "SELECT * FROM articulos ORDER BY random()";
      if( strtolower(FS_DB_TYPE) == 'mysql' )
      {
         $sql = "SELECT * FROM articulos ORDER BY RAND()";
      }
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new articulo($d);
         }
      }
      else if($recursivo)
      {
         $this->articulos();
         return $this->random_articulos(FALSE);
      }
      
      return $lista;
   }
}
