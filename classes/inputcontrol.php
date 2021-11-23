<?php


class InputControl
{

//index.php CONTROL

    public static function getLeaguesCTRL($command){    //Only 1 error handable(è ridondante ma per completezza le metto tutte)
        $err= '';

        if ($command == 'empty') {
            $err .= 'La combinazione di parametri della funzione è inconcludente';
        }

        return $err ;
    }


    public static function spawnSearchFormCTRL(&$fromDate, &$toDate, $league){
      $err = '';

      if (strtotime($fromDate) == 0 && strtotime($toDate) !=0) : $fromDate = date('Y-m-d'); 
      elseif ((strtotime($fromDate) != 0 && strtotime($toDate) == 0)) : $toDate = $fromDate;
      elseif ($fromDate == 0 && $toDate == 0) : $fromDate = $toDate = date('Y-m-d'); 
      endif;

      if (strtotime($fromDate) > strtotime($toDate)) {$err .= 'La data di fine periodo &egrave precedente a quella di inizio.<BR>' . PHP_EOL;}
      if ( $league == '-1') {$err .= 'Devi selezionare un campionato';}

      return $err;
    }


//fixtures.php CONTROL
    
    public static function spawnFixtureFormCTRL($totalResponses, $fromDate, $toDate, $league, $db, $destination){
      if ($totalResponses == 0) {
        $fromDateF = date('d-m-Y', strtotime($fromDate));
        $toDateF =  date('d-m-Y', strtotime($toDate));
        $leagueName = $db->getLeagues('empty',$league);
        $toDate != '' ? $msg = "Non ci sono partite di {$leagueName} nel periodo che va dal {$fromDateF} al {$toDateF}" : $msg = "Non ci sono partite salvate oltre il {$fromDateF}"; //usato nel caso dell'aggiunta di partite a martingala, pagina: addFixtures2Martingale
        Builder::javaAlert('Attenzione', $msg, $destination);
        return true;
        //TODO: gestione messaggio errore per spawn con sorgente DB
      }
      return false;
    }



//martinagle.php CONTROL
    
    public static function addMartingaleCTRL($num_rows, $date, $today) {           //DB funct called from spawnNewMartingaleForm in Builder 
        $err = '';

        $flag = ($num_rows == 0 && $date >= $today);

        if (!$flag) { 
          $num_rows > 0 ? $err = 'Martingala gi&agrave presente.<BR>' : $err = '';
          $date < $today ? $err .= 'Flusso canalizzatore rotto.' : $err;
        }

        if ($err != '') Builder::javaAlert('Errore',$err,'martingale.php');

        return $err;
    }
    


    public static function spawnMartingaleCTRL($num_rows) {

        if($num_rows > 0) return '';
        else return 'Nun ce so martingale';

    }



//addFixtures2Martingale.php  -->  spawnFixtureForm() already handled in fixtures.php section



//addPredictions2MartingaleFixtures.php

    public static function spawnOpinionistiFormCTRL($predictions,$idO, $err = false){

      $bool = true;
      $msg = '';

      if ($idO == -1) {
        $msg .= 'Selezionare un opinionista prima di salvare la martingala.' . PHP_EOL;
        $bool = false;
      } 

      //4 perchè cerchiamo N.D.
      if (in_array(4,$predictions)) {
        $msg .= 'Mancano delle previsioni.';
        $bool = false;
      }

      if($err) return Builder::javaAlert('Errore',$msg,'');

      return array($bool,$msg);

    }

}



