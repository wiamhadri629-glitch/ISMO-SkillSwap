<?php
try{
    $db = new PDO("mysql:host=localhost;dbname=projet", "root", "");}
catch(PDOException $e){
    echo "Erreur : " . $e->getMessage();}
?>