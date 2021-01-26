<?php

//TwistOAuth.pharのパス
require 'TwistOAuth-master/build/TwistOAuth.phar';

//APIキー、アクセストークンを設定して認証を通す
define("CONSUMER_KEY","48IFO0a99MrqsW8xZ3AEr9xYx");
define("CONSUMER_SECRET","uY3EAhQeuBqstXR71X4N20JnzMHPdvERTMnuXZrCweoO0z6UN8");
define("ACCESS_TOKEN","1002032671298502658-0avh2JUCW0ue3dnjvrr2F4DUcci8iQ");
define("ACCESS_TOKEN_SECRET","4SIEH9mUzDS9YMY8Gp4idpMG6Y1870DYyhAVlDKK9oS5n");

$connection = new TwistOAuth(CONSUMER_KEY,CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET);

//idを配列に格納
$id_array = array();
//初回かどうか
$first_time = true;
//過去の日付
$past_date = '';
//タイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

//すべてのステータスを格納する配列
$statuses_array = array();

//ツイートが存在する間id_strを取得
do{
  if($flag){
    $date = date('Y-m-d_H:i:s_T');
    $first_time = false;
  } else{
    $time = str_replace('+0000','',$past_date);
    $date = date('Y-m-d_H:i:s_T',strtotime($time));
  }
  $tweets_params = ['q' => '#テスト -rt','count'=>'10','lang' => 'ja','result_type'=>'mixed','until'=>$date];
  try{
    $tweets = $connection->get('search/tweets',$tweets_params);
  }catch(TwistException $e){
    echo $e->getMessage();
    break;
  }
  foreach($tweets->statuses as $tweet){
    $statuses_array[] = $tweet;
    $id_array[] = $tweet->user->id_str;
    $past_date = $tweet->created_at;
  }
  //変数の割当を解除
  unset($tweet);
}while(!empty($tweets));

echo count($test);

// $tweets_params = ['q' => '#テスト -rt','count'=>'11','lang' => 'ja','result_type'=>'mixed'];
// $tweets = $connection->get('search/tweets',$tweets_params);
//
// //idを配列に格納
// $id_array = array();
// foreach($tweets->statuses as $tweet){
//   $id_array[] = $tweet->user->id_str;
// }
// //変数の割当を解除
// unset($tweet);

//配列でのidの重複を削除
$unique = array_unique($id_array);

//重複しないツイート
$result_array = array();
$array_keys = array_keys($unique);
//ステータス内のそれぞれのパラメータを取得
do{
  $key = current($array_keys);
  $tweet = $statuses_array[$key];
  $array = array();
  $array['created_at'] = $tweet->created_at;
  $array['id_str'] = $tweet->user->id_str;
  $array['name'] = $tweet->user->name;
  $array['screen_name'] = $tweet->user->screen_name;
  $array['location'] = $tweet->user->location;
  $array['text'] = str_replace("\n","",$tweet->text);
  $result_array[] = $array;
}while(next($array_keys));

//csvファイルに出力
$now = date('Y-m-d_H:i:s_T');
$fp = fopen($now.'.csv','w');
$line = implode(',',$array_keys);
fputs($fp,mb_convert_encoding($line."\n","SJIS","UTF-8"));
foreach($result_array as $result){
  $line = implode(',',$result);
  fputs($fp,mb_convert_encoding($line."\n","SJIS","UTF-8"));
}
fclose($fp);
