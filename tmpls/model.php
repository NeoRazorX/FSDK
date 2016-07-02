<?php

/*
 * @author XXXX      xxx@xxx.xx
 * @copyright 2016, XXXX. All Rights Reserved.
 */


/**
 * Description of holamundo
 *
 * @author XXX
 */
class holamundo extends fs_model
{
   public $id;
   public $item1;
   public $item2;
   public $item3;
   
   public function __construct($e = FALSE)
   {
      parent::__construct('holamundo_tbl', 'plugins/holamundo');
      if($e)
      {
         $this->item1 = $e['item1'];
         $this->item2 = $e['item2'];
         $this->item3 = $e['item3'];
      }
      else
      {
         $this->item1 = NULL;
         $this->item2 = NULL;
         $this->item3 = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET item1 = ".$this->var2str($this->item1)
                 .", item2 = ".$this->var2str($this->item2)
                 .", item3 = ".$this->var2str($this->item3)
                 ."  WHERE id = ".$this->var2str($this->id).";";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (item1, item2, item3) VALUES "
                 . "(".$this->var2str($this->item1)
                 . ",".$this->var2str($this->item2)
                 . ",".$this->var2str($this->item3)
                 .");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = ".$this->var2str($this->id).';');
   }
   
   public function all()
   {
      $elist = array();
      $sql = "SELECT * FROM ".$this->table_name." ORDER BY id ASC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $elist[] = new holamundo($d);
         }
      }
      
      return $elist;
   }
}
