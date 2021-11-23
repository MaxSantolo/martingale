<?php


/* Builder */

class  Builder
{

  public static function Header()
  {

    header('Content-Type: text/html; charset=ISO-8859-1; <meta name="viewport" content="width=device-width, initial-scale=1">');
    echo '<title>Martingale v. 0.2</title>';


    echo '

            <!-- Smartphone implementation -->
            <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />  

            <!-- Font Awesome -->
            <link
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
              rel="stylesheet"
            />

            <!-- Google Fonts -->
            <link
              href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
              rel="stylesheet"
            />
            <link 
              href="https://fonts.googleapis.com/css?family=Archivo+Black&display=swap" 
              rel="stylesheet">
            
            <!-- MDB -->
            <link
              href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.css"
              rel="stylesheet"
            
            />
            <!-- MDB -->
            <script
              type="text/javascript"
              src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.js"
            ></script>  



            ';
    
            /*
                        <!-- CSS -->
            <link
              type="text/css"
              href="css/baseline2.css"
              rel="stylesheet"
            />

          
            */
  }

  public static function spawnFixtureForm($db, $fromDate, $toDate, $league, $source = 'API', $destination = 'index.php', $idM = '', $year = 2021)
  {

    function addRow($fixtureValuesJSON,$teamLogoHome,$fixtureText,$teamLogoAway,$date)
    {
      return "
                <TR>
                    <TD ><INPUT TYPE='checkbox' VALUE='{$fixtureValuesJSON}' NAME='fixt[]'></TD>
                    <TD ><IMG SRC='{$teamLogoHome}' WIDTH='30%'></TD>
                    <TD class='form-outline mb-4 relative-pos-center text_bold' style='font-family:Verdana'>
                      {$fixtureText}
                    </TD>
                    <TD><IMG SRC='{$teamLogoAway}' WIDTH='30%'></TD>
                    <TD class='form-outline mb-4 relative-pos-center text_bold' style='font-family:Verdana'>{$date}</TD>
                </TR>
            ";
    }

    /* Istancing */
    
    if ($source == 'API') {
      $test = $db->getFixtures(2021, $league, $fromDate, $toDate);
      $response = json_decode($test, true);
      $totalResponses = count($response['response']);
      $title = 'PARTITE NEL PERIODO SELEZIONATO';
      $buttonText = 'SALVA LE PARTITE';
      $buttonCommand = 'save';
    } else {
      $conn = $db->getMartDBonn();
      $sql = "SELECT * FROM fixtures WHERE date_f >= '{$fromDate}'";
      $response = $conn->query($sql);
      $totalResponses = $response->num_rows;
      $title = 'PARTITE DISPONIBILI PER LA MARTINGALA';
      $buttonText = 'AGGIUNGI LE PARTITE ALLA MARTINGALA';
      $buttonCommand = 'add';
    }

    /* Response iterator */
    if(!InputControl::spawnFixtureFormCTRL($totalResponses, $fromDate, $toDate, $league, $db, $destination)){
   
        /* Title */
      $return =  "
                  <form name='{$buttonCommand}' action='' method='POST'>
                    <DIV class='container-sm'>   
                      <TABLE CLASS='table table-hover align-middle text-center'>
                        <THEAD>
                          <TR>
                            <TH COLSPAN = 5>
                            <h3 style='font-family:Verdana' class='text_bold_2'>{$title}</h3></TH>
                          </TR>
                        </THEAD>
                  ";
      
      if ($source == 'API') { 
          foreach (range(0, $totalResponses - 1) as $i) {

              /* Istancing */
              $teamHome = $response['response'][$i]['teams']['home']['name'];
              $teamAway = $response['response'][$i]['teams']['away']['name'];
              $teamLogoHome = $response['response'][$i]['teams']['home']['logo'];
              $teamLogoAway = $response['response'][$i]['teams']['away']['logo'];
              $timestamp = $response['response'][$i]['fixture']['timestamp'];
              $date = date('d-m-Y H:i', $timestamp);
              $dt = new DateTime($date, new DateTimeZone('UTC'));
              $dt->setTimezone(new DateTimeZone('Europe/Rome'));
              $fixtureText = $teamHome . ' - ' . $teamAway;
              $fixtureValuesJSON = json_encode(array($fixtureText, $teamLogoHome, $teamLogoAway, $date));
            
              /* Appending row */
              print("eccone una");
              $return .= addRow($fixtureValuesJSON,$teamLogoHome,$fixtureText,$teamLogoAway,$date);
          }
      }        
      else { 

        while($fixture = $response->fetch_assoc()) {
            $teamLogoHome = $fixture['homelogo'];
            $fixtureText = $fixture['description'];
            $teamLogoAway = $fixture['awaylogo'];
            $date = $fixture['date_f'];
            $fixtureID = $fixture['id_f'];
            $fixtureValuesJSON = json_encode(array($fixtureID));

            /* Appending row */
            $return .= addRow($fixtureValuesJSON,$teamLogoHome,$fixtureText,$teamLogoAway,$date);
        }
      }    

        /* Appending save button */
      $return .= "
                  <TR>
                    <TD COLSPAN=5>
                      <button type='submit' name='{$buttonCommand}' class='btn btn-primary btn-block button_css'>{$buttonText}</button>
                    </TD>
                  </TR>
              </TABLE>
            </FORM>
          </DIV>";
    }

    /* Form action */
    if (isset($_POST['save'])) {
      $fixt = $_POST['fixt'];
      $counter = count($fixt) - 1;
      foreach (range(0, $counter) as $i) {
        $nameFixt = json_decode($fixt[$i])[0];
        $homeLogo = json_decode($fixt[$i])[1];
        $awayLogo = json_decode($fixt[$i])[2];
        $date = json_decode($fixt[$i])[3];
        $dateDB = date('Y-m-d H:i:s', strtotime($date));
        $db->addFixtures($homeLogo, $awayLogo, $nameFixt, $dateDB);
      }
      $partite = $counter + 1;
      $counter == 0 ? $msg = "Ho agggiunto {$partite} partita al database" : $msg = "Ho agggiunto {$partite} partite al database";
      builder::javaAlert('Partite salvate', $msg, $destination);
    }

    if (isset($_POST['add'])) {

      $fixtIDs = $_POST['fixt'];
      $counter = count($fixtIDs) - 1;
      foreach (range(0, $counter) as $i) {
        $fixtID = json_decode($fixtIDs[$i])[0];
        $db->addFixtureDB($fixtID,$idM);
      }
      $partite = $counter + 1;
      $counter == 0 ? $msg = "Ho agggiunto {$partite} partita alla martingala" : $msg = "Ho agggiunto {$partite} partite alla martingala";
      builder::javaAlert('Partite aggiunte', $msg, '../martingale.php');

    }
    return $return;
  }

