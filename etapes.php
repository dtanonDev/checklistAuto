<?php

/** Inspiré des checklist store commander
 * TODO: SECURITE : verifier la suppression des fichiers install.TXT, Install_PrestaShop.html et LICENCE
 * TODO: SECURITE : verifier la suppresion de CHANGELOG.txt, readme_de.txt, readme_en.txt, readme_es.txt, readme_fr.txt, readme_it.txt
 * TODO: SECURITE : vérifier la présence du fichier robots.txt
 * TODO: SECURITE : verifier la suppression du dossier vendor/phpunit
 * TODO: SECURITE : vérifier patch CVE-2020-5250 pour les version 1.7.6.0 à 1.7.6.3
 * 
 * TODO: check de securite connue
 *  types of injection :
 *     SQL Injections
    XSS Injections
    SHELL Commands Injections
    Code Injections
    CGI Injections


    data thief :

    Hotlinking protection
    Files protection
    Copy-Paste protection
    Right Clic protection
 * 
 */
require __DIR__ . '/vendor/autoload.php';

use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;

$etape = $_POST['etape'];

global $conn;

$servername = "localhost";
$username = "root";
$password = "";
define("_DB_PREFIX_", "ps_");

// Create connection
$link = mysqli_connect($servername, $username, $password);

// Check connection
/*if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
} */

