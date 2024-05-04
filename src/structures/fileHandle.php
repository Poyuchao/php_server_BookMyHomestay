<?php

class File{
        private $src_addr;
        private $fileAddr;
        function __construct($src_addr)
        {
            $this->src_addr = $src_addr;
        }
        function readFile($fileName){
            $this->fileAddr = $this->src_addr."/$fileName";
            if(file_exists($this->src_addr."/$fileName")){
                $file = fopen($this->fileAddr,"r");
                $data = fread($file,filesize($this->fileAddr));
                fclose($file);
                return $data;
            }else{
                return false;
            }

        }

        function listFiles()
        {
            $files = scandir($this->src_addr); // get all files in the directory
            $files = array_diff($files, array(".", "..")); 
            $files = array_values($files);
            $files = implode("\n", $files); //  dispaly the files name line by line
            return $files;
        }

        function writeFile($fileName,$data,$hardWrite = false){
            $this->fileAddr = $this->src_addr."/$fileName";
            $writeFlag = "w";
            if(file_exists($this->fileAddr) && !$hardWrite){
                $extension = explode(".",$fileName)[1];
                    switch(strtolower($extension)){
                        case "txt":
                            $writeFlag = "a";
                        break;
                        case "json":
                            $this->writeJSON($fileName,$data);
                            return 0;
                        break;
                    }
            }
                $file = fopen($this->fileAddr,$writeFlag);
                fwrite($file,(is_array($data))?json_encode([$data]):$data);
                fclose($file);
        }
        private function writeJSON($fileName,$data){
            $prevData = json_decode($this->readFile($fileName));
            array_push($prevData,$data); // add the new data to the array
            $this->writeFile($fileName,json_encode($prevData),true);
        }
    }



    class fileUpload {
        private $srcFile;
        private $destAddr;
        private $sizeCap;

        function __construct($srcFile,$destAddr,$sizeCap)
        {
            $this->srcFile = $srcFile;
            $this->destAddr = $destAddr."/".$this->srcFile['name']; // get the file name
            $this->sizeCap = $sizeCap;
        }

        private function fileSize(){
            if($this->srcFile['size'] > $this->sizeCap){
                throw new Exception("File size too large".$this->sizeCap,413);
            }
        }

        private function ext_Chk(){
            $contType= substr($this->srcFile['type'],0,stripos($this->srcFile['type'],"/"));
            switch($contType){
                case "image":
                    $extArray = ["jpeg","jpg","png","bmp","webp"];
                break;
                case "application":
                    $extArray = ["json"];
                break;
                default:
                    throw new Exception("Invalid file type",403);
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE); // get the file type
            $realExt=basename($finfo->file($this->srcFile['tmp_name'])); // get the file extension

            if(!(false==array_search($realExt,$extArray))){ // check if the file type is in the array
                return true;
            }
            throw new Exception("Invalid file type",403);
        }

        function commitUpload(){
      
            $this->fileSize();
            $this->ext_Chk();
            if(!move_uploaded_file($this->srcFile['tmp_name'],$this->destAddr)){
                throw new Exception("File upload failed",500);
            }
            
            //how to use substr -> substr("Hello",1,3) -> "ell"
            $destAddr = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_ADDR"].substr($_SERVER["SCRIPT_NAME"],0,stripos($_SERVER["SCRIPT_NAME"],"classSolution.php")).substr($this->destAddr,3); 
            
            return  $destAddr ;
        }

    }
