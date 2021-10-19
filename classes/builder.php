<?php


/* Builder */

class  Builder
{

  public static function Header()
  {

    header('Content-Type: text/html; charset=ISO-8859-1; <meta name="viewport" content="width=device-width, initial-scale=1">');
    echo '<title>Martingale v. 0.2</title>';


    echo '
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

  public static function spawnFixtureForm($db, $fromDate, $toDate, $league, $source = 'API', $idM = '', $year = 2021)
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
    if ($totalResponses == 0) {
      $fromDateF = date('d-m-Y', strtotime($fromDate));
      $toDateF =  date('d-m-Y', strtotime($toDate));
      $leagueName = $db->getLeagues('empty',$league);
      $msg = "Non ci sono partite di {$leagueName} nel periodo che va dal {$fromDateF} al {$toDateF}";
      Builder::javaAlert('Attenzione', $msg, 'index.php');
      //TODO: gestione messaggio errore per spawn con sorgente DB
    } else {
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
      builder::javaAlert('Partite salvate', $msg, 'index.php');
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
                    <h2 class='text-center'>MATCH FINDER</h2>
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
            
                <label class= 'form-label' for='league'>Scegli una competizione:</label>
                <select class='form-control' name='league' id='league'>
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
  
      $url = "fixtures.php?fromDate={$fromDate}&toDate={$toDate}&league={$league}";

      Builder::move_to($url);
  
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
              src="asset/RRLogo.png"
              width="75"
              alt=""
              loading="lazy"
            />
          </a> 
          <!-- Left links -->
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Ricerca Partite</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="martingale.php">Martingale</a>
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

    function spawnFixturesRows($idM,$conn) {

       $sql = "SELECT * FROM martingale_fixturesV WHERE id_m = '{$idM}'";
       $return = '';
       $rows = $conn->query($sql);
       while ($row = $rows->fetch_assoc()) {
         $return .= "
         <TR> 
            <TD><IMG SRC=\"{$row['homelogo']}\" width = 50></td>
            <TD>{$row['description']}</td>
            <TD><IMG SRC=\"{$row['awaylogo']}\" width = 50></td>
            <TD>Tasto1</td>
            <TD>Tasto2</td>
         </TR>              
         ";
       }

       return $return;

    }

    $conn = $db->getMartDBonn(); 
    $today = date('y-m-d');
    $martingales = $conn->query("SELECT * from martingale WHERE date >= '{$today}'");
    $return = "<table>";

    if ($martingales->num_rows > 0) {
      
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
          <table class="table table-hover align-middle text-center table-striped mx-auto" style="width:80%;">
            <thead>
              <tr>
                <th>ID</th>
                <th>Descrizione</th>
                <th>Data</th>
                <th colspan=2 style="width:10%">Modifica</th> 
              </tr>
            </thead>
            <tbody>
      
      '; 

      
      while($martingale = $martingales->fetch_assoc()) {
        
        $id_m = $martingale['id_m'];
        $description = $martingale['description'];
        $date_f = Builder::showFormatDate($martingale['date']);
        
        $fixtureRows = spawnFixturesRows($id_m,$conn);
        $return .= "
              <tr>
                <td>{$id_m}</td>
                <td>{$description}</td>
                <td>{$date_f}</td>
                <td><A HREF='working/addFixtures2Martingale.php?id_m={$id_m}'><IMG SRC='asset/addfixt.ico' width='25'></A></td>
                <td><A href='working/deleteitem.php?iditem={$id_m}&returnpage=martingale&itemtype=martingale' onclick='confirm_delete()'><IMG SRC='asset/delete.png' width='25'></A></td>
              </tr>
              <TR><TD colspan = 5>
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
    
    } else {$return = "Nun ce so martingale.";}

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

}
