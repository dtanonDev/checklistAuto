<?php

$etape = $_POST['etape'];

global $conn;


$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$link = mysqli_connect($servername, $username, $password);

// Check connection
/*if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} */




switch($etape){
    case 1:
        $listeDB = getSiteListe($link);
        $toDisplay = '<li>'.count($listeDB).' tables trouvées</li>';
        $listePrestaDB = getPrestashopTables($link, $listeDB);
        $toDisplay .= '<li>'.count($listePrestaDB).' tables prestashop trouvées</li>';

        $nexData = $listePrestaDB;
        echo json_encode(
            array(
                'toDisplay' => $toDisplay,
                'listePrestaDB' => $listePrestaDB,
                'nbtoDig' => count($listePrestaDB)
            )
        );

    break;

    case 2:
        $currentDB = $_POST['table'];
        $toDisplay = '<ul><li>Traitement de la table '.$currentDB.'</li>';
        echo json_encode(
            array(
                'toDisplay' => $toDisplay,
                //'listePrestaDB' => $listePrestaDB,
                //'nbtoDig' => count($listePrestaDB)
            )
        );
    break;
}

/**
 * Undocumented function
 *
 * @return void
 */
function getSiteListe($link)
{
    //echo 'etape 1';
    //$db_list = mysqli_list_dbs();
    $res = mysqli_query($link, "SHOW DATABASES");
    //$dbList = mysqli_fetch_row($res);

    while( $row = mysqli_fetch_row( $res ) ){
        if (($row[0]!="information_schema") && ($row[0]!="mysql")) {
            $dbList[] = $row[0];
        }
    }
    return($dbList);

}

/** */ 
function getPrestashopTables($link, $listeDB)
{
    $prestashopDbList = array();
    
    foreach ($listeDB as $key => $unetable) {
        mysqli_select_db($link, $unetable);

        /*if ($result = mysqli_query($link, "SELECT DATABASE()")) {
            $row = mysqli_fetch_row($result);
            printf("La base de données courante est %s.\n", $row[0]);
            mysqli_free_result($result);
        }*/

        if ($result = mysqli_query($link, "SELECT name FROM ps_configuration where name like '%PS_VERSION_DB%'")) {
            //printf("Select a retourné %d lignes.\n", mysqli_num_rows($result));
            $prestashopDbList[] = $unetable;
            //var_dump($result);
            /* Libération du jeu de résultats */
            mysqli_free_result($result);
        }
        //else echo mysqli_error($link);
    }
    return $prestashopDbList;
}

/** */
function checklist($link, $table) {
    //Check que l'employé est bien crée

    // force la compile
    //PS_SMARTY_FORCE_COMPILE => 1
    //caCHE SMARTY
    //PS_SMARTY_CACHE => 1
    if ($result = mysqli_query($link, "SELECT name FROM ps_configuration where name like '%PS_VERSION_DB%'")) {

    }
}



?>