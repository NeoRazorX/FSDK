<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved.
 */

class fsdk_tabla extends fs_controller
{
   public $tabla;
   public $xml;
   
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
         
         $this->xml = $this->export_structure_xml($this->tabla);
      }
      else
      {
         $this->new_error_msg('Tabla desconocida.');
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
   
   public function export_structure_xml($table)
   {
      $cadena_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!--
    Document   : " . $table . ".xml
    Description:
        Estructura de la tabla " . $table . ".
-->

<tabla>
</tabla>\n";

      /// creamos el xml
      $archivo_xml = simplexml_load_string($cadena_xml);
      $columnas = Array();
      $restricciones = Array();
      if( $this->db->table_exists($table) )
      {
         $primary_key = '...';
         $columnas = $this->db->get_columns($table);
         $restricciones = $this->db->get_constraints($table);
         
         if($columnas)
         {
            foreach($columnas as $col)
            {
               $aux = $archivo_xml->addChild('columna');
               $aux->addChild('nombre', $col['column_name']);
               
               /// comprobamos si es clave primaria
               if( isset($col['key']) )
               {
                  if($col['key'] == 'PRI')
                  {
                     $primary_key = $col['column_name'];
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
                  
                  $aux->addChild('defecto', "nextval('".$table.'_'.$col['column_name']."_seq'::regclass)");
               }
               else if($col['data_type'] == 'integer' AND $col['column_default'] == "nextval('".$table.'_'.$col['column_name']."_seq'::regclass)") /// comprobamos si es tipo serial
               {
                  $primary_key = $col['column_name'];
                  
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
         
         if($restricciones)
         {
            foreach($restricciones as $col)
            {
               $aux = $archivo_xml->addChild('restriccion');
               
               if($col['restriccion'] == 'PRIMARY')
               {
                  $aux->addChild('nombre', $table.'_pkey');
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
                        $aux->addChild('consulta', 'PRIMARY KEY ('.$primary_key.')');
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
                     $aux->addChild('consulta', 'PRIMARY KEY ('.$primary_key.')');
                  }
                  else
                     $aux->addChild('consulta', $col['tipo'].' (...)');
               }
            }
         }
      }
      
      return $archivo_xml->asXML();
   }
}