  public static function javaAlert($title, $message, $url)
  {

    echo '
      
      <div class="modal-dialog" tabindex="-1">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">' . $title . '</h5>
          </div>
          <div class="modal-body">' . $message . '</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="window.location.href=\'' . $url . '\'">
              Close
            </button>
          </div>
        </div>
      </div>
      </div>
      
      ';
  }

  //spawn directly the associated html
  public static function spawnSearchForm($leaguesFormOptions) {

    echo  "
        <DIV class='container mt-5'>
            
            <!--Titolo-->

            <form name='cerca' action='' method='POST' class='container shadow-2-strong p-2' style='width: 400px' id='grad_diagonal_red_reddark'>
            <div class='form-outline mb-4'>
                    <h2 class='text-center'>RICERCA PARTITE</h2>
            </div>

            <!-- Inizio input -->
                <div class='form-outline mb-4'>
                    <label class='form-label' for='fromDate'>Inizio</label>    
                    <input class='form-control' type='date' id='fromDate' name='fromDate'/>
                </div>
              <!-- Fine input -->
                <div class='form-outline mb-4'>
                    <label class='form-label' >Fine</label>      
                    <input class='form-control' type='date' id='toDate' name='toDate'/>
                </div>
              
                <div class='form-outline mb-4'>
            
                <!-- <label class= 'form-label' for='league'>Scegli una competizione:</label> -->
                <select class='form-control' name='league' id='league'>
                <option value = '-1' SELECTED>Selezione la competizione</option>
                {$leaguesFormOptions}
                </select> 
            </div>
                <!-- Submit button -->
                <button type='submit' name='cerca' class='btn btn-primary btn-block button_css'>Cerca</button>
            </form>
            
        </DIV>
    
    
    ";
    
    if (isset($_POST['cerca'])) {

      $fromDate = $_POST['fromDate'];
      $toDate = $_POST['toDate'];
      $league = $_POST['league'];
      $err = InputControl::spawnSearchFormCTRL($fromDate, $toDate, $league);

      if ($err == '') {
        $url = "fixtures.php?fromDate={$fromDate}&toDate={$toDate}&league={$league}";
        Builder::move_to($url);
      } else Builder::javaAlert('Errore',$err,'');
     
  
  }


  }

