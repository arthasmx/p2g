<?php
class Module_Core_Repository_Model_Zip extends Core_Model_Repository_Model{

  private $extensions		= array();

  // -----------------------
  /**
   * @param string (filePath)
   * @param string (array, string)
   * @param string (blacklist oder whitelist)
   * @return Boolean
   *
   * @desc Filter, you can use it as white or black -list
   */
  private function file_filter($file, $extensions = '*', $whatlist = 'whitelist'){
    if($extensions == '*'){
      $this->extensions = '*';
    }elseif(isset($extensions) && is_array($extensions) && COUNT($extensions)){
      $this->extensions = $extensions;
    }
    if(is_file($file)){
      $parts = pathinfo($file);
      if(is_array($this->extensions) && COUNT($this->extensions)){
        if($whatlist == 'whitelist'){
          if(in_array($parts["extension"], $this->extensions)){
            return TRUE;
          }
        }
        if($whatlist == 'blacklist'){
          if(!in_array($parts["extension"], $this->extensions)){
            return TRUE;
          }
        }
      }else{
        if($this->extensions == '*'){
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  // -----------------------
  /**
   * @param string (verzeichnis, directory)
   *
   * @desc Get a recursive tree list of a folder
   */
  private function get_iterator($dir){
    $get_dir =  new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), true);

    if(is_object($get_dir)){
      return $get_dir;
    }
    return FALSE;
  }

  // -----------------------
  /**
   * @param string $dir (which dir)
   * @param string $zipfile (name of zipfile)
   * @param string $pakto (pack to this dir)
   * @param string $extension '*' = wildcart, '.jpg'
   * @param string (whitlist||blacklist)
   * @param string (overwrite TRUE||FALSE)
   * @return Boolean
   * @desc Pack folders recursive
   */
  public function zip_files($dir, $zipfile, $pakto, $extensions = '*', $whatlist = 'whitelist', $overwrite = TRUE){
    if(!is_dir($pakto)){
      mkdir($pakto);
    }
    if(is_file($pakto.$zipfile) && $overwrite === TRUE){
      @unlink($pakto.$zipfile);
      $zipfile = $zipfile;
    }
    if(is_file($pakto.$zipfile) && $overwrite === FALSE){
      $zipfile = date('d.m.Y_').$zipfile;
    }
    if(is_file($pakto.$zipfile) && $overwrite === FALSE){
      $zipfile = date(time()).$zipfile;
    }
    $oZIP = new ZipArchive;
    $make = $oZIP->open($pakto.$zipfile, ZipArchive::CREATE);
    if($make === TRUE){
      $get_dir =  self::get_iterator($dir);
      if($get_dir !== FALSE){
        foreach($get_dir AS $key){
          if($get_dir->isDir()){
            $oZIP->addEmptyDir($get_dir->getPath());
          }else{
            if(self::file_filter($get_dir->getPath().'/'.$get_dir->getFilename(), $extensions, $whatlist) === TRUE){
              $oZIP->addFile($get_dir->getPath().'/'.$get_dir->getFilename(), $get_dir->getPath().'/'.$get_dir->getFilename());
            }
          }
        }

      }
    }
    $oZIP->close();
    if(is_file($pakto.$zipfile)){
      return TRUE;
    }
    return FALSE;
  }

  // -----------------------
  /**
   * @param string
   * @param string
   * @return Boolean
   *
   * @desc Unpack zip archive and overrides an existing archive
   */
  public function unzip($destination, $zipfile){
    if(!is_dir($destination)){
      mkdir($destination);
    }
    $zip = new ZipArchive;
    if ($zip->open($zipfile) === TRUE){
      $zip->extractTo($destination);
      $zip->close();
      @unlink($zipfile);
      return TRUE;
    }else{
      return FALSE;
    }
  }

  // -----------------------
}