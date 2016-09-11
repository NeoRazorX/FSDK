<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved.
 */

class fsdk_tabla extends fs_controller
{
   public $modelo;
   public $nombre_modelo;
   public $tabla;
   public $xml;
   
   private $columnas;
   private $primary_key;
   private $restricciones;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Tabla', 'admin', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->tabla = FALSE;
      if( isset($_REQUEST['table']) )
      {
         $this->tabla = $_REQUEST['table'];
      }
      
      if($this->tabla)
      {
         $this->page->title = 'Tabla '.  $this->tabla;
         
         $this->export_structure_xml();
         $this->generar_modelo();
      }
      else
      {
         $this->new_error_msg('Tabla desconocida.', 'error', FALSE, FALSE);
      }
   }
   
   public function url()
   {
      if($this->tabla)
      {
         return parent::url().'&table='.$this->tabla;
      }
      else
      {
         return parent::url();
      }
   }
   
   public function export_structure_xml()
   {
      $cadena_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!--
    Document   : " . $this->tabla . ".xml
    Description:
        Estructura de la tabla " . $this->tabla . ".
-->

<tabla>
</tabla>\n";

      /// creamos el xml
      $archivo_xml = simplexml_load_string($cadena_xml);
      $this->columnas = Array();
      $this->restricciones = Array();
      if( $this->db->table_exists($this->tabla) )
      {
         $this->primary_key = '...';
         $this->columnas = $this->db->get_columns($this->tabla);
         $this->restricciones = $this->db->get_constraints($this->tabla);
         
         if($this->columnas)
         {
            foreach($this->columnas as $col)
            {
               $aux = $archivo_xml->addChild('columna');
               $aux->addChild('nombre', $col['column_name']);
               
               /// comprobamos si es clave primaria
               if( isset($col['key']) )
               {
                  if($col['key'] == 'PRI')
                  {
                     $this->primary_key = $col['column_name'];
                  }
               }
               
               /// comprobamos si es auto_increment
               $auto_increment = FALSE;
               if( isset($col['extra']) )
               {
                  if($col['extra'] == 'auto_increment')
                  {
                     $auto_increment = TRUE;
                  }
               }
               
               if($auto_increment)
               {
                  $aux->addChild('tipo', 'serial');
                  
                  if( $col['is_nullable'] == 'YES')
                  {
                     $aux->addChild('nulo', 'YES');
                  }
                  else
                     $aux->addChild('nulo', 'NO');
                  
                  $aux->addChild('defecto', "nextval('".$this->tabla.'_'.$col['column_name']."_seq'::regclass)");
               }
               else if($col['data_type'] == 'integer' AND $col['column_default'] == "nextval('".$this->tabla.'_'.$col['column_name']."_seq'::regclass)") /// comprobamos si es tipo serial
               {
                  $this->primary_key = $col['column_name'];
                  
                  $aux->addChild('tipo', 'serial');
                  
                  if( $col['is_nullable'] == 'YES')
                  {
                     $aux->addChild('nulo', 'YES');
                  }
                  else
                     $aux->addChild('nulo', 'NO');
                  
                  $aux->addChild('defecto', $col['column_default']);
               }
               else
               {
                  if( isset($col['character_maximum_length']) )
                  {
                     $aux->addChild('tipo', $col['data_type'] . '(' . $col['character_maximum_length'] . ')');
                  }
                  else
                     $aux->addChild('tipo', $col['data_type']);
                  
                  if( $col['is_nullable'] == 'YES')
                  {
                     $aux->addChild('nulo', 'YES');
                  }
                  else
                     $aux->addChild('nulo', 'NO');
                  
                  if( isset($col['column_default']) )
                  {
                     $aux->addChild('defecto', $col['column_default']);
                  }
               }
            }
         }
         
         if($this->restricciones)
         {
            foreach($this->restricciones as $col)
            {
               $aux = $archivo_xml->addChild('restriccion');
               
               if($col['restriccion'] == 'PRIMARY')
               {
                  $aux->addChild('nombre', $this->tabla.'_pkey');
               }
               else
                  $aux->addChild('nombre', $col['restriccion']);
               
               if( strtolower(FS_DB_TYPE) == 'postgresql' )
               {
                  switch($col['tipo'])
                  {
                     default:
                        $aux->addChild('consulta', '...');
                        break;
                        
                     case 'p':
                        $aux->addChild('consulta', 'PRIMARY KEY ('.$this->primary_key.')');
                        break;
                     
                     case 'f':
                        $aux->addChild('consulta', 'FOREIGN KEY (...) REFERENCES ...');
                        break;
                     
                     case 'u':
                        $aux->addChild('consulta', 'UNIQUE (...)');
                        break;
                  }
               }
               else
               {
                  if($col['tipo'] == 'PRIMARY KEY')
                  {
                     $aux->addChild('consulta', 'PRIMARY KEY ('.$this->primary_key.')');
                  }
                  else
                     $aux->addChild('consulta', $col['tipo'].' (...)');
               }
            }
         }
      }
      
      $this->xml = $archivo_xml->asXML();
   }
   
   private function generar_modelo()
   {
      $this->nombre_modelo = $this->tabla;
      if( substr($this->nombre_modelo, -1) == 's' )
      {
         $this->nombre_modelo = substr($this->nombre_modelo, 0, -1);
      }
      
      $tab = '   ';
      $this->modelo = "<?php\n\n"
              . "class ".$this->nombre_modelo." extends fs_model\n{\n";
      
      if($this->columnas)
      {
         foreach($this->columnas as $col)
         {
            if($col['column_name'] == $this->primary_key)
            {
               $this->modelo .= "\n".$tab."/// clave primaria. ".$col['data_type']."\n";
               $this->modelo .= $tab.'public $'.$col['column_name'].";\n\n";
            }
            else
            {
               $this->modelo .= $tab.'public $'.$col['column_name'].";\n";
            }
         }
         
         $this->modelo .= "\n"
                 . $tab.'public function __construct($d=FALSE)'."\n"
                 . $tab."{\n"
                 . $tab.$tab."parent::__construct('".$this->tabla."');\n"
                 . $tab.$tab.'if($d)'."\n"
                 . $tab.$tab."{\n";
         
         foreach($this->columnas as $col)
         {
            if($col['data_type'] == 'boolean' OR $col['data_type'] == 'tinyint(1)')
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name'].' = $this->str2bool($d'."['".$col['column_name']."']);\n";
            }
            else if($col['data_type'] == 'date')
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name'].' = date("d-m-Y", strtotime($d'."['".$col['column_name']."']));\n";
            }
            else if($col['data_type'] == 'double')
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name'].' = floatval($d'."['".$col['column_name']."']);\n";
            }
            else if($col['data_type'] == 'integer')
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name'].' = $this->intval($d'."['".$col['column_name']."']);\n";
            }
            else
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name'].' = $d'."['".$col['column_name']."'];\n";
            }
         }
         
         $this->modelo .= $tab.$tab."}\n"
                 . $tab.$tab."else\n"
                 . $tab.$tab."{\n"
                 . $tab.$tab.$tab."/// valores predeterminados\n";
         
         foreach($this->columnas as $col)
         {
            if($col['data_type'] == 'boolean' OR $col['data_type'] == 'tinyint(1)')
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name']." = FALSE;\n";
            }
            else if($col['data_type'] == 'date')
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name']." = date('d-m-Y');\n";
            }
            else if($col['data_type'] == 'double')
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name']." = 0;\n";
            }
            else
            {
               $this->modelo .= $tab.$tab.$tab.'$this->'.$col['column_name']." = NULL;\n";
            }
         }
         
         $this->modelo .= $tab.$tab."}\n"
                 . $tab."}\n\n"
                 . $tab."public function install()\n"
                 . $tab."{\n"
                 . $tab.$tab."return '';\n"
                 . $tab."}\n\n"
                 . $tab."public function exists()\n"
                 . $tab."{\n";
         
         $encontrada = FALSE;
         foreach($this->columnas as $col)
         {
            if($col['column_name'] == $this->primary_key)
            {
               $this->modelo .= $tab.$tab.'if( is_null($this->'.$col['column_name'].") )\n"
                       . $tab.$tab."{\n"
                       . $tab.$tab.$tab."return FALSE;\n"
                       . $tab.$tab."}\n"
                       . $tab.$tab."else\n"
                       . $tab.$tab."{\n"
                       . $tab.$tab.$tab.'return $this->db->select('."'SELECT * FROM ".$this->tabla
                       . " WHERE ".$col['column_name']." = '".'.$this->var2str($this->'.$col['column_name'].').'."';');\n"
                       . $tab.$tab."}\n";
               $encontrada = TRUE;
               break;
            }
         }
         
         if(!$encontrada)
         {
            $this->modelo .= $tab.$tab."/// tu código aquí\n";
         }
         
         $this->modelo .= $tab."}\n\n"
                 . $tab."public function save()\n"
                 . $tab."{\n"
                 . $tab.$tab.'if( $this->exists() )'."\n"
                 . $tab.$tab."{\n"
                 . $tab.$tab.$tab."/// UPDATE ".$this->tabla." SET ... WHERE ...;\n"
                 . $tab.$tab."}\n"
                 . $tab.$tab."else\n"
                 . $tab.$tab."{\n"
                 . $tab.$tab.$tab."/// INSERT INTO ".$this->tabla." (...) VALUES (...);\n"
                 . $tab.$tab."}\n"
                 . $tab."}\n\n"
                 . $tab."public function delete()\n"
                 . $tab."{\n";
         
         $encontrada = FALSE;
         foreach($this->columnas as $col)
         {
            if($col['column_name'] == $this->primary_key)
            {
               $this->modelo .= $tab.$tab.'return $this->db->exec('."'DELETE FROM ".$this->tabla
                       . " WHERE ".$col['column_name']." = '".'.$this->var2str($this->'.$col['column_name'].').'."';');\n";
               $encontrada = TRUE;
               break;
            }
         }
         
         if(!$encontrada)
         {
            $this->modelo .= $tab.$tab."/// tu código aquí\n";
         }
         
         $this->modelo .= $tab."}\n\n";
      }
      
      $this->modelo .= "}\n";
   }
}
