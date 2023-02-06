<?php 
include_once("functions.php");
include_once("find_token.php");

if(!isset($_GET['type'])){
    echo ajax_echo(
        "Ошибка!",
        "Вы не указали GET параметр type!",
        "ERROR",
        null
    );
    exit;
}

//Register
if(preg_match_all("/^register_user|reg_usr$/ui", $_GET['type'])){
    if(!isset($_GET['login'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр login!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['pass'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр pass!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "CALL `RegisterUser`('".$_GET['login']."', '".$_GET['pass']."');";
    $res_query = mysqli_query($connection,$query);

    if(!$res_query){
        echo ajax_echo(
            "Ошибка!", 
            "Ошибка в запросе!",
            true,
            "ERROR",
            null
        );
        exit();
    }

    echo ajax_echo(
        "Успех!", 
        "Пользователь зарегестрирован!",
        false,
        "SUCCESS"
    );
    exit();
}

//Login
if(preg_match_all("/^auth_user$/ui", $_GET['type'])){
    if(!isset($_GET['login'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр login!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['pass'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр pass!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "SELECT COUNT(id) AS `num` FROM `users` WHERE `login`='".$_GET['login']."' AND `password`='".$_GET['pass']."' ";
    $res_query = mysqli_query($connection,$query);

    if(!$res_query){
        echo ajax_echo(
            "Ошибка!", 
            "Ошибка в запросе!",
            true,
            "ERROR",
            null
        );
        exit();
    }

    $rows = mysqli_num_rows($res_query);

    for ($i=0; $i < $rows; $i++){
        $row = mysqli_fetch_assoc($res_query);
        if($row["num"] == "0"){
            echo ajax_echo(
                "Ошибка!", 
                "Пользователь отсутствует!",
                true,
                "ERROR",
                null
            );
            exit();
        }
    }

    $query = "SELECT `id`, `login`, `image`, `cart`, `favorites` FROM `users` WHERE `login`='".$_GET['login']."' AND `password`='".$_GET['pass']."' ";
    $res_query = mysqli_query($connection,$query);

    if(!$res_query){
        echo ajax_echo(
            "Ошибка!", 
            "Ошибка в запросе 2!",
            true,
            "ERROR",
            null
        );
        exit();
    }

    $arr_res = array();
    $rows = mysqli_num_rows($res_query);

    for ($i=0; $i < $rows; $i++){
        $row = mysqli_fetch_assoc($res_query);
        array_push($arr_res, $row);
    }
    echo ajax_echo(
        "Успех!", 
        "Пользователь авторизирован!",
        false,
        "SUCCESS",
        $arr_res
    );
    exit();
}

//Load user image
if(preg_match_all("/^load_user_image$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['url'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр url!",
            "ERROR",
            null
        );
        exit;
    }

    $query = "UPDATE `users` SET `image`='".$_GET['url']."' WHERE `id` = ".$_GET['userid'];
    $res_query = mysqli_query($connection,$query);

    if(!$res_query){
        echo ajax_echo(
            "Ошибка!", 
            "Ошибка в запросе!",
            true,
            "ERROR",
            null
        );
        exit();
    }

    echo ajax_echo(
        "Успех!", 
        "Фотография загружена",
        false,
        "SUCCESS"
    );
    exit();
}

//Вывод всех продуктов
if(preg_match_all("/^list_product|lst_prd$/ui", $_GET['type'])){
    $query = "SELECT id, name, description FROM products WHERE `deleted` = FALSE";
    $res_query = mysqli_query($connection,$query);

    if(!$res_query){
        echo ajax_echo(
            "Ошибка!", 
            "Ошибка в запросе!",
            true,
            "ERROR",
            null
        );
        exit();
    }

    $arr_res = array();
    $rows = mysqli_num_rows($res_query);

    for ($i=0; $i < $rows; $i++){
        $row = mysqli_fetch_assoc($res_query);
        array_push($arr_res, $row);
    }
    echo ajax_echo(
        "Успех!", 
        "Список продукции!",
        false,
        "SUCCESS",
        $arr_res
    );
    exit();
}

//Добавление продукта
else if(preg_match_all("/^add_product|add_prd$/ui", $_GET['type'])){
    if(!isset($_GET['name'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр name!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['desc'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр desc!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['price'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр price!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "INSERT INTO products(name,description,price) VALUES ('".$_GET['name']."','".$_GET['desc']."', ".$_GET['price'].")";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Новый товар был добавлен в базу данных!",
        false,
        "SUCCESS"
    );
    exit;
}

//Удаление продукта
else if(preg_match_all("/^delete_product|del_prd$/ui", $_GET['type'])){
    if(!isset($_GET['productid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр productid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "UPDATE `products` SET `is_deleted` = '1' WHERE `products`.`id` = ".$_GET['productid'];
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Товар был удален из базы данных!",
        false,
        "SUCCESS"
    );
    exit;
}

//Добавление в корзину
else if(preg_match_all("/^addTo_cart$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['productid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр productid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "CALL `ProductToCart` ((SELECT `cart` FROM `users` WHERE `users`.`id` = ".$_GET['userid']."), ".$_GET['productid'].");";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Товар был добавлен в корзину!",
        false,
        "SUCCESS"
    );
    exit;
}

//Удаление из корзины
else if(preg_match_all("/^remove_from_cart$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['productid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр productid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "UPDATE `cart_items` SET `deleted`=true WHERE `product`=".$_GET['productid']." AND `cartid`=(SELECT `cart` FROM `users` WHERE `id` = ".$_GET['userid'].")";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Товар был удален из корзины!",
        false,
        "SUCCESS"
    );
    exit;
}

//Вывести товары из корзины
else if(preg_match_all("/^list_cart$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "SELECT `name`, `description` from `products` WHERE `id` IN (SELECT `product` FROM `cart_items` WHERE `cartid`=(SELECT `cart` FROM `users` WHERE `id`=".$_GET['userid'].") AND `deleted`=false)";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }

    $arr_res = array();
    $rows = mysqli_num_rows($res_query);

    for ($i=0; $i < $rows; $i++){
        $row = mysqli_fetch_assoc($res_query);
        array_push($arr_res, $row);
    }
    
    echo ajax_echo(
        "Успех!",
        "Товары были выведены!",
        false,
        "SUCCESS",
        $arr_res
    );
    exit;
}

//Добавление в избранное
else if(preg_match_all("/^addTo_favorite$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['productid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр productid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "CALL `ProductToFavorite` (".$_GET['productid'].", (SELECT `favorites` FROM `users` WHERE `users`.`id` = ".$_GET['userid']."));";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Товар был добавлен в избранное!",
        false,
        "SUCCESS"
    );
    exit;
}

//Удаление из избранного
else if(preg_match_all("/^remove_from_favorite$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['productid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр productid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "UPDATE `favorite_items` SET `deleted`=true WHERE `product`=".$_GET['productid']." AND `cartid`=(SELECT `favorites` FROM `users` WHERE `id` = ".$_GET['userid'].")";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Товар был удален из избранного!",
        false,
        "SUCCESS"
    );
    exit;
}

//Вывести товары из избранного
else if(preg_match_all("/^list_favorite$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "SELECT `name`, `description` from `products` WHERE `id` IN (SELECT `product` FROM `favorite_items` WHERE `favoriteid`=(SELECT `favorites` FROM `users` WHERE `id`=".$_GET['userid'].") AND `deleted`=false)";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }

    $arr_res = array();
    $rows = mysqli_num_rows($res_query);

    for ($i=0; $i < $rows; $i++){
        $row = mysqli_fetch_assoc($res_query);
        array_push($arr_res, $row);
    }
    
    echo ajax_echo(
        "Успех!",
        "Товары были выведены!",
        false,
        "SUCCESS",
        $arr_res
    );
    exit;
}

//Добавленить заказ
else if(preg_match_all("/^set_order$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['productid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр productid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['address'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр address!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "INSERT INTO `order`(`product`, `adress`, `userid`) VALUES (".$_GET['productid'].", '".$_GET['address']."', ".$_GET['userid'].");";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Заказ составлен!",
        false,
        "SUCCESS"
    );
    exit;
}

//Отменить заказ
else if(preg_match_all("/^cancel_order$/ui", $_GET['type'])){
    if(!isset($_GET['orderid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "UPDATE `order` SET `status`=6 WHERE `id` = ".$_GET['orderid'];
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Заказ отменен!",
        false,
        "SUCCESS"
    );
    exit;
}

//Добавление фотографии продукту
else if(preg_match_all("/^add_image_product$/ui", $_GET['type'])){
    if(!isset($_GET['productid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр productid!",
            "ERROR",
            null
        );
        exit;
    }
    if(!isset($_GET['url'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр url!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "INSERT INTO `images`(`url`, `productid`) VALUES ('".$_GET['url']."', ".$_GET['productid'].")";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }
    
    echo ajax_echo(
        "Успех!",
        "Фото добавлено!",
        false,
        "SUCCESS"
    );
    exit;
}

//Вывести заказы
else if(preg_match_all("/^list_orders$/ui", $_GET['type'])){
    if(!isset($_GET['userid'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр userid!",
            "ERROR",
            null
        );
        exit;
    }
    $query = "SELECT `name`, `description` FROM `products` WHERE `id` IN (SELECT `product` FROM `order` WHERE `userid` = ".$_GET['userid']." AND `deleted`=false)";
    
    $res_query = mysqli_query($connection, $query);
    
    if(!$res_query){
        echo ajax_echo(
            "Ошибка!",
            "Ошибка в запросе!",
            true,
            null
        );
        exit;
    }

    $arr_res = array();
    $rows = mysqli_num_rows($res_query);

    for ($i=0; $i < $rows; $i++){
        $row = mysqli_fetch_assoc($res_query);
        array_push($arr_res, $row);
    }
    
    echo ajax_echo(
        "Успех!",
        "Товары были выведены!",
        false,
        "SUCCESS",
        $arr_res
    );
    exit;
}



else{
    echo ajax_echo(
        "Ошибка 404!",
        "Page not found",
        "ERROR",
        null
    );
    exit;
}