  public static function Navbar() {

  
   echo '<!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light" id="grad_purple_to_blue_dissolve">
      <!-- Container wrapper -->
      <div class="container-fluid">
        
    
        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- Navbar brand -->
          <a class="navbar-brand mt-2 mt-lg-0" href="#">
            <img
              src="https://service.radioradio.it/martingale/asset/RRLogo.png"
              width="75"
              alt=""
              loading="lazy"
            />
          </a> 
          <!-- Left links -->
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="https://service.radioradio.it/martingale/index.php">Ricerca Partite</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://service.radioradio.it/martingale/martingale.php">Martingale</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Impostazioni</a>
            </li>
          </ul>
          <!-- Left links -->
        </div>
        <!-- Collapsible wrapper -->
    
        <!-- Right elements -->
        <div class="d-flex align-items-center"></div>
          
        <!-- Right elements -->
      </div>
      <!-- Container wrapper -->
    </nav>
    <!-- Navbar -->';

  }
  

  public static function move_to($url) {
    echo "<script type='text/javascript'>document.location.href='{$url}';</script>";
    echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $url . '">';
  }

  public static function spawnMartingale($db) {

    function spawnFixturesRows($idM,$conn,$db) {

       $sql = "SELECT * FROM martingale_fixturesV WHERE id_m = '{$idM}'";
       $return = '';
       $rows = $conn->query($sql);
       while ($row = $rows->fetch_assoc()) {
        
         $idF = $row['id_f'];
         $predictionsRows = spawnPredictionRows($idF,$conn,$db);
         $return .= "
         <TR> 
            <TD><IMG SRC=\"{$row['homelogo']}\" width = 50></td>
            <TD>{$row['description']}</td>
            <TD><IMG SRC=\"{$row['awaylogo']}\" width = 50></td>
            <TD><A href='working/deleteitem.php?item0=f&iditem0={$idF}&item1=m&iditem1={$idM}&returnpage=martingale&itemtype=rel_martingale_fixtures' onclick='confirm_delete()'><IMG SRC='asset/delete.png' width='20'></A></td>
         </TR>
         <TR><TD colspan = 4>
         <table class='table table-hover text-center table-striped mx-auto align-middle' style='width:50%;'>
              <tbody>
                  {$predictionsRows}
              </tbody>  
        </table>        
        </TD></TR>  
         ";
       }

       return $return;

    }

    function spawnPredictionRows($idF,$conn,$db) {

      $sql = "SELECT id_o, id_pt FROM predictions WHERE id_f = {$idF}";
      $return = "";
      $rows = $conn->query($sql);

      while ($row = $rows->fetch_assoc()) {
        
        $opinionista = $db->getOpinionisti($row['id_o']);
        $prediction = $db->getPredictionValue($row['id_pt']);
        $return .= "
        <TR> 
           <TD>{$opinionista}</td>
           <TD>{$prediction}</td>
        </TR>              
        ";
      }

      return $return;

    }

    $conn = $db->getMartDBonn(); 
    $today = date('y-m-d');
    $martingales = $conn->query("SELECT * from martingale WHERE date >= '{$today}'");
    $return = "<table>";
    $msg = InputControl::spawnMartingaleCTRL($martingales->num_rows);
    if ($msg == '') {
      
      // JSS Functions
      $return .= '
        <script type="text/javascript">
        function confirm_delete() {
        return confirm("Sei sicuro?");
        }
        </script>
      ';
      
      // HTML Spawn
      $return .= '
          <HR>
          <div class="container-sm">
          <h1 class="text-center">Martingale Future</h1>
          <table class="table table-hover align-middle text-center table-striped mx-auto" style="width:90%;">
            <thead>
              <tr>
                <th>ID</th>
                <th>Descrizione</th>
                <th>Data</th>
                <th colspan=3 style="width:10%">Modifica</th> 
              </tr>
            </thead>
            <tbody>
      
      '; 

      
      while($martingale = $martingales->fetch_assoc()) {
        
        $id_m = $martingale['id_m'];
        $description = $martingale['description'];
        $date_f = Builder::showFormatDate($martingale['date']);
        $today2Table = date('Y-m-d H:i:s', strtotime($martingale['date']));
        
        $fixtureRows = spawnFixturesRows($id_m,$conn,$db);
        
        $return .= "
              <tr>
                <td>{$id_m}</td>
                <td>{$description}</td>
                <td>{$date_f}</td>
                <td><A HREF='working/addFixtures2Martingale.php?id_m={$id_m}&today={$today2Table}'><IMG SRC='asset/addfixt.ico' width='25'></A></td>
                <td><A HREF='working/addPredictions2MartingaleFixtures.php?id_m={$id_m}'><IMG SRC='asset/pred.png' width='25'></A></td>
                <td><A href='working/deleteitem.php?item0=m&iditem0={$id_m}&iditem1=&returnpage=martingale&itemtype=martingale' onclick='confirm_delete()'><IMG SRC='asset/delete.png' width='25'></A></td>
              </tr>
              <TR><TD colspan = 6>
              <table class='table table-hover text-center table-striped mx-auto align-middle' style='width:90%;'>
                <tbody>
                    {$fixtureRows}
                </tbody>  
              </table>  
              
              </TD></TR>
            
        ";
        }

        $return .= '
            </tbody>
          </table>
        </div>
        ';
    
    } else {$return = $msg;}

    echo $return;

  }

