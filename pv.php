<?php
include("parametres.php");
include("library/fonctions_php.php");
//include("pattern.php");$test='sddsfgdfg';

mysql_connect( $server,$user,$password);
echo $database;
if ($encodage=="utf-8") mysql_query("SET NAMES utf8;");
@mysql_select_db($database) or die( "Unable to select database");
//à préciser lorsqu'on est sur sciencemapping.com
if ($user!="root") mysql_query("SET NAMES utf8;");


for ($ii=1;$ii<2;$ii++) {
    $name=2006+$ii;
    $fichier="http://127.0.0.1/medline/PubMed_Abstracts/Pubmed_".$name."[dp]/Pubmed_".$name."[dp].txt";
    echo $fichier.'<br/>';
    //$fichier="data/pubmed_result.txt";
    $fichier="data/test.txt";
    $adresse_root= $_SERVER['DOCUMENT_ROOT'];

    include("include/header.php");
    // import des données
    //echo $fichier;
    //$tabfich=file($fichier);
    $handle=@fopen($fichier, "r");
    $query="DROP TABLE pvalues ";
    mysql_query($query) or die ("<b>table non effacée</b>.");


// creation de la table
    $fields=array_keys($to_process);
    $query="
CREATE TABLE IF NOT EXISTS `pvalues` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    for ($i=1;$i<count($fields);$i++) {
        $query.="`".$fields[$i]."` ".$to_process[$fields[$i]]."  NOT NULL,";
    }
    $sql=substr($query,0,-1).") ENGINE=MyISAM DEFAULT CHARSET=latin1";
    echo $sql;
    mysql_query($sql) or die ("<b>Requête non exécutée (creation de la table pvalues)</b>.");
//
    $nbAB=0;
    $articles=0;

    if ($handle) {
        while (($ligne = fgets($handle, 10000)) !== false) {

        $prefix=getField($line);
        if ((strcmp($prefix),'PMID')==0)&&($articles>0){


        }else{


        }
            if (strcmp(substr($ligne, 0,4),'PMID')==0) {
                $temp=explode(' ',$ligne);
                $PMID=$temp[1];
                $PMIDMeta=1; // dit si des infos mate ont déjà été ajoutées sur cet abstract ex: nb abstract
            }
            //echo $ligne.'<br/>';
            //for ($j=0;$j<count($pattern); $j++){

            //if (preg_match("/p{1}((\s|-)?)(value?)(=|<|>|≤|≥)[0-9\.\,]+/", $ligne, $matches)) {
            if (preg_match_all("/[Pp]{1}(\s|-)*(value)?(\s)*(=|<|>|≤|≥){1}(\s)*[0-9]+[\,|\.]?[0-9]+/", $ligne, $matches)) {
                echo '<br/>'.$nbAB.') '.$ligne;
                if ($PMIDMeta==1) {
                    $nbAB++;
                    $PMIDMeta=0;
                }

                for  ($j=0;$j<count($matches); $j++) {
                    if (strlen($matches[0][$j])>0) {
                        $chunk=$matches[0][$j];
                        //echo $chunk.'<br/>';
                        $temp=preg_split("/(=|<|>|≤|≥)/",$chunk);
                        //echo $temp[1].'<br/>';
                        preg_match("/(=|<|>|≤|≥)/", $chunk, $delimiter);
                        //echo $delimiter[0].'<br/>';
                        if ($temp[1]<1) {
                            $sql="INSERT INTO pvalues (id,PMID,type,value) VALUES ('','".$PMID."','".$delimiter[0]."','".str_replace(',','.',$temp[1])."')";
                            //echo $sql.'<br/>';
                        }
                        mysql_query($sql) or die ("<b>data not inserted)</b>.");

                    }
                }
                //error();
            }



        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }


//    for( $i = 0 ; $i < count($tabfich) ; $i++ ) {
//        //echo $i.'<br/>';
//        $ligne = $tabfich[$i];
//        if (strcmp(substr($ligne, 0,4),'PMID')==0){
//            $temp=explode(' ',$ligne);
//            $PMID=$temp[1];
//            $PMIDMeta=1; // dit si des infos mate ont déjà été ajoutées sur cet abstract ex: nb abstract
//        }
//        //echo $ligne.'<br/>';
//        //for ($j=0;$j<count($pattern); $j++){
//            //if (preg_match("/p{1}((\s|-)?)(value?)(=|<|>|≤|≥)[0-9\.\,]+/", $ligne, $matches)) {
//            if (preg_match_all("/[Pp]{1}(\s|-)*(value)?(\s)*(=|<|>|≤|≥){1}(\s)*[0-9]+[\,|\.]?[0-9]+/", $ligne, $matches)) {
//                echo '<br/>'.$nbAB.') '.$ligne;
//                if ($PMIDMeta==1){
//                    $nbAB++;
//                    $PMIDMeta=0;
//                }
//
//                for  ($j=0;$j<count($matches); $j++){
//                    if (strlen($matches[0][$j])>0){
//                        $chunk=$matches[0][$j];
//                        //echo $chunk.'<br/>';
//                        $temp=preg_split("/(=|<|>|≤|≥)/",$chunk);
//                        //echo $temp[1].'<br/>';
//                        preg_match("/(=|<|>|≤|≥)/", $chunk, $delimiter);
//                        //echo $delimiter[0].'<br/>';
//                        if ($temp[1]<1){
//                            $sql="INSERT INTO pvalues (id,PMID,type,value) VALUES ('','".$PMID."','".$delimiter[0]."','".str_replace(',','.',$temp[1])."')";
//                            //echo $sql.'<br/>';
//                        }
//                        mysql_query($sql) or die ("<b>data not inserted)</b>.");
//
//                    }
//                }
//                //error();
//            }
//
//      }

// STAT ///////
    echo 'number of abstracts treated: '.$nbAB.'<br/>';

// Graphiques
    $sql="SELECT value FROM pvalues WHERE type=('=' OR '<' OR '≤') ORDER BY value";
    $resultat=mysql_query($sql) or die ("<b>pvalues not retrieved)</b>.");
    $data=array();
    while ($ligne=mysql_fetch_array($resultat)) {
        $value=$ligne[value];
        //echo $value.'<br/>';
        if ($data[trim($value)]==null) {
            $data[trim($value)]=1;
            echo 'new pvalue: '.trim($value).'<br/>';
        }else {
            $data[trim($value)]+=1;
        };
    }

    $data_val=array_keys($data);
    $data_occ=array_values($data);

    $dataValFile = fopen('dataval_'.$name.'.txt','w');
    $dataOccFile = fopen('dataocc_'.$name.'.txt','w');

    while (count($data_val)>0) {
        $val=array_pop($data_val);
        $occ=array_pop($data_occ);
        fputs($dataValFile,$val.' ');
        fputs($dataOccFile,$occ.' ');
    }

    fclose($dataValFile);
    fclose($dataOccFile);
//include('include/include_chart.php');
//echo $myscript;
}


function getField($line) {
// done le descripteur du champ Medline ou retourne 0
    if (strcmp($ligne[0],' ')==0) {
        return FALSE;
    }else {
        $pos=stripos($ligne,'-');
        return trim(substr($ligne,0,$pos-1));
    }
}
?>