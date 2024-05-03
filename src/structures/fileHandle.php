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