  public static function spawnNewMartingaleForm($db) {

    echo  "
    <DIV class='container mt-5 mb-5'>
        
        <!--Titolo-->

        <form name='addMartingale' action='' method='POST' class='container shadow-2-strong p-2' style='width: 400px' id='grad_diagonal_red_reddark'>
        <div class='form-outline mb-4'>
                <h2 class='text-center'>AGGIUNGI MARTINGALA</h2>
        </div>

        <!-- Inizio input -->
            <div class='form-outline mb-4'>
                <label class='form-label' for='description'>Nome</label>    
                <input class='form-control' type='text' id='description' name='description'/>
            </div>
          <!-- Fine input -->
            <div class='form-outline mb-4'>
                <label class='form-label' for='date'>Data</label>      
                <input class='form-control' type='date' id='date' name='date'/>
            </div>
          
            <!-- Submit button -->
            <button type='submit' name='addMartingale' class='btn btn-primary btn-block button_css'>Salva</button>
        </form>
        
    </DIV>


    ";

    if (isset($_POST['addMartingale'])) {

      $date = $_POST['date'];
      $date_f = date('d-m-Y',strtotime($_POST['date']));

     

      $_POST['description'] == '' ? $descr = "Martingale del {$date_f}" : $descr = $_POST['description'];

      $db->addMartingale($descr,$date);

    }
  }

  public static function showFormatDate($date) {

    $date_f = date('d-m-Y',strtotime($date));
    return $date_f;

  }



