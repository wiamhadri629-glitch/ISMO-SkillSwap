<?php
try{
    $db = new PDO("mysql:host=localhost;port=3308;dbname=skillswap", "root", "");}
catch(PDOException $e){
    echo "Erreur : " . $e->getMessage();}
?>