switch ($etape) {
    case 1:
        $listeDB = getSiteListe($link);
        $toDisplay = '<li>' . count($listeDB) . ' tables trouvées</li>';
        $listePrestaDB = getPrestashopTables($link, $listeDB);
        $toDisplay .= '<li>' . count($listePrestaDB) . ' tables prestashop trouvées</li>';

        $nexData = $listePrestaDB;
        echo json_encode(
            array(
                'toDisplay' => $toDisplay,
                'listePrestaDB' => $listePrestaDB,
                'nbtoDig' => count($listePrestaDB),
            )
        );

        break;

    case 2:
        $currentDB = $_POST['table'];
        $toDisplay = '<ul><li>Traitement de la table ' . $currentDB . '<ul>';

        $toDisplay .= checklist($servername, $username, $password, $currentDB);
        $toDisplay .= '</ul></li></ul>';

        //enregistrement dans un fichier
        //file_put_contents(dirname(__FILE__) . '/' . $currentDB . '.html', $toDisplay);
        ////$html2pdf = new Html2Pdf();
        ////$html2pdf->writeHTML($toDisplay);
        try {

            $html2pdf = new Html2Pdf('P', 'A4', 'fr');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($toDisplay);
            $html2pdf->output(dirname(__FILE__) .'/'. $currentDB . '_'. date('Ymdis').'.pdf', 'F');
            ////$html2pdf->output('example00.pdf', 'F');
        } catch (Html2PdfException $e) {
            $html2pdf->clean();

            $formatter = new ExceptionFormatter($e);
            echo $formatter->getHtmlMessage();
        }
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

    while ($row = mysqli_fetch_row($res)) {
        if (($row[0] != "information_schema") && ($row[0] != "mysql")) {
            $dbList[] = $row[0];
        }
    }
    return ($dbList);

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
function checklist($servername, $username, $password, $table)
{
    $db = new mysqli($servername, $username, $password, $table);
    $db->set_charset("utf8");

    $sql = "SELECT value FROM ps_configuration where name like '%PS_VERSION_DB%'";

    if ($result = $db->query($sql)) {
        $row = $result->fetch_object();
        $prestaVers = $row->value;
        $response = '<li>PS Version ' . $prestaVers . '</li>';

    }
    $result->close();

    $sqlLang = "SELECT id_lang,name FROM ps_lang";
    if ($resultLang = $db->query($sqlLang)) {
        while ($row = $resultLang->fetch_object()) {
            $lstLang[] = get_object_vars($row);
            //$response = '<li>PS Version '.$prestaVers.'</li>';
        }
    }
    $resultLang->close();

    // force la compile
    //PS_SMARTY_FORCE_COMPILE => 1
    //caCHE SMARTY
    //PS_SMARTY_CACHE => 1
    /**
     * PS_SSL_ENABLED_EVERYWHERE
     * PS_SMARTY_CACHING_TYPE
     * PS_SMARTY_CLEAR_CACHE
     * PS_SHOP_DOMAIN = PS_SHOP_DOMAIN_SSL
     * PS_SHOP_EMAIL
     * PS_MAIL_METHOD
     * PS_MAIL_SERVER
     * PS_MAIL_USER
     * PS_MAIL_PASSWD
     * PS_MAIL_SMTP_ENCRYPTION
     * PS_MAIL_SMTP_PORT
     * PS_SMARTY_CACHE
     *
     ********** Thème *
    Développer le thème en fonction du fichier psd fourni
    Vérification dimension(colonne de gauche, colonne centrale, colonne de droite, encarts…)
    Mettre les balises noindex et nofollow dans le header.tpl (pour les /new)
    Vérifier les versions responsive (320; 768;992;1200)
    Vérification des liens (logo, menu…)
    Vérification de la présence des éléments (plan du site, coordonnées, logo, logo INCOMM, men…)
    Vérifier l'affichage de la carte google map, si nécessaire éditer le fichier storesController.php
    Faire des captures d'écrans et les joindre à la fiche d'intervention
     ***************** Modules ************/
    $response .= '<li><b>Modules</b><ul>';
    $sqlmodule = 'SELECT name,active FROM ps_module';
    if ($resultModule = $db->query($sqlmodule)) {
        while ($row = $resultModule->fetch_object()) {
            $lstModules[] = get_object_vars($row);
            //$response = '<li>PS Version '.$prestaVers.'</li>';
        }

        //print_r($lstModules);
    }

//Vérifier l'installation du module "image slider"
    //table ps_module : name = ps_imageslider     active = 1 ou 1.6 table ps_module : name = homeslider     active = 1
    if ($key = array_search('ps_imageslider', array_column($lstModules, 'name'))) {
        //echo $key . '***';
        if ($lstModules[$key]['active'] == 1) {
            $response .= "<li class='text-success'>Le module image slider est activé</li>";
        } else {
            $response .= "<li class='text-warning'>Le module image slider n'est pas activé</li>";
        }
        //valeur de la colonne active
    } else {
        $response .= "<li class='text-danger'>Le module image slider n'est pas installé</li>";
    }
    /*Vérifier l'installation du module "Loi Hamon"
    table ps_module : name = loihamon     active = 1*/
    if ($key = array_search('loihamon', array_column($lstModules, 'name'))) {
        if ($lstModules[$key]['active'] == 1) {
            $response .= "<li class='text-success'>Le module loi hamon est activé</li>";
        } else {
            $response .= "<li class='text-warning'>Le module loi hamon n'est pas activé</li>";
        }
    } else {
        $response .= "<li class='text-danger'>Le module loi hamon n'est pas installé</li>";
    }
/*Vérifier l'installation du module "Cookie"
table ps_module : name = loihamon     active = 1
Vérifier l'installation du module "Directive Européenne des Cookies"*/

    if ($key = array_search('lgcookieslaw', array_column($lstModules, 'name'))) {
        if ($lstModules[$key]['active'] == 1) {
            $response .= "<li class='text-success'>Le module Directive Européenne des Cookies est activé</li>";
        } else {
            $response .= "<li class='text-warning'>Le module Directive Européenne des Cookies n'est pas activé</li>";
        }
    } else {
        $response .= "<li class='text-danger'>Le module Directive Européenne des Cookies n'est pas installé</li>";
    }
/*Vérifier l'installation du module "Advanced Top Menu"
table ps_module : name = loihamon     active = 1
Vérifier l'installation du module "avis client"
Vérifier l'installation et la configuration du module "alert mail"*/
    if ($key = array_search('mailalerts', array_column($lstModules, 'name'))) {
        if ($lstModules[$key]['active'] == 1) {
            $response .= "<li class='text-success'>Le module mail alert est activé</li>";
        } else {
            $response .= "<li class='text-warning'>Le module mail alert n'est pas activé</li>";
        }
    } else {
        $response .= "<li class='text-danger'>Le module mail alert n'est pas installé</li>";
    }
    /*Vérifier l'installation et la configuration du module "Seo Expert"*/
    if ($key = array_search('seoexpert', array_column($lstModules, 'name'))) {
        if ($lstModules[$key]['active'] == 1) {
            $response .= "<li class='text-success'>Le module Seo Expert est activé</li>";
        } else {
            $response .= "<li class='text-warning'>Le module Seo Expert n'est pas activé</li>";
        }
    } else {
        $response .= "<li class='text-danger'>Le module Seo Expert n'est pas installé</li>";
    }
    /*Vérifier l'installation et la configuration du module "Google site map"*/
    if ($key = array_search('gsitemap', array_column($lstModules, 'name'))) {
        if ($lstModules[$key]['active'] == 1) {
            $response .= "<li class='text-success'>Le module Google site map est activé</li>";
        } else {
            $response .= "<li class='text-warning'>Le module Google site map n'est pas activé</li>";
        }
    } else {
        $response .= "<li class='text-danger'>Le module Google site map n'est pas installé</li>";
    }
    /*Vérifier l'installation et la configuration du module "Google Analyticsp"*/
    if ($key = array_search('ganalytics', array_column($lstModules, 'name'))) {
        if ($lstModules[$key]['active'] == 1) {
            $response .= "<li class='text-success'>Le module Google Analytics est activé</li>";
        } else {
            $response .= "<li class='text-warning'>Le module Google Analytics n'est pas activé</li>";
        }
    } else {
        $response .= "<li class='text-danger'>Le module Google Analytics n'est pas installé</li>";
    }
/*Vérifier l'installation du module "google analytics"
table ps_module : name = loihamon     active = 1
Vérifier les liens et les images de Best Kits (pour theme flowershop)
Vérifier l'installation du module "cheque" + configuration avec les informations du log
Désinstaller le module "virement bancaire"
table ps_module : name = loihamon     active = 1
TODO: Lier avec l'API lelog
Mettre à jour le log avec la liste des modules installés et les versions
Désactiver le défilement pour image (Homeslider)
Supprimer le module Sendtoafriend (désinstaller et supprimer du FTP)
table ps_module : name = loihamon     active = 1
TODO: Désinstaller les modules depuis ce script
supprimer le module gamification*/
    if ($key = array_search('gamification', array_column($lstModules, 'name'))) {
        if ($lstModules[$key]['active'] == 1) {
            $response .= '<li class="text-danger">Le module gamification est activé</li>';
            //supprimerModule($key);
        } else {
            $response .= "<li class='text-warning'>Le module gamification n'est pas activé</li>";
        }
    } else {
        $response .= "<li class='text-success'>Le module gamification n'est pas installé</li>";
    }

    $resultModule->close();

    $response .= '</ul></li>';
    /**** fin d'analyse des modules */
/********************************************************* Administration *******************************************************
TODO:Créer le compte utilisateur pour le partenaire avec le profil gérant
TODO: test fonctionnel : Vérification des accès kameleon (utilisateur / administrateur)
TODO: Vérifier les infos dans le Log et Remplir les informations de l'utilisateur dans le log*/
    $response .= '<li><b>Administration</b><ul>';
    //vérifier le profil Gérant
    $sqlRole = 'SELECT name, id_profile FROM ps_profile_lang WHERE name = "Gérant"';
    if ($resultRole = $db->query($sqlRole)) {
        $row = $resultRole->fetch_object();
        if (count($row) > 0) {
            $idProfile = $row->id_profile;
            $response .= '<li class="text-success">Le profile gérant existe (id ' . $idProfile . ')</li>';
        } else {
            $response .= "<li class='text-danger'>Le profile gérant n'existe pas :<ul>";
            //on crée le profile gérant
            $maxProfile = $db->query("SELECT max(id_profile) as maxid FROM ps_profile");
            $rowProfile = $maxProfile->fetch_object();
            $idProfile = $rowProfile->maxid + 1;

            $db->query("INSERT INTO ps_profile (id_profile) VALUES($idProfile)");

            foreach ($lstLang as $key => $value) {
                if (!$db->query("INSERT INTO ps_profile_lang (id_profile, name, id_lang) VALUES($idProfile, 'Gérant', " . $value['id_lang'] . ")")) {
                    //echo $db->error;
                    $response .= '<li class="text-danger">Impossible de crée le role gérant : ' . $db->error . '</li></ul>';
                } else {
                    $response .= '<li class="text-success">Role gérant crée</li></ul>';
                }
            }

        }
    }

    $resultRole->close();
    //On vérifie qu'un employé avec le role gérant existe
    $resultEmployee = $db->query('SELECT email FROM ps_employee WHERE id_profile = ' . $idProfile);
    $row = $resultEmployee->fetch_object();
    if (count($row) > 0) {

        $response .= '<li class="text-success">un employé à le role gérant(email ' . $row->email . ')</li>';
    } else {
        $response .= '<li class="text-danger">Aucun employé avec le role gérant</li>';
    }
    $resultEmployee->close();

    // Vérification des droit pour le gérant
    $sqlDroits = 'SELECT id_authorization_role,slug FROM ps_authorization_role';
    if ($resultDroits = $db->query($sqlDroits)) {
        while ($row = $resultDroits->fetch_object()) {
            $lstDroits[] = get_object_vars($row);
            //$response = '<li>PS Version '.$prestaVers.'</li>';
        }

        //print_r($lstDroits);
        $resultDroits->close();
    }

    //Tableau de bord
    $accessList = array(
        'ROLE_MOD_TAB_ADMINDASHBOARD_CREATE',
        'ROLE_MOD_TAB_ADMINDASHBOARD_DELETE',
        'ROLE_MOD_TAB_ADMINDASHBOARD_READ',
        'ROLE_MOD_TAB_ADMINDASHBOARD_UPDATE',
        'ROLE_MOD_TAB_SELL_CREATE',
        'ROLE_MOD_TAB_SELL_DELETE',
        'ROLE_MOD_TAB_SELL_READ',
        'ROLE_MOD_TAB_SELL_UPDATE',
        'ROLE_MOD_TAB_ADMINPARENTORDERS_CREATE',
        'ROLE_MOD_TAB_ADMINPARENTORDERS_DELETE',
        'ROLE_MOD_TAB_ADMINPARENTORDERS_READ',
        'ROLE_MOD_TAB_ADMINPARENTORDERS_UPDATE',
        'ROLE_MOD_TAB_ADMINSUPPLYORDERS_CREATE',
        'ROLE_MOD_TAB_ADMINORDERS_CREATE',
        'ROLE_MOD_TAB_ADMINORDERS_DELETE',
        'ROLE_MOD_TAB_ADMINORDERS_READ',
        'ROLE_MOD_TAB_ADMINORDERS_UPDATE',
        'ROLE_MOD_TAB_ADMININVOICES_CREATE',
        'ROLE_MOD_TAB_ADMININVOICES_DELETE',
        'ROLE_MOD_TAB_ADMININVOICES_READ',
        'ROLE_MOD_TAB_ADMININVOICES_UPDATE',
        'ROLE_MOD_TAB_ADMINSLIP_CREATE',
        'ROLE_MOD_TAB_ADMINSLIP_DELETE',
        'ROLE_MOD_TAB_ADMINSLIP_READ',
        'ROLE_MOD_TAB_ADMINSLIP_UPDATE',
        'ROLE_MOD_TAB_ADMINDELIVERYSLIP_CREATE',
        'ROLE_MOD_TAB_ADMINDELIVERYSLIP_DELETE',
        'ROLE_MOD_TAB_ADMINDELIVERYSLIP_READ',
        'ROLE_MOD_TAB_ADMINDELIVERYSLIP_UPDATE',
        'ROLE_MOD_TAB_ADMINCATALOG_CREATE',
        'ROLE_MOD_TAB_ADMINCATALOG_DELETE',
        'ROLE_MOD_TAB_ADMINCATALOG_READ',
        'ROLE_MOD_TAB_ADMINCATALOG_UPDATE',
        'ROLE_MOD_TAB_ADMINPRODUCTS_CREATE',
        'ROLE_MOD_TAB_ADMINPRODUCTS_DELETE',
        'ROLE_MOD_TAB_ADMINPRODUCTS_READ',
        'ROLE_MOD_TAB_ADMINPRODUCTS_UPDATE',
        'ROLE_MOD_TAB_ADMINCATEGORIES_CREATE',
        'ROLE_MOD_TAB_ADMINCATEGORIES_DELETE',
        'ROLE_MOD_TAB_ADMINCATEGORIES_READ',
        'ROLE_MOD_TAB_ADMINCATEGORIES_UPDATE',
        'ROLE_MOD_TAB_ADMINFEATURES_CREATE',
        'ROLE_MOD_TAB_ADMINFEATURES_DELETE',
        'ROLE_MOD_TAB_ADMINFEATURES_READ',
        'ROLE_MOD_TAB_ADMINFEATURES_UPDATE',
        'ROLE_MOD_TAB_ADMINATTRIBUTESGROUPS_CREATE',
        'ROLE_MOD_TAB_ADMINATTRIBUTESGROUPS_DELETE',
        'ROLE_MOD_TAB_ADMINATTRIBUTESGROUPS_READ',
        'ROLE_MOD_TAB_ADMINATTRIBUTESGROUPS_UPDATE',
        'ROLE_MOD_TAB_ADMINPARENTATTRIBUTESGROUPS_CREATE',
        'ROLE_MOD_TAB_ADMINPARENTATTRIBUTESGROUPS_DELETE',
        'ROLE_MOD_TAB_ADMINPARENTATTRIBUTESGROUPS_READ',
        'ROLE_MOD_TAB_ADMINPARENTATTRIBUTESGROUPS_UPDATE',
        'ROLE_MOD_TAB_ADMINMANUFACTURERS_CREATE',
        'ROLE_MOD_TAB_ADMINMANUFACTURERS_DELETE',
        'ROLE_MOD_TAB_ADMINMANUFACTURERS_READ',
        'ROLE_MOD_TAB_ADMINMANUFACTURERS_UPDATE',
        'ROLE_MOD_TAB_ADMINPARENTMANUFACTURERS_CREATE',
        'ROLE_MOD_TAB_ADMINPARENTMANUFACTURERS_DELETE',
        'ROLE_MOD_TAB_ADMINPARENTMANUFACTURERS_READ',
        'ROLE_MOD_TAB_ADMINPARENTMANUFACTURERS_UPDATE',
        'ROLE_MOD_TAB_ADMINSUPPLIERS_CREATE',
        'ROLE_MOD_TAB_ADMINSUPPLIERS_DELETE',
        'ROLE_MOD_TAB_ADMINSUPPLIERS_READ',
        'ROLE_MOD_TAB_ADMINSUPPLIERS_UPDATE',
        'ROLE_MOD_TAB_ADMINATTACHMENTS_CREATE',
        'ROLE_MOD_TAB_ADMINATTACHMENTS_DELETE',
        'ROLE_MOD_TAB_ADMINATTACHMENTS_READ',
        'ROLE_MOD_TAB_ADMINATTACHMENTS_UPDATE',
        'ROLE_MOD_TAB_ADMINPARENTCARTRULES_CREATE',
        'ROLE_MOD_TAB_ADMINPARENTCARTRULES_DELETE',
        'ROLE_MOD_TAB_ADMINPARENTCARTRULES_READ',
        'ROLE_MOD_TAB_ADMINPARENTCARTRULES_UPDATE',
        'ROLE_MOD_TAB_ADMINCARTRULES_CREATE',
        'ROLE_MOD_TAB_ADMINCARTRULES_DELETE',
        'ROLE_MOD_TAB_ADMINCARTRULES_READ',
        'ROLE_MOD_TAB_ADMINCARTRULES_UPDATE',
        'ROLE_MOD_TAB_ADMINSPECIFICPRICERULE_CREATE',
        'ROLE_MOD_TAB_ADMINSPECIFICPRICERULE_DELETE',
        'ROLE_MOD_TAB_ADMINSPECIFICPRICERULE_READ',
        'ROLE_MOD_TAB_ADMINSPECIFICPRICERULE_UPDATE',
    );

    /** Pur les presta 1.7 */

    if (version_compare($prestaVers, '1.7.0.0') >= 0) {

        $tblAccess = array();
        $sqlListRole = "SELECT `slug`, `id_authorization_role` FROM `" . _DB_PREFIX_ . "authorization_role`";
        if ($resultListRole = $db->query($sqlListRole)) {
            while ($row = $resultListRole->fetch_array()) {
                $tblAccess[$row['slug']] = $row['id_authorization_role'];
            }
        }

        //var_dump($tblAccess);

        $sqlAccess = "SELECT a.`slug`, a.`id_authorization_role`, j.`id_profile` FROM `" . _DB_PREFIX_ . "authorization_role` a
    LEFT JOIN `" . _DB_PREFIX_ . "access` j ON j.id_authorization_role = a.id_authorization_role
    WHERE j.`id_profile` = " . (int) $idProfile . " AND a.`slug` IN ('" . implode("','", $accessList) . "')";

        $lstAccess = array();

        if ($resultAccess = $db->query($sqlAccess)) {
            while ($row = $resultAccess->fetch_array()) {
                //var_dump($row);
                $lstAccess[] = $row[0];
            }
        } else {
            echo '****' . $db->error;
        }

        foreach ($accessList as $key => $unacces) {
            if (in_array($unacces, $lstAccess)) {
                $response .= '<li class="text-success">Permission ' . $unacces . ' Ok</li>';
            } else {

                if ($db->query('INSERT INTO ps_access (id_profile,id_authorization_role) VALUES( ' . (int) $idProfile . ',' . (int) $tblAccess[$unacces] . ')  ')) {
                    $response .= '<li class="text-success">Permission ' . $unacces . ' Ajouté</li>';
                } else {
                    $response .= '<li class="text-success">Erreur Ajout Permission ' . $unacces . ' : ' . $db->error . '</li>';
                }
            }

        }

    }
    $response .= '</ul></li>';
/**********************************************************************************************/

    $response .= '<li><b>Paramètres avancés</b><ul>';
    $configList = array(
        'PS_SMARTY_FORCE_COMPILE' => 0, // 0
        'PS_SMARTY_CACHE' => 1, //  0
        //PS_SMARTY_CONSOLE
        //PS_SMARTY_CONSOLE_KEY
        'PS_SMARTY_CACHING_TYPE' => 'filesystem', //filesystem
        'PS_SMARTY_CLEAR_CACHE' => 'never', // never
        // debug Mode
        //CCC
        'PS_GIFT_WRAPPING' => 0, // 0
        'PS_RECYCLABLE_PACK' => 0, // 0
    );

    $sqlConfig = "SELECT `name`, `value` FROM `" . _DB_PREFIX_ . "configuration`";

    $lstConfig = array();

    if ($resultConfig = $db->query($sqlConfig)) {
        while ($row = $resultConfig->fetch_array()) {
            //var_dump($row);
            $lstConfig[$row['name']] = $row['value'];
        }
    } else {
        echo '****' . __LINE__ . $db->error;
    }

    //var_dump($lstConfig);

    foreach ($configList as $key => $uneConfig) {
        if ($lstConfig[$key] == $configList[$key]) {
            $response .= '<li class="text-success">Configuration ' . $key . ' Ok</li>';
        } else {

            //TODO : GROS BUG
           /* if ($db->query("UPDATE `" . _DB_PREFIX_ . "configuration`  SET value = '" . $configList[$key] . "' WHERE name = '" . $key . "'")) {
                $response .= '<li class="text-warning">Configuration ' . $key . ' Modifiéé</li>';
            } else {
                $response .= '<li class="text-danger">Erreur modif Configuration ' . $key . ' : ' . $db->error . '</li>';
            }*/
        }

    }



    $response .= '</ul></li>';


    /********************************************************* Préférences générales *******************************************************/
    $response .= '<li><b>Préférences générales</b><ul>';

/*
Activer le SSL : OUI
Activer le SSL sur tout le site OUI.
Améliore la sécurité du front-office OUI
Utiliser la bibliothèque HTMLPurifier OUI
Afficher les fournisseurs et les fabricants OUI
Afficher les meilleures ventes Oui

*/
    $response .= '</ul></li>';
    /*

    Never recompile template files = 0
    Recompile templates if the files have been updated = 1
    Force compilation = 2

    Activer toutes les options de la partie CCC (CONCATÉNATION, COMPRESSION ET MISE EN CACHE)
    TODO: Configurer l'envoi des mails en SMTP à partir des information du log
    TODO: Vérifier l'adresse email du destinataire du formulaire de contact
    TODO: fonctionnel : Faire un test d'envoi de mail depuis le formulaire de contact
     */

    /****************************************************************************************************** */
    $response .= '<li><b>Préférences</b></li>';
/*
TODO: Fonctionnel : Vérifier le logo des factures et des emails
TODO: Vérifier le nombre de produit par page pour correspondre à la maquette
Activer les URL simplifiée
TODO: Remplir les coordonnées de la boutique à partir du log
Désactiver l'affichage des erreurs dans config/defines.inc.php
 */

    return $response;
}
