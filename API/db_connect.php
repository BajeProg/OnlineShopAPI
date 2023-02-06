<?php

include_once("config.php");

$connection = mysqli_connect($DB["host"], $DB["login"], $DB["password"], $DB["name"]);

mysqli_query($connection, "SET NAMES '" . $DB['charset'] . "_unicode_ci';");
mysqli_query($connection, "SET CHRACTER SET '" . $DB['charset'] . "_unicode_ci';");
mysqli_query($connection, "SET timezone = '" . TIME_ZONE . "';");
mysqli_query($connection, "SET group_concat_max_len = 9999999;");

if(mysqli_connect_errno()){
    echo ajax_echo(
        "Ошибка!", 
        "Ошибка подключения к базе данных (" . mysqli_connect_errno . '): ' . mysqli_connect_errno(),
        true,
        "ERROR",
        null

    );
    echo("Ошибка подключения к базе данных (" . mysqli_connect_errno . '): ' . mysqli_connect_errno());
    exit();
}

if(!$connection->set_charset($DB['charset'])){
    echo ajax_echo(
        "Ошибка!", 
        "Ошибка при загрузке набора символов " . $DB['charset'] . ": %s\n",
        $connection->error ,
        true,
        "ERROR",
        null

    );
exit();
}