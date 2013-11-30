<?php
class FolderUtils{
    var $sub_folder;
    var $dir;
    var $path;

    function __construct(){
        $this->sub_folder = false;
    }
    function __destruct(){
        if ($this->dir){
            closedir ($this->dir);
        }
    }
    public function close(){
        if ($this->dir){
            closedir ($this->dir);
            $this->dir = null;
        }
    }
    public function get_next_file ($ext){
        $file = "";
        if ($this->sub_folder !== false){
            $fl = $this->sub_folder->get_next_file($ext);
            if ($fl !== false){
                return $fl;
            } else {
                $this->sub_folder->close();
                $this->sub_folder = false;
            }
        }
        while ($file = readdir($this->dir)){
            if($file == "."||$file == ".."){
                continue;
            }
            if (is_dir ($this->path.$file)){
                $this->sub_folder = new FolderUtils();
                $this->sub_folder->set_path($this->path.$file);
                $file = $this->sub_folder->get_next_file($ext);
                if ($file == false){
                    $this->sub_folder->close();
                    $this->sub_folder = false;
                } else {
                    return $file;
                }
            } else {
                if (get_file_extension($file) == $ext)
                    return $this->path.$file;
            }
        }
        return false;
    }

    public function set_path ($path){
        $path = normalize_path ($path);
        $this->path = $path;
        $this->dir = opendir($this->path)
            or die ("cannot get files from $path");
    }
}
?>
