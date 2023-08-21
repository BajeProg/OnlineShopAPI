<?php 
header('Content-Type: application/json');

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

    
    $hash = hash('md5', $_GET['login'].date(DATE_RFC822).$_GET['pass']);
    if(isset($_GET["session"])) $hash = $_GET["session"];
    if(isset($_SERVER['HTTP_USER_AGENT'])) $user_agent = $_SERVER['HTTP_USER_AGENT'];
    else $user_agent = "";
    $query = "INSERT INTO `sessions`(`token`, `user_agent`, `ip`, `user_id`) VALUES ('".$hash."', '".$user_agent."', '".$_SERVER['REMOTE_ADDR']."', (SELECT `id` FROM `users` WHERE `login`='".$_GET['login']."' AND `password`='".$_GET['pass']."'))";
    $res_query = mysqli_query($connection,$query);

    if(!$res_query){
        echo ajax_echo(
            "Ошибка!", 
            "Ошибка в запросе!",
            true,
            "ERROR",
            null
        );
        exit($query);
    }

    echo ajax_echo(
        "Успех!", 
        "Сессия создана!",
        false,
        "SUCCESS",
        $hash
    );
}

//enter
if(preg_match_all("/^enter_session$/ui", $_GET['type'])){
    if(!isset($_GET['session_token'])){
        echo ajax_echo(
            "Ошибка!",
            "Вы не указали GET параметр session_token!",
            "ERROR",
            null
        );
        exit;
    }

    if(isset($_SERVER['HTTP_USER_AGENT'])) $user_agent = $_SERVER['HTTP_USER_AGENT'];
    else $user_agent = "";
    $query = "SELECT COUNT(id) AS `num` FROM `sessions` WHERE `token`='".$_GET['session_token']."' AND `user_agent`='".$user_agent."' AND `closed`=false";
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
                "Сессия отсутствует!",
                true,
                "ERROR",
                null
            );
            exit();
        }
    }

    $query = "SELECT
    u.`id`,
    u.`login`,
    u.`image`,
    u.`cart`,
    u.`favorites`,
    ar.`right` AS `access_rights`
    FROM
    `users` u
    JOIN
    (
        SELECT
            `user_id`,
            MAX(`id`) AS `latest_session_id`
        FROM
            `sessions`
        WHERE
            `token` = '".$_GET['session_token']."'
        GROUP BY
            `user_id`
    ) latest_session
    ON
    u.`id` = latest_session.`user_id`
    LEFT JOIN
    `access_rights` ar
    ON
    ar.`id` = u.`access_rights`;";

    $res_query = mysqli_query($connection,$query);

    if(!$res_query){
        echo ajax_echo(
            "Ошибка!", 
            "Ошибка в запросе 2!<br>".$query,
            true,
            "ERROR",
            null
        );
        exit();
    }

    $res = mysqli_fetch_assoc($res_query);
    echo ajax_echo(
        "Успех!", 
        "Пользователь авторизирован!",
        false,
        "SUCCESS",
        $res
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
    if(isset($_GET["product_id"])) $id = " AND p.id = ".$_GET["product_id"];
    else $id = "";

    $query = "SELECT
    p.id,
    p.name,
    p.description,
    p.price,
    i.url AS image
    FROM
    products p
    LEFT JOIN
    images i ON i.productid = p.id
    WHERE
    p.deleted = 0".$id;

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
    $query = "UPDATE `products` SET `deleted` = '1' WHERE `products`.`id` = ".$_GET['productid'];
    
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
    $query = "UPDATE `cart_items` ci
    JOIN `users` u ON ci.`cartid` = u.`cart`
    SET ci.`deleted` = 1
    WHERE ci.`product` = ".$_GET['productid']."
    AND u.`id` = ".$_GET['userid'];
    
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
    $query = "SELECT
    p.id,
    p.name,
    p.description,
    p.price,
    i.url AS image
    FROM
    `products` p
    LEFT JOIN
    `images` i ON i.productid = p.id
    WHERE
    p.id IN (
        SELECT ci.`product`
        FROM `cart_items` ci
        JOIN `users` u ON ci.`cartid` = u.`cart`
        WHERE u.`id` = ".$_GET['userid']."
        AND ci.`deleted` = 0
    );";
    
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
    $query = "UPDATE `favorite_items` fi
    JOIN `users` u ON fi.`cartid` = u.`favorites`
    SET fi.`deleted` = 1
    WHERE fi.`product` = ".$_GET['productid']." 
    AND u.`id` = ".$_GET['userid'];
    
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
    $query = "SELECT
    p.id,
    p.name,
    p.description,
    p.price,
    i.url AS image
    FROM
    `products` p
    LEFT JOIN
    `images` i ON i.productid = p.id
    WHERE
    p.id IN (
        SELECT fi.`product`
        FROM `favorite_items` fi
        JOIN `users` u ON fi.`favoriteid` = u.`favorites`
        WHERE u.`id` = ".$_GET['userid']." 
        AND fi.`deleted` = 0
    );";
    
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
    $query = "SELECT
    o.`id`,
    o.`product`,
    o.`adress`,
    os.`name` AS `status`,
    o.`date`
    FROM
    `order` o
    LEFT JOIN
    `order_statuses` os ON os.`id` = o.`status`
    WHERE
    o.`userid` = ".$_GET['userid']." 
    AND o.`deleted` = 0;";
    
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

        $query2 = "SELECT id, name, description, price, (SELECT url FROM images WHERE productid = `products`.`id`) as image FROM products WHERE `deleted` = FALSE AND id = ".$row['product'];
        $rez = mysqli_query($connection, $query2);
        if(!$rez){
            echo ajax_echo(
                "Ошибка!",
                "Ошибка в запросе2!",
                true,
                null
            );
            exit;
        }
        $row['product'] = mysqli_fetch_assoc($rez);

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