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
            $this->destAddr = $destAddr; // destination address -> absolute path
            print_r("here is dest addr ".$this->destAddr);
            $this->sizeCap = $sizeCap;
        }

        private function fileSize(){
            if($this->srcFile['size'] > $this->sizeCap){
                throw new Exception("File size too large".$this->sizeCap,413);
            }
        }

        private function ext_Chk(){
            $contType= substr($this->srcFile['type'],0,stripos($this->srcFile['type'],"/"));
            print_r("here is cont type ".$contType);
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

            if (in_array($realExt,$extArray)) {
                echo "jpeg is in the array.";
            } else {
                throw new Exception("Invalid file type",403);
            }
            
    
        }

        function commitUpload(){
      
            $this->fileSize();
        
            $this->ext_Chk();

            // $directoryPath = ROOT. '/homestayImg';  // Absolute path to the directory.

            // Check if the directory exists, if not, create it.
            if (!is_dir( $this->destAddr)) {
                if (!mkdir( $this->destAddr, 0755, true)) {  
                    throw new Exception("Failed to create directory", 500);
                }
            }

            // Create the full path for the file to be moved to
            $fullFilePath =  $this->destAddr . '/' . basename($this->srcFile['name']); 

            // Move the uploaded file to the newly created directory
            if (!move_uploaded_file($this->srcFile['tmp_name'], $fullFilePath)) {
                throw new Exception("File upload failed", 500);
            }

            print_r("here is src name".$this->srcFile['name']);
            print_r("here is dest addr ".$this->destAddr."\n");
            //how to use substr -> substr("Hello",1,3) -> "ell"
            $destAddr = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_ADDR"].substr($_SERVER["SCRIPT_NAME"],0,stripos($_SERVER["SCRIPT_NAME"],"index.php")).substr(HOMESTAY_IMG_FOLDER,1)."/".$this->srcFile['name']; 
            
            return  $destAddr ;
        }

    }
    // current seems the route is not properly -> C:\xampp\htdocs\webdev6\php_server_BookMyHomestay\src//homestayImg\father_and_son.webp

    // C:\xampp\htdocs\webdev6\php_server_BookMyHomestay\src//homestayImg