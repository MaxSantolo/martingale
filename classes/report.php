<?php

class Report
{

    function __construct()
    {
        $this->dir_path = $_SERVER['DOCUMENT_ROOT'] . 'martingale/logs/';
        $this->file_path = $this->dir_path . 'test0.txt';

        $this->today = date('d/m/Y h:i:s');
        $this->creation;

        $this->file;
        $this->file_data;
        $this->init_grammar = '1|000|0|' . $this->today . '|';  // EXISTING BIT-ROWS-COUNT-CREATION
        $this->actual_grammar = $this->init_grammar;
        $this->init_file();

        $this->max_rows = 200;

        

    }


    function init_file()
    {   
        $last_line = $this->last_line();

        $this->file_data = explode('|', $last_line);

        if( $this->file_data[0] == '1' ){
            $this->filecount = $this->file_data[2];
            $this->creation = $this->file_data[3];
            $this->actual_grammar = $last_line;
        }
        
        else{
            $this->file = fopen($this->file_path, "a+");
            
            fwrite($this->file, $this->init_grammar);
            $this->file_data = explode('|', $this->init_grammar);

            fclose($this->file);

            $this->creation = $this->today;
        }
    }


    function end_file($string, $opcode)
    {   
        
        $tarname = str_replace('/','.',$this->file_data[3]);
        $tarname = str_replace(' ','_',$tarname);
        $tarname = str_replace(':','.',$tarname);
        $tarname .= '_' . $this->file_data[2] . '.log.tar.gz';

        $cmd = "tar cvzf logs/" .  $tarname . " logs/test0.txt";
        print($cmd);
        system($cmd);
        /* Re-instancing the file */

        $this->creation = date('d/m/Y h:i:s');

        $this->blank_file();

        $this->file_data[1] = 0;
        $this->file_data[2] = $this->file_data[2] + 1;
        $this->file_data[3] = $this->creation;
        $this->actual_grammar = $this->string_file_data();
        
        $this->file = fopen($this->file_path, "a+");

        /*
        $this->actual_grammar = '1|000|' . $this->file_data[2]+1 . '|' . $this->creation . '|';  // EXISTING BIT-ROWS-COUNT-CREATION
        $this->file_data = explode('|', $this->actual_grammar);
        */

        fwrite($this->file, $this->actual_grammar);
                
        fclose($this->file);

        $this->add_event($string, $opcode);
    }


    function add_event($string, $opcode){   //OpCode= Delete-Insert-Error
        if( (int)$this->file_data[1] < $this->max_rows ){               
            
            $this->file = fopen($this->file_path, "a+");

            $stat = fstat($this->file);
            ftruncate($this->file , $stat['size'] - strlen($this->actual_grammar));

            $string = '[' . $opcode . '][' . date('d/m/Y h:i:s') . '] ' . $string;  
            fwrite($this->file, $string . PHP_EOL);

            $this->file_data[1] = (int)$this->file_data[1] + 1;

            $new_header = $this->string_file_data();
            fwrite($this->file, $new_header);

            fclose($this->file);
            
            $this->actual_grammar = $new_header;

        }
        else{
            $this->end_file($string, $opcode);
        }
    }


    function last_line(){   
        $data = file($this->file_path);
        $line = $data[count($data)-1];
        return $line;      
        /*
        fseek($this->file, -1, SEEK_END); 
        $pos = ftell($this->file);
        $last_line = "";
        // Loop backword util EOL is found
        while((($c = fgetc($this->file)) != PHP_EOL) && ($pos > 0)) {
            $LastLine = $c.$last_line;
            fseek($this->file, $pos--);
        }

        return $last_line;
        */

        /*
        $last_line= '';
        $cursor = -1;

        fseek($this->file, $cursor, SEEK_END);
        $char = fgetc($this->file);


        while ($char === "\n" || $char === "\r") {
            fseek($this->file, $cursor--, SEEK_END);
            $char = fgetc($this->file);
        }

        while ($char !== false && $char !== "\n" && $char !== "\r") {
 
           $line = $char . $last_line;
            fseek($this->file, $cursor--, SEEK_END);
            $char = fgetc($this->file);
        }

        return $last_line;
        */
    }


    function string_file_data(){
        $row = '';
        if(strlen((int)$this->file_data[1]) == 1){
            $row = '00' . $this->file_data[1];
        }
        if(strlen((int)$this->file_data[1]) == 2){
            $row = '0' . $this->file_data[1];
        }
        if(strlen((int)$this->file_data[1]) == 3){
            $row = $this->file_data[1];
        }
      
        $string =  '1|' . $row . '|' . $this->file_data[2] . '|' .  $this->file_data[3] . '|';

        return $string;

    }

    function blank_file(){
        $this->file = fopen($this->file_path, "w");
        fclose($this->file);
    }

}
