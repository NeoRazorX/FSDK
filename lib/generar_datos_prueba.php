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
require_model('servicio_cliente.php');

/**
 * Clase con todo tipo de funciones para generar datos aleatorios.
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
    * Constructor. Inicializamos todo lo necesario, y randomizamos.
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
   
   /**
    * Genera $max fabricantes aleatorios.
    * Devuelve el número de fabricantes generados.
    * @param int $max
    * @return int
    */
   public function fabricantes($max = 50)
   {
      $num = 0;
      
      while($num < $max)
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
   
   /**
    * Genera $max familias aleatorias.
    * Devuelve el número de familias creadas.
    * @param type $max
    * @return int
    */
   public function familias($max = 50)
   {
      $num = 0;
      $codfamilia = NULL;
      
      while($num < $max)
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
   
   /**
    * Acorta un string hasta $len y sustituye caracteres especiales.
    * Devuelve el string acortado.
    * @param type $txt
    * @param type $len
    * @return type
    */
   private function txt2codigo($txt, $len = 8)
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
   
   /**
    * Genera $max artículos aleatorios.
    * Devuelve el número de artículos generados.
    * @param type $max
    * @return int
    */
   public function articulos($max = 50)
   {
      $num = 0;
      
      $fab = new fabricante();
      $fabricantes = $fab->all();
      
      $fam = new familia();
      $familias = $fam->all();
      
      while($num < $max)
      {
         if( mt_rand(0, 2) == 0 )
         {
            shuffle($fabricantes);
            shuffle($familias);
            
            if($this->impuestos[0]->iva <= 10)
            {
               shuffle($this->impuestos);
            }
         }
         
         $art = new articulo();
         $art->descripcion = $this->descripcion();
         $art->codimpuesto = $this->impuestos[0]->codimpuesto;
         $art->set_pvp_iva( $this->precio(1, 49, 699) );
         $art->stockmin = mt_rand(0, 10);
         $art->stockmax = mt_rand($art->stockmin+1, $art->stockmin+1000);
         
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
         
         if( mt_rand(0, 9) > 0 )
         {
            $art->codfabricante = $fabricantes[0]->codfabricante;
            $art->codfamilia = $familias[0]->codfamilia;
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
   
   /**
    * Devuelve una descripción de producto aleatoria.
    * @return string
    */
   private function descripcion()
   {
      $prefijos = array(
          'Jet', 'Jex', 'Max', 'Pro', 'FX', 'Neo', 'Maxi', 'Extreme', 'Sub',
          'Ultra', 'Minga', 'Hiper', 'Giga', 'Mega', 'Super', 'Fusion', 'Broken'
      );
      shuffle($prefijos);
      
      $nombres = array(
          'Motor', 'Engine', 'Generator', 'Tool', 'Oviode', 'Box', 'Proton', 'Neutro',
          'Radeon', 'GeForce', 'nForce', 'Labtech', 'Station', 'Arco', 'Arkam'
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
            $texto .= ': '.$descripciones1[0]." con:\n- ".$descripciones2[0]."\n- ".$descripciones2[1]."\n- ".$descripciones2[2]."\n- ".$descripciones2[3].'.';
            break;
         
         default:
            $texto .= ': '.$descripciones1[0].' con '.$descripciones2[0].', '.$descripciones2[1].' y '.$descripciones2[2].'.';
            break;
      }
      
      return $texto;
   }
   
   /**
    * Devuelve un número aleatorio entre $min y $max1.
    * 1 de cada 10 veces lo devuelve entre $min y $max2.
    * 1 de cada 5 veces lo devuelve con decimales.
    * @param type $min
    * @param type $max1
    * @param type $max2
    * @return type
    */
   private function cantidad($min, $max1, $max2)
   {
      $cantidad = mt_rand($min, $max1);
      
      if( mt_rand(0, 9) == 0 )
      {
         $cantidad = mt_rand($min, $max2);
      }
      else if( $cantidad < $max1 AND mt_rand(0, 4) == 0 )
      {
         $cantidad += round( mt_rand(1, 5) / mt_rand(1, 10), mt_rand(0, 3) );
         $cantidad = min( array($max1, $cantidad) );
      }
      
      return $cantidad;
   }
   
   /**
    * Devuelve un número aleatorio entre $min y $max1.
    * 1 de cada 10 veces lo devuelve entre $min y $max2.
    * 1 de cada 3 veces lo devuelve con decimales.
    * @param type $min
    * @param type $max1
    * @param type $max2
    * @return type
    */
   private function precio($min, $max1, $max2)
   {
      $precio = mt_rand($min, $max1);
      
      if( mt_rand(0, 9) == 0 )
      {
         $precio = mt_rand($min, $max2);
      }
      else if( $precio < $max1 AND mt_rand(0, 2) == 0 )
      {
         $precio += round( mt_rand(1, 5) / mt_rand(1, 10), FS_NF0_ART );
         $precio = min( array($max1, $precio) );
      }
      
      return $precio;
   }
   
   /**
    * Genera $max agentes (empleados) aleatorios.
    * Devuelve el número de agentes generados.
    * @param type $max
    * @return int
    */
   public function agentes($max = 50)
   {
      $num = 0;
      
      while($num < $max)
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
         
         if( mt_rand(0, 5) == 0 )
         {
            $agente->porcomision = $this->cantidad(0, 5, 20);
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
   
   /**
    * Genera $max clientes aleatorios.
    * Devuelve el número de clientes generados.
    * @param type $max
    * @return int
    */
   public function clientes($max = 50)
   {
      $num = 0;
      
      while($num < $max)
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
         
         if( mt_rand(0, 9) == 0 )
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
   
   /**
    * Genera $max proveedores aleatorios.
    * Devuelve el número de proveedores generados.
    * @param type $max
    * @return int
    */
   public function proveedores($max = 50)
   {
      $num = 0;
      
      while($num < $max)
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
         
         if( mt_rand(0, 9) == 0 )
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
   
   /**
    * Devuelve un nombre aleatorio.
    * @return string
    */
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
   
   /**
    * Devuelve dos apellidos aleatorios.
    * @return type
    */
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
   
   /**
    * Devuelve un nombre comercial aleatorio.
    * @return type
    */
   private function empresa()
   {
      $nombres = array(
          'Tech', 'Motor', 'Pasión', 'Future', 'Max', 'Massive', 'Industrial',
          'Plastic', 'Pro', 'Micro', 'System', 'Light', 'Magic', 'Fake', 'Techno',
          'Miracle', 'NX', 'Smoke', 'Steam', 'Power', 'FX', 'Fusion', 'Bastion',
          'Investments', 'Solutions', 'Neo', 'Ming', 'Tube', 'Pear', 'Apple',
          'Dolphin', 'Chrome', 'Cat', 'Hat', 'Linux', 'Soft', 'Mobile', 'Phone',
          'XL', 'Open', 'Thunder', 'Zero', 'Scorpio', 'Zelda', '10', 'V', 'Q',
          'X', 'Arch', 'Arco', 'Broken', 'Arkam', 'RX'
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
   
   /**
    * Devuelve un email aleatorio.
    * @return type
    */
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
   
   /**
    * Devuelve una provincia aleatoria.
    * @return string
    */
   private function provincia()
   {
      $nombres = array(
          'Alicante', 'Valencia', 'Andalucía', 'Madrid', 'Pichita',
          'La meseta', 'Black mesa', 'Antioquía', 'Genérica', 'Medellín'
      );
      
      shuffle($nombres);
      return $nombres[0];
   }
   
   /**
    * Devuelve una ciudad aleatoria.
    * @return string
    */
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
   
   /**
    * Devuelve una dirección aleatoria.
    * @return type
    */
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
   
   /**
    * Genera $max albaranes de venta aleatorios.
    * Devuelve el número de albaranes generados.
    * @param type $max
    * @return int
    */
   public function albaranescli($max = 25)
   {
      $num = 0;
      $clientes = $this->random_clientes();
      
      $recargo = FALSE;
      if( $clientes[0]->recargo OR mt_rand(0, 4) == 0 )
      {
         $recargo = TRUE;
      }
      
      while($num < $max)
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
            foreach ($clientes[$num]->get_direcciones() as $dir)
            {
               if ($dir->domfacturacion)
               {
                  $alb->codpais = $dir->codpais;
                  $alb->provincia = $dir->provincia;
                  $alb->ciudad = $dir->ciudad;
                  $alb->direccion = $dir->direccion;
                  $alb->codpostal = $dir->codpostal;
               }

               if ($dir->domenvio)
               {
                  $alb->envio_nombre = $this->nombre();
                  $alb->envio_apellidos = $this->apellidos();
                  $alb->envio_provincia = $dir->provincia;
                  $alb->envio_codpostal = $dir->codpostal;
                  $alb->envio_ciudad = $dir->ciudad;
                  $alb->envio_direccion = $dir->ciudad;
                  $alb->envio_codigo = mt_rand(10, 99999);
               }
            }

            if( $alb->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = $this->cantidad(1, 10, 200);
               while($numlineas > 0)
               {
                  $lin = new linea_albaran_cliente();
                  $lin->idalbaran = $alb->idalbaran;
                  $lin->cantidad = $this->cantidad(1, 3, 19);
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = $this->precio(1, 49, 699);
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( $recargo AND mt_rand(0, 2) == 0 )
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
                     $lin->dtopor = $this->cantidad(0, 33, 100);
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
   
   /**
    * Genera $max albaranes de compra aleatorios.
    * Devuelve el número de albaranes generados.
    * @param type $max
    * @return int
    */
   public function albaranesprov($max = 25)
   {
      $num = 0;
      $proveedores = $this->random_proveedores();
      
      $recargo = FALSE;
      if( mt_rand(0, 4) == 0 )
      {
         $recargo = TRUE;
      }
      
      while($num < $max)
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
               
               $numlineas = $this->cantidad(1, 10, 200);
               while($numlineas > 0)
               {
                  $lin = new linea_albaran_proveedor();
                  $lin->idalbaran = $alb->idalbaran;
                  
                  $lin->cantidad = $this->cantidad(1, 3, 19);
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = $this->precio(1, 49, 699);
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( $recargo AND mt_rand(0, 2) == 0 )
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
                     $lin->dtopor = $this->cantidad(0, 33, 100);
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
   
   /**
    * Genera $max pedidos de venta aleatorios.
    * Devuelve el número de pedidos generados.
    * @param type $max
    * @return int
    */
   public function pedidoscli($max = 25)
   {
      $num = 0;
      $clientes = $this->random_clientes();
      
      $recargo = FALSE;
      if( $clientes[0]->recargo OR mt_rand(0, 4) == 0 )
      {
         $recargo = TRUE;
      }
      
      while($num < $max)
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
            foreach ($clientes[$num]->get_direcciones() as $dir)
            {
               if ($dir->domenvio)
               {
                  $ped->codpais = $dir->codpais;
                  $ped->provincia = $dir->provincia;
                  $ped->ciudad = $dir->ciudad;
                  $ped->direccion = $dir->direccion;
                  $ped->codpostal = $dir->codpostal;
               }

               if ($dir->domenvio)
               {
                  $ped->envio_nombre = $this->nombre();
                  $ped->envio_apellidos = $this->apellidos();
                  $ped->envio_provincia = $dir->provincia;
                  $ped->envio_codpostal = $dir->codpostal;
                  $ped->envio_ciudad = $dir->ciudad;
                  $ped->envio_direccion = $dir->ciudad;
                  $ped->envio_codigo = mt_rand(10, 99999);
               }
            }

            if( $ped->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = $this->cantidad(1, 10, 200);
               while($numlineas > 0)
               {
                  $lin = new linea_pedido_cliente();
                  $lin->idpedido = $ped->idpedido;
                  $lin->cantidad = $this->cantidad(1, 3, 19);
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = $this->precio(1, 49, 699);
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( $recargo AND mt_rand(0, 2) == 0 )
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
                     $lin->dtopor = $this->cantidad(0, 33, 100);
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
   
   /**
    * Genera $max pedidos de compra aleatorios.
    * Devuelve el número de pedidos generados.
    * @param type $max
    * @return int
    */
   public function pedidosprov($max = 25)
   {
      $num = 0;
      $proveedores = $this->random_proveedores();
      
      $recargo = FALSE;
      if( mt_rand(0, 4) == 0 )
      {
         $recargo = TRUE;
      }
      
      while($num < $max)
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
               
               $numlineas = $this->cantidad(1, 10, 200);
               while($numlineas > 0)
               {
                  $lin = new linea_pedido_proveedor();
                  $lin->idpedido = $ped->idpedido;
                  $lin->cantidad = $this->cantidad(1, 3, 19);
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = $this->precio(1, 49, 699);
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( $recargo AND mt_rand(0, 2) == 0 )
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
                     $lin->dtopor = $this->cantidad(0, 33, 100);
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
   
   /**
    * Genera $max presupuestos de venta aleatorios.
    * Devuelve el número de presupuestos generados.
    * @param type $max
    * @return int
    */
   public function presupuestoscli($max = 25)
   {
      $num = 0;
      $clientes = $this->random_clientes();
      
      $recargo = FALSE;
      if( $clientes[0]->recargo OR mt_rand(0, 4) == 0 )
      {
         $recargo = TRUE;
      }
      
      while($num < $max)
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
               if ($dir->domfacturacion)
               {
                  $presu->codpais = $dir->codpais;
                  $presu->provincia = $dir->provincia;
                  $presu->ciudad = $dir->ciudad;
                  $presu->direccion = $dir->direccion;
                  $presu->codpostal = $dir->codpostal;
               }
               if($dir->domenvio)
               {
                  $presu->envio_nombre = $this->nombre();
                  $presu->envio_apellidos = $this->apellidos();
                  $presu->envio_provincia = $dir->provincia;
                  $presu->envio_codpostal = $dir->codpostal;
                  $presu->envio_ciudad = $dir->ciudad;
                  $presu->envio_direccion = $dir->ciudad;
                  $presu->envio_codigo = mt_rand(10, 99999);
               }
            }
            
            if( $presu->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = $this->cantidad(1, 10, 200);
               while($numlineas > 0)
               {
                  $lin = new linea_presupuesto_cliente();
                  $lin->idpresupuesto = $presu->idpresupuesto;
                  $lin->cantidad = $this->cantidad(1, 3, 19);
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = $this->precio(1, 49, 699);
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( $recargo AND mt_rand(0, 2) == 0 )
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
                     $lin->dtopor = $this->cantidad(0, 33, 100);
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
   
   /**
    * Devuelve unas observaciones aleatorias.
    * @param type $fecha
    * @return string
    */
   private function observaciones($fecha = FALSE)
   {
      $observaciones = array(
          'Pagado', 'Faltan piezas', 'No se corresponde con lo solicitado.',
          'Muy caro', 'Muy barato', 'Mala calidad',
          'La parte contratante de la primera parte será la parte contratante de la primera parte.'
      );
      
      /// añadimos muchos blas como otra opción
      $bla = 'Bla';
      while( mt_rand(0, 29) > 0 )
      {
         $bla .= ', bla';
      }
      $observaciones[] = $bla.'.';
      
      /// randomizamos (es posible que me haya inventado esta palabra)
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
   
   /**
    * Genera $max servicios aleatorios.
    * Devuelve el número de servicios generados.
    * @param type $max
    * @return int
    */
   public function servicioscli($max = 25)
   {
      $num = 0;
      $clientes = $this->random_clientes();
      
      $recargo = FALSE;
      if( $clientes[0]->recargo OR mt_rand(0, 4) == 0 )
      {
         $recargo = TRUE;
      }
      
      while($num < $max)
      {
         $serv = new servicio_cliente();
         $serv->fecha = mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016);
         $serv->hora = mt_rand(10, 20).':'.mt_rand(10, 59).':'.mt_rand(10, 59);
         $serv->codalmacen = $this->empresa->codalmacen;
         $serv->coddivisa = $this->empresa->coddivisa;
         $serv->codpago = $this->empresa->codpago;
         $serv->codserie = $this->empresa->codserie;
         
         if( mt_rand(0, 2) == 0 )
         {
            $serv->codagente = $this->agentes[0]->codagente;
            $serv->codalmacen = $this->almacenes[0]->codalmacen;
            $serv->codpago = $this->formas_pago[0]->codpago;
            $serv->coddivisa = $this->divisas[0]->coddivisa;
            $serv->tasaconv = $this->divisas[0]->tasaconv;
            
            if($this->series[0]->codserie != 'R')
            {
               $serv->codserie = $this->series[0]->codserie;
               $serv->irpf = $this->series[0]->irpf;
            }
            
            $serv->observaciones = $this->observaciones($serv->fecha);

            $serv->numero2 = mt_rand(10, 99999);
         }
         $serv->material = $this->observaciones($serv->fecha);
         $serv->material_estado = $this->observaciones($serv->fecha);
         $serv->accesorios = $this->observaciones($serv->fecha);
         $serv->descripcion = $this->observaciones($serv->fecha);
         $serv->solucion = $this->observaciones($serv->fecha);
         $serv->fechainicio = Date('d-m-Y H:i',mt_rand(1356998400,1531353600));
         $serv->fechafin = date('Y-m-d H:i', strtotime($serv->fechainicio. '+ '.mt_rand(10, 59).' minutes'));   
         $serv->idestado = mt_rand(1, 2);
         $serv->garantia = rand(0,1) == 1;


         $eje = $this->ejercicio->get_by_fecha($serv->fecha);
         if($eje)
         {
            $serv->codejercicio = $eje->codejercicio;
            
            $serv->codcliente = $clientes[$num]->codcliente;
            $serv->nombrecliente = $clientes[$num]->razonsocial;
            $serv->cifnif = $clientes[$num]->cifnif;
            foreach($clientes[$num]->get_direcciones() as $dir)
            {
               $serv->codpais = $dir->codpais;
               $serv->provincia = $dir->provincia;
               $serv->ciudad = $dir->ciudad;
               $serv->direccion = $dir->direccion;
               $serv->codpostal = $dir->codpostal;
               
               if($dir->domfacturacion)
               {
                  break;
               }
            }
            
            if( $serv->save() )
            {
               $articulos = $this->random_articulos();
               
               $numlineas = $this->cantidad(1, 10, 200);
               while($numlineas > 0)
               {
                  $lin = new linea_servicio_cliente();
                  $lin->idservicio = $serv->idservicio;
                  $lin->cantidad = $this->cantidad(1, 3, 19);
                  $lin->descripcion = $this->descripcion();
                  $lin->pvpunitario = $this->precio(1, 49, 699);
                  $lin->codimpuesto = $this->impuestos[0]->codimpuesto;
                  $lin->iva = $this->impuestos[0]->iva;
                  
                  if( $recargo AND mt_rand(0, 2) == 0 )
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
                  
                  $lin->irpf = $serv->irpf;
                  
                  if($clientes[$num]->regimeniva == 'Exento')
                  {
                     $lin->codimpuesto = NULL;
                     $lin->iva = 0;
                     $lin->recargo = 0;
                     $serv->irpf = $lin->irpf = 0;
                  }
                  
                  if( mt_rand(0, 4) == 0 )
                  {
                     $lin->dtopor = $this->cantidad(0, 33, 100);
                  }
                  
                  $lin->pvpsindto = ($lin->pvpunitario * $lin->cantidad);
                  $lin->pvptotal = $lin->pvpunitario * $lin->cantidad * (100 - $lin->dtopor) / 100;
                  
                  if( $lin->save() )
                  {
                     $serv->neto += $lin->pvptotal;
                     $serv->totaliva += ($lin->pvptotal * $lin->iva/100);
                     $serv->totalirpf += ($lin->pvptotal * $lin->irpf/100);
                     $serv->totalrecargo += ($lin->pvptotal * $lin->recargo/100);
                  }
                  
                  $numlineas--;
               }
               
               /// redondeamos
               $serv->neto = round($serv->neto, FS_NF0);
               $serv->totaliva = round($serv->totaliva, FS_NF0);
               $serv->totalirpf = round($serv->totalirpf, FS_NF0);
               $serv->totalrecargo = round($serv->totalrecargo, FS_NF0);
               $serv->total = $serv->neto + $serv->totaliva - $serv->totalirpf + $serv->totalrecargo;
               $serv->save();
               
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
}
