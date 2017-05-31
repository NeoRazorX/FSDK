<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez (neorazorx@gmail.com)
 * 
 * This file is part of plugin for FacturaScripts
 * Copyright (C) 2017 Artex Trading sa <jcuello@artextrading.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of fs_standard_controller
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class fs_standard_controller extends fs_controller {

   /**
    * Icons for orders. Making a standard for all views
    */
   const icono_asc = '<span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>';   
   const icono_desc= '<span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>';

   /**
    * Icon for the view
    * @var string
    */
   public $icon;
   
   /**
    * Title for the view
    * @var string
    */
   public $title;
   
   /**
    * Determines whether the user can delete data from the controller
    * @var boolean
    */
   public $allow_delete;

   /**
    * Cursor to data
    * @var array of model 
    */
   public $cursor;

   /**
    * List of fields that are loaded in the cursor and its parameterization
    * @var array of dictionary("label" => "Field Label", "field" => "Field Name", "display" => "left/center/right/none")
    */
   public $fields;

   /**
    * List of fields to select order by
    * @var array of dictionary
    */
   public $orderby;

   /**
    * List of fields filters that are loaded in the cursor and its parameterization
    * @var array of dictionay
    */
   public $filters;
   
   /**
    * First record to select from database
    * @var int
    */
   public $offset;

   /**
    * Total number of records reader
    * @var int
    */
   public $count;

   /**
    * Item selected into order by list
    * @var string
    */
   public $selected_orderby;

   
   /* -------------
    * P R I V A T E
    * ------------- */
   
   private function get_selected_order($order) {
      $result = FALSE;
      $keys = array_keys($this->orderby);
      foreach ($keys as $item ) {
         if ($item == $order) {
            $result = $item;
            break;
         }
      }

      if ($result == FALSE)
         $result = array_keys($this->orderby)[0];
      
      return $result;
   }   
   
   /**
    * Define a new filter data option
    * @param string $type    ['select', 'checkbox']
    * @param string $key     (filter identification)
    * @param array  $options (options for configure and run filter)
    */
   private function add_filter($type, $key, $options) {      
      $this->filters[$key] = ['type' => $type, 'value'=> $this->get_value($key), 'options' => $options];
   }
   
   /* -----------------
    * P R O T E C T E D
    * ----------------- */

   /**
    * Check if the parameter is informed in the url and returns its value
    * @param string $field : Param name to check into url
    * @return variant
    */
   protected function get_value($field) {
      $result = FALSE;
      if (isset($_REQUEST[$field]))         
         $result = $_REQUEST[$field];

      return $result;
   }

   /**
    * Set a Where clause to the selection of records
    * @return string
    */
   protected function get_where() {
      $result = "1 = 1";
      
      foreach ($this->filters as $key => $value) {
         switch ($value['type']) {
            case 'select': {
               if ($value['value'] != "")
                  $result .= " AND LOWER(" . $value['options']['field'] .") = LOWER('" . $value['value'] . "')";

               break;
            }

            case 'checkbox': {
               if ($value['value'])
                  if ($value['options']['inverse'])
                    $result .= " AND " . $value['options']['field'] . " = FALSE";
                  else
                    $result .= " AND " . $value['options']['field'] . " = TRUE";

               break;
            }
            
            default: {
               break;
            }
         }
      }
      
      return $result;
   }

   /**
    * Make a string with params into url source
    * @return string
    */
   protected function get_params() {
      $result = "";
      if ($this->get_value("query"))
         $result = "&query=" . $_REQUEST["query"];

      foreach ($this->filters as $key => $value) {
         if ($value['value'] != "")
            $result .= "&" . $key . "=" . $value['value'];
      }                 
      
      return $result;
   }

   /**
    * Add a field to order by list
    * @param string $field
    * @param string $label
    */
   protected function add_orderby($field, $label = '') {
      $key1 = strtolower($field).'_asc';
      $key2 = strtolower($field).'_desc';
      
      if (empty($label))
         $label = ucfirst ($field);
      
      $this->orderby[$key1] = ['icon'  => $this::icono_asc, 'label' => $label,'order' => $field.' ASC'];
      $this->orderby[$key2] = ['icon'  => $this::icono_desc, 'label' => $label,'order' => $field.' DESC'];      
   }
   
   /**
    * Add a filter type data table selection
    * @param string $key      (Filter identifier)
    * @param string $table    (Table name)
    * @param string $field    (Field of the table with the data to show)
    * @param string $where    (Where condition for table)
    */
   protected function add_filter_select($key, $table, $field = '', $where = '') {
      if (empty($field))
         $field = $key;
      
      $options = [ 'field' => $field, 'table' => $table, 'where' => $where ];
      $this->add_filter('select', $key, $options);
   }
   
   /**
    * Add a filter type boolean condition
    * @param string  $key     (Filter identifier)
    * @param string  $label   (Human reader description)
    * @param string  $field   (Field of the table to apply filter)
    * @param boolean $inverse (If you need to invert the selected value) 
    */
   protected function add_filter_checkbox($key, $label, $field = '', $inverse = FALSE) {
      if (empty($field))
         $field = $key;
      
      $options = [ 'label' => $label, 'field' => $field, 'inverse' => $inverse ];
      $this->add_filter('checkbox', $key, $options);
   }   
   
   /*
    * FacturaScript entry point
    */
   protected function private_core() {
      parent::private_core();

      // Set standard template
      $this->template = "fs_standard_view";

      // Set pagination values
      if (isset($_GET["offset"]))
         $this->offset = intval($_GET["offset"]);
      else
         $this->offset = 0;

      // Set order by selected
      $order = $this->get_value("order");
      if ($order)
         $this->selected_orderby = $this->get_selected_order($order);
      else
         $this->selected_orderby = array_keys($this->orderby)[0];
   }

   /* -----------
    * P U B L I C
    * ----------- */

   /**
    * Constructor inherited from parent controller
    */
   public function __construct($name = '', $title = 'home', $folder = '', $admin = FALSE, $shmenu = TRUE, $important = FALSE) {
      $this->allow_delete = FALSE;
      $this->offset = 0;
      $this->count = 0;

      if (!is_array($this->orderby) or empty($this->orderby)) 
         $this->orderby = [];

      if (!is_array($this->filters) or empty($this->filters)) 
         $this->filters = [];      
      
      parent::__construct($name, $title, $folder, $admin, $shmenu, $important);
   }

   /**
    * Load Array list from database table
    * @param string $field : Field name to load
    * @param string $table : Table name from load
    * @param string $where : Where filter
    * @return array
    */
   public function optionlist($field, $table, $where) {
      $result = [];
      if ($this->db->table_exists($table)) {
         $sql = "SELECT DISTINCT " . $field . " FROM " . $table
              . " WHERE " . $field . " IS NOT NULL AND " . $field . " <> ''";

         if ($where != "")
            $sql .= " AND " .$where;

         $sql .= " ORDER BY 1 ASC;";

         $data = $this->db->select($sql);
         foreach ($data as $item) {
            $value = $item[$field];
            if ($value != "") {
               $result[mb_strtolower($value, "UTF8")] = $value;
            }
         }
      }
      return $result;
   }
   
   /*
    * Calculate footer pagination jumper
    * @return array of array(
    *      url    => link to jump when user click
    *      icon   => shows the specified bootstrap icon instead of the page number
    *      page   => page number
    *      active => if item is active item
    */

   public function pagination() {
      $result = [];
      $url = $this->url() . $this->get_params();
      $page_margin = 3;
      $index = 0;

      $record_min = $this->offset - (FS_ITEM_LIMIT * $page_margin);
      if ($record_min < 0) {
         $record_min = 0;
      }

      $record_max = $this->offset + (FS_ITEM_LIMIT * ($page_margin + 1));
      if ($record_max > $this->count) {
         $record_max = $this->count;
      }

      // Add first page, if not included in pag_margin
      if ($this->offset > (FS_ITEM_LIMIT * $page_margin)) {
         $result[$index] = [
             'url' => $url . "&offset=0",
             'icon' => "glyphicon-step-backward",
             'page' => 1,
             'active' => FALSE
         ];
         $index++;
      }

      // Add middle left page, if offset is greater than page_margin
      $record_middle = ($record_min > FS_ITEM_LIMIT) ? ($this->offset / 2) : $record_min;
      if ($record_middle < $record_min) {
         $page = floor($record_middle / FS_ITEM_LIMIT);
         $result[$index] = [
             'url' => $url . "&offset=" . ($page * FS_ITEM_LIMIT),
             'icon' => "glyphicon-backward",
             'page' => ($page + 1),
             'active' => FALSE
         ];
         $index++;
      }

      // Add -pagination / offset / +pagination
      for ($record = $record_min; $record < $record_max; $record += FS_ITEM_LIMIT) {
         if (($record >= $record_min AND $record <= $this->offset) OR ( $record <= $record_max AND $record >= $this->offset)) {
            $page = ($record / FS_ITEM_LIMIT) + 1;
            $result[$index] = [
                'url' => $url . "&offset=" . $record,
                'icon' => FALSE,
                'page' => $page,
                'active' => ($record == $this->offset)
            ];

            $index++;
         }
      }

      // Add middle right page, if offset is lesser than page_margin   
      $record_middle = $this->offset + (($this->count - $this->offset) / 2);
      if ($record_middle > $record_max) {
         $page = floor($record_middle / FS_ITEM_LIMIT);
         $result[$index] = [
             'url' => $url . "&offset=" . ($page * FS_ITEM_LIMIT),
             'icon' => "glyphicon-forward",
             'page' => ($page + 1),
             'active' => FALSE
         ];
         $index++;
      }

      // Add last page, if not include in pag_margin
      if ($record_max < $this->count) {
         $page_max = floor($this->count / FS_ITEM_LIMIT);
         $result[$index] = [
             'url' => $url . "&offset=" . ($page_max * FS_ITEM_LIMIT),
             'icon' => "glyphicon-step-forward",
             'page' => ($page_max + 1),
             'active' => FALSE
         ];
      }

      return $result;
   }
}