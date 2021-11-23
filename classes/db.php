<?php

/**
 * Created by PhpStorm.
 * User: msantolo
 * Date: 30/10/2018
 * Time: 10:46
 *
 *
 * Classe per la gestione dei database. Inizializza le stringhe di connessione e definisce i metodi di gestione dei dati.
 *
 *
 */


class DB
{

    //funzione di costruzione di base eseguita sul comando NEW
    function __construct()
    {
        $ini = parse_ini_file('dbparams.ini', true);
        $this->opinionistiServerIP = $ini['DB']['opinionistiServerIP'];
        $this->RRMySQLUserName = $ini['DB']['RRMySQLUserName'];
        $this->RRMySQLPassword = $ini['DB']['RRMySQLPassword'];
        $this->RRMySQLDB = $ini['DB']['RRMySQLDB'];
        $this->MartDB = $ini['DB']['MartMariaDB'];
        $this->MartDBUserName = $ini['DB']['MartDBUserName'];
        $this->MartDBPassword = $ini['DB']['MartDBPassword'];
        $this->MartDB = $ini['DB']['MartDB'];
    }

    //genera connessione al RRMySQL
    function getRRMySQLConn()
    {
        $servername = $this->opinionistiServerIP;
        $username = $this->RRMySQLUserName;
        $password = $this->RRMySQLPassword;
        $db = $this->RRMySQLDB;
        $conn = mysqli_connect($servername, $username, $password, $db) or die("Impossibile connettersi a: " . $db . " - " . mysqli_connect_error());
        return $conn;
    }

    //genera connessione ad MartDB
    function getMartDBonn()
    {
        $servername = $this->MartMariaDB;
        $username = $this->MartDBUserName;
        $password = $this->MartDBPassword;
        $db = $this->MartDB;
        $conn = mysqli_connect($servername, $username, $password, $db) or die("Impossibile connettersi a: " . $db . " - " . mysqli_connect_error());
        return $conn;
    }

    //distrugge connessione
    function dropConn($conn)
    {
        mysqli_close($conn);
    }

    //simple api-football call for fixtures
    function getFixtures($season, $league, $fromDate, $toDate)
    {

        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://v3.football.api-sports.io/fixtures?league={$league}&season={$season}&from={$fromDate}&to={$toDate}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-rapidapi-key: 729c2a87ff1576df584546fa37cbb7e8',
                'x-rapidapi-host: v3.football.api-sports.io'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    function addFixtures($homeLogo, $awayLogo, $description, $date)
    {

        $conn = $this->getMartDBonn();

        $doubleSql = "SELECT id_f FROM fixtures WHERE description = '{$description}' AND date_f = '{$date}'";
        //print($doubleSql);
        $doubles = $conn->query($doubleSql);
        //print($doubles->num_rows);


        if ($doubles->num_rows == 0) {

            $sql = "INSERT INTO fixtures (homelogo,awaylogo,description,date_f) VALUES ('{$homeLogo}','{$awayLogo}','{$description}','{$date}')";
            $conn->query($sql);
        }
    }

    function  getLeagues($command = 'empty', $code = -1)
    {

        //commands: 'options' - forms

        $conn = $this->getMartDBonn();

        $sql = "SELECT * FROM leagueCodes";
        $return = '';
        // 

        if ($code == -1) {

            $report = InputControl::getLeaguesCTRL($command);
            if ($report != '') return $report; 

            //if ($command == 'options') {

            $leagues = $conn->query($sql);
            while ($league = $leagues->fetch_assoc()) {

                $leagueCode = $league['id_league'];
                $leagueName = $league['description'];
                $return .= "<option value='{$leagueCode}'>$leagueName</option>\n";
            }
            //}
        } else {

            $sql .= " WHERE id_league = '{$code}'";
            $leagues = $conn->query($sql);
            $return = $leagues->fetch_assoc()['description'];
        }

        return $return;
    }

    function addMartingale($description,$date) {
                
        $today = date('Y-m-d');
        $conn = $this->getMartDBonn();

        $check = $conn->query("SELECT id_m FROM martingale WHERE description = '{$description}' AND date = '{$date}'");
       
        $err = InputControl::addMartingaleCTRL($check->num_rows, $date,$today);
       
        if ($err == '') { 
            $sql = "INSERT INTO martingale (description , date) VALUES ('{$description}','{$date}')";
            $conn->query($sql); 
        }
        
    }

    function addFixtureDB($idF,$idM) {
        $conn = $this->getMartDBonn();
        //check for duplicates
        $checkresult = $conn->query("SELECT id_rel_m_f FROM rel_martingale_fixtures WHERE id_m = '{$idM}' AND id_f = '{$idF}'")->num_rows;
        if ($checkresult == 0) {
            $sql = "INSERT INTO rel_martingale_fixtures (id_m,id_f) VALUES ('{$idM}','{$idF}')";
            $conn->query($sql);
        } 
    }


    function  getOpinionisti($anaID = '%%')
    {
      
        $connOpinionisti = $this->getRRMySQLConn();
        
        $sql = "SELECT * FROM Mopinionisti WHERE anaID LIKE '{$anaID}' ORDER BY anaCognome ASC";
        $return = '';

        $opinionistiA = $connOpinionisti->query($sql);

        while ($op = $opinionistiA->fetch_assoc()) {

            $opID = $op['anaID'];
            $opCognomeNome = $op['anaCognome'] . ' ' . $op['anaNome'];
            
           
            $return .= "<option value='{$opID}'>{$opCognomeNome}</option>\n";
       

        } 
     
        return $return;
    }
    

    public function insertPrediction($idO,$idF,$idPT) {

        $conn = $this->getMartDBonn();
        
        //cancello tutte le previsioni precedenti per quell'opinionista
        $delSql = "DELETE FROM predictions WHERE id_o = {$idO} AND id_f = {$idF}";
        $conn->query($delSql);

        $insSql = "INSERT INTO predictions (id_o,id_f,id_pt) VALUES ({$idO},{$idF},{$idPT})";
        $conn->query($insSql);


    }

    public function getPredictionValue($idPT) {

        $conn = $this->getMartDBonn();
        $sql = "SELECT type FROM predictiontype WHERE id_pt = {$idPT}";
        $result = $conn->query($sql);

        $return = $result->fetch_array()[0];

        return $return;



    }

}