  public static function spawnOpinionistiForm($opinionistiFormOptions, $db) {
    
  //ISTANCING 

    $return = '';
    $idM = $_GET['id_m'];

    //SPAWN FORM OPINIONISTI


    $return .=  "
        <DIV class='container mt-5'>
            
            <!--Titolo-->

            <form name='cerca' action='' method='POST' class='container shadow-2-strong p-2' style='width: 800px' id='grad_diagonal_red_reddark'>
            <div class='form-outline mb-4'>
                    <h2 class='text-center'>OPINIONISTI</h2>
            </div>
              
            <div class='form-outline mb-4'>
                <!-- Opinionisti Search -->
                <!-- <label class= 'form-label' for='opinionisti'>Scegli un opinionista:</label> -->
                <select class='form-control' name='opinionisti' id='opinionisti'>
                  <option value='-1' SELECTED>Selezionare un opinionista</option>
                  {$opinionistiFormOptions}
                </select> 

            </div>

            <DIV class='container-sm'>
            <h3 class='text-center'>PARTITE DELLA MARTINGALA</h3>
            
            <TR><TD colspan = 5>
            <table class='table table-hover text-center table-striped mx-auto align-middle' style='width:90%;'>
              <tbody>" .
                        Builder::fixturesList4Predictions($idM,$db)
                       .
              "</tbody>  
            </table>  
            </TD></TR>
            </DIV>
                <!-- Submit button -->
                <button type='submit' name='salvaprevisione' class='btn btn-primary btn-block button_css'>Salva la previsione</button>
                <button type='submit' name='exit' class='btn btn-primary btn-block button_css'>Esci</button>
            </form>
      
        </DIV>
    ";
    

    
    //TODO: isPOST salvare i dati e reindirizzare

    if (isset($_POST['salvaprevisione'])) {

      

      $idO = $_POST['opinionisti'];
      $fixt = $_POST['fixt'];
      $opts = $_POST['option'];
      
      if (InputControl::spawnOpinionistiFormCTRL($opts,$idO)[0]) {
      $counter = count($fixt) - 1;
      
      foreach (range(0, $counter) as $i) {
        $idF = $fixt[$i];
        $idPT = $opts[$i];
        $db->insertPrediction($idO,$idF,$idPT);
        
      } 
    }

    else {
      InputControl::spawnOpinionistiFormCTRL($opts,$idO, true);
    }

    } 

    if (isset($_POST['exit'])) {
      Builder::move_to('https://service.radioradio.it/martingale/martingale.php');
    }


    //CHECK SELECTED OPINIONISTA
    return $return;

    


  }

  public static function fixturesList4Predictions($idM,$db) {

    $conn = $db->getMartDBonn();
    $sql = "SELECT * FROM fixtures JOIN rel_martingale_fixtures ON fixtures.id_f = rel_martingale_fixtures.id_f WHERE  rel_martingale_fixtures.id_m = {$idM}";
    
    $fixtures = $conn->query($sql);
    $return = "";
    
    //TODO: controllo se ci sono partite altrimenti li mando alla schermata ...
    while($fixture = $fixtures->fetch_assoc()) {

      $predictionOptions = Builder::spawnSelectOptions($db,'predictiontype','type','id_pt',4,'REQUIRED');
      $return .= "           
          <TR> 
            <TD><INPUT TYPE='text' VALUE='{$fixture['id_f']}' name=fixt[] hidden><IMG SRC='{$fixture['homelogo']}' WIDTH=20></td>
            <TD>{$fixture['description']}</td>
            <TD><IMG SRC='{$fixture['awaylogo']}' WIDTH=20></td>
            <TD>{$predictionOptions}</td>
          </TR> ";

    }

    return $return;

  }

  public static function spawnSelectOptions($db,$table,$valueFieldName,$idFieldName,$defaultValue,$required='')  {

    $return = '';
    $conn = $db->getMartDBonn();
    $sql = "SELECT {$table}.{$idFieldName}, {$table}.{$valueFieldName} FROM {$table}";
    $data = $conn->query($sql);

    $return = "<SELECT CLASS='form-control' name=option[] {$required}>";

    while($dt = $data->fetch_assoc()) {

      $defaultValue == $dt[$idFieldName] ? $default = 'selected' : $default = '';
      $return .= "
          <OPTION VALUE='{$dt[$idFieldName]}' {$default}>{$dt[$valueFieldName]}</OPTION>
      ";


    }

    $return .= "</SELECT>";
    return $return;
    
  }

}
