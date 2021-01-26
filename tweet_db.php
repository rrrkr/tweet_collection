<?php

//*******************
// 外部ファイル読み込み
//*******************

//TwistOAuth.pharのパス
require 'TwistOAuth-master/build/TwistOAuth.phar';
//*******************
// 定数定義
//*******************

date_default_timezone_set('Asia/Tokyo');

//APIキー、アクセストークンを設定して認証を通す
const CONSUMER_KEY = "XXXXXXXXXXXXXXXXXXXXXXXx";
const CONSUMER_SECRET = "XXXXXXXXXXXXXXXXXXXXXXXXXx";
const ACCESS_TOKEN = "XXXXXXXXXXXXXXXXXXXXxx";
const ACCESS_TOKEN_SECRET = "XXXXXXXXXXXXXXXXXXXXXx";


//localhost == 127.0.0.1
const LOCALHOST = "127.0.0.1";
const USER_NAME = "XXXXXXXx";
const USER_PASS = "XXXXXXx";
const DB_NAME = "twitter";


//*******************
// DB設定
//*******************

//mysqliクラスのオブジェクト作成
$mysqli = new mysqli(LOCALHOST,USER_NAME,USER_PASS,DB_NAME);

if($mysqli->connect_error){
    echo $mysqli->connect_error;
    exit();
}
$mysqli->set_charset("utf8");

//*******************
// Twitter API設定
//*******************

//インスタンス
$connection = new TwistOAuth(CONSUMER_KEY,CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET);

$search_word =  '#サッカー -rt';
$search_count = '90';
$search_lang = 'ja';
$search_result_type = 'mixed';
$search_until = '2018-07-12';
$search_since_id = '';

//デフォルト検索ツイートパラメータ
$tweets_params = array(
    'q' => $search_word,
    'count' => $search_count,
    'lang' => $search_lang,
    'result_type' =>$seach_result_type
);


$record = $mysqli->query("select id_str from tweets order by created_at desc");
$id = $record->fetch_array(MYSQLI_ASSOC);
$result_id = $id['id_str'];

echo "{$result_id}\n";
if(!empty($result_id)){
    $tweets_params['since_id'] = $result_id;
    echo "yes\n";
}else{
    $tweets_params['until'] = $search_until;
    echo "no\n";
}

//*******************
// ツイート検索
//*******************

$tweets = $connection->get('search/tweets',$tweets_params);


if(empty($tweets)){
    echo "新着ツイートはありません\n";
    exit;
}

//*******************
// ツイート処理
//*******************
$result_array = array();
foreach($tweets->statuses as $tweet){
    $array = array();
    $date = str_replace(" +0000","",$tweet->created_at);
    $array['created_at'] = date("Y-m-d H:i:s",strtotime($date));
    $array['id_str'] = $tweet->id_str;
    $array['name'] = $tweet->user->name;
    $array['screen_name'] = $tweet->user->screen_name;
    $array['location'] = $tweet->user->location;
    $array['text'] = str_replace("\n","",$tweet->text);
    $result_array[] = $array;
}

//*******************
// DBデータ登録
//*******************



foreach($result_array as $result){
    $sql = "insert into tweets("
        ."created_at,"
        ."id_str,"
        ."name,"
        ."screen_name,"
        ."location,"
        ."user_text,"
        ."search_word,"
        ."insert_date"
        .")values(";

    $keys = array_keys($result);

    for($i = 0;$i<count($keys);$i++){
        if($result[$keys[$i]] == null){
            $sql .= "null,";
        }else{
            $sql .= "'{$result[$keys[$i]]}',";
        }
    }
    $sql .= "'{$search_word}',now())";
    $mysqli->query($sql);
}

$mysqli->close();
