<?php
//==============================================================
//　静岡大学情報学部　Rapid Q & A システム　「宴会さん」
//   - Shohei Yokoyama
//==============================================================

//おまじない
mb_language("ja");
mb_internal_encoding("UTF-8");
mb_http_output( "UTF-8" );
setlocale(LC_ALL, 'ja_JP.UTF-8');

//以下、必要に応じて設定してください。==========================
//このPHPプログラムのURL
$URL = ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']!="off")?"https://":"http://").$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
//このURLはメールに記載される事に注意してください。長すぎると、
//受け手のメーラーが勝手に改行したりあるいはリンクにならなかったり
//する可能性があります。


//結果格納ディレクトリ(最初に作ってください)
//  -個人情報の為、必ずWeb非公開に
//  -最後のスラッシュ忘れずに
//  -このPHPプログラムからrw権限必須
$DIR = "vote/";

//管理者パスワード
//  -URLに含む事のできる文字のみ
//  -$URL."?admin=".$PASSWORD が管理者用メニューになります
$PASSWORD = "";
if(file_exists($DIR.'.password')){
    $PASSWORD=trim(file_get_contents($DIR.'.password'));
}
$CONFIG = array();
if(file_exists($DIR.'.config')){
    $CONFIG=json_decode(trim(file_get_contents($DIR.'.config')),true);
}else{
    $CONFIG=array(
        "title"=>"Rapid Q&amp;A Service",
        "color"=>"Indigo"
    );
}

if (php_sapi_name() == 'cli') {
    //--
    echo "Admin Password:        \t\t(default=uniqid())\n> ";
    $PASSWORD = trim(fgets(STDIN));
    if($PASSWORD == ""){
        $PASSWORD = uniqid();
    }
    $rtn = file_put_contents($DIR.'.password',$PASSWORD);
    //--
    echo "\t->set:\t".($rtn?'true':'false')."\n";
    echo "\n";
    //--
    echo "Envelope From Address:\n> ";
    $SENDER = trim(fgets(STDIN));
    $rtn = file_put_contents($DIR.'.sender',$SENDER);
    echo "\t->set:\t".($rtn?'true':'false')."\n";
    echo "\n";
    //--
    echo "Account for Web Server:\t\t(default=nobody)\n>";
    $acc = trim(fgets(STDIN));
    if($acc==""){
        $acc = 'nobody';
    }
    //--
    echo "\n";
    echo "Title of your service\t\t(default=".$CONFIG['title'].")\n> ";
    $rtn = trim(fgets(STDIN));
    if($rtn!=""){
        $CONFIG['title'] = $rtn;
    }
    echo "\n";
    //--
    $init = true;
    do{
        if(!$init){
            echo "[ERROR]===Wrong color. Choose one of the following list.===\n";
        }
        echo "Key color of your service\t\t(default=".$CONFIG['color'].")\n";
        echo "\tRed\tPink\tPurple\tDeepPurple\n";
        echo "\tIndigo\tBlue\tLightBlue\tCyan\n";
        echo "\tTeal\tGreen\tLightGreen\tLime\n";
        echo "\tYellow\tAmber\tOrange\tDeepOrange\n";
        echo "\tBrown\tGrey\tBlueGrey\n> ";
        $rtn = trim(fgets(STDIN));
        if($rtn == ""){
            break;
        }
        $init = false;
    }while(array_search($rtn,array_keys($COL)));
    if($rtn!=""){
        $CONFIG['color'] = $rtn;
    }
    $rtn = file_put_contents($DIR.'.config',json_encode($CONFIG));
    echo "\n";
    //--
    $rtn = chgrp($DIR,$acc);
    echo "chgrp Dir:\t".($rtn?'true':'false')."\n";
    $rtn = chgrp($DIR.'.password',$acc);
    echo "chgrp File[.password]:\t".($rtn?'true':'false')."\n";
    $rtn = chgrp($DIR.'.sender',$acc);
    echo "chgrp File[.sender]:\t".($rtn?'true':'false')."\n";
    $rtn = chgrp($DIR.'.config',$acc);
    echo "chgrp File[.config]:\t".($rtn?'true':'false')."\n";
    $rtn = chmod($DIR,0770);
    echo "chmod Dir:\t".($rtn?'true':'false')."\n";
    $rtn = chmod($DIR.'.password',0640);
    echo "chmod File[.password]:\t".($rtn?'true':'false')."\n";
    $rtn = chmod($DIR.'.sender',0640);
    echo "chmod File[.sender]:\t".($rtn?'true':'false')."\n";
    $rtn = chmod($DIR.'.config',0640);
    echo "chmod File[.config]:\t".($rtn?'true':'false')."\n";
    echo "\n";
    echo "[Done!]\n\tSee index.php?admin=".$PASSWORD."\n\n";
    exit;
}else if($PASSWORD==""){
	echo "Please run index.php from command line to setup EnkaiSan.";
    exit;
}
//===============================================================

$FILE = array("source"=>"member.csv","subject"=>"subject.txt","mail_from"=>"from.txt","mail" => "mail.txt", "sign" => "sign.txt", "choice" => "choice.txt","deadline"=>"deadline.csv","total"=>"total.txt", "pub_total"=>"pubtot.txt" ,"result"=>"result.csv");

$COL = array(
    "Red" => "#f44336",
    "Pink"=> "#E91E63",
    "Purple"=>"#9C27B0",
    "DeepPurple"=>"#673AB7",
    "Indigo"=>"#3F51B5",
    "Blue"=>"#2196F3",
    "LightBlue"=>"#03A9F4",
    "Cyan"=>"#00BCD4",
    "Teal"=>"#009688",
    "Green"=>"#4CAF50",
    "LightGreen"=>"#8BC34A",
    "Lime"=>"#CDDC39",
    "Yellow"=>"#FFEB3B",
    "Amber"=>"#FFC107",
    "Orange"=>"#FF9800",
    "DeepOrange"=>"#FF5722",
    "Brown"=>"#795548",
    "Grey"=>"#9E9E9E",
    "BlueGrey"=>"#607D8B"
);

$lCOL = array(
    "Red" => "#ffcdd2",
    "Pink"=> "#F8BBD0",
    "Purple"=>"#E1BEE7",
    "DeepPurple"=>"#D1C4E9",
    "Indigo"=>"#C5CAE9",
    "Blue"=>"#BBDEFB",
    "LightBlue"=>"#B3E5FC",
    "Cyan"=>"#B2EBF2",
    "Teal"=>"#B2DFDB",
    "Green"=>"#C8E6C9",
    "LightGreen"=>"#DCEDC8",
    "Lime"=>"#F0F4C3",
    "Yellow"=>"#FFF9C4",
    "Amber"=>"#FFECB3",
    "Orange"=>"#FFE0B2",
    "DeepOrange"=>"#FFCCBC",
    "Brown"=>"#D7CCC8",
    "Grey"=>"#E0E0E0",
    "BlueGrey"=>"#CFD8DC"
);

function sendMail($to,$subject,$body,$from,$sender){
    if((@include_once('Mail.php')) !== false){
        $headers = array(
            "To" => $to,
            "From" => $from,
            "Return-Path"=>$sender,
            "Subject" => mb_encode_mimeheader($subject)
        );
        $body = mb_convert_encoding($body,"ISO-2022-JP","AUTO");
        $smtp = Mail::factory('smtp', array());
        $mail = $smtp->send($to, $headers, $body);
        return !(PEAR::isError($mail));
    }else{
        return mb_send_mail($to,$subject,$body,"From: ".$from." \n","-f".$sender);
    }
}

function sumup($results,$items){
	$sum = 0;
	foreach($items as $i){
		foreach($results as $r){
			if($r["choice"]==$i){
				$sum++;
			}
		}
	}
	return $sum;
}

function names ($results,$items){
	$names = array();
	foreach($items as $i){
		foreach($results as $id => $r){
			if($r["choice"]==$i){
				array_push($names,$r["name"]);
			}
		}
	}
	return $names;
}

function printTotal($totals,$results){
    global $COL,$lCOL,$CONFIG;
	$c = 0;
	foreach($totals as $total){
		list($mode,$title,$item) = explode("|",$total);
		if($mode == "-"){
			if($c != 0){
				echo "<br style=\"clear:both;\" />";
			}
			if($title!=""){
				echo '<div style="margin-top:10px;font-size:x-large;border-top: inset 3px '.$COL[$CONFIG['color']].';">'.$title."</div>";
			}
			continue;
		}
		$c++;
		$items = explode(",",trim($item));
		echo '<div style="width:300px;float:left;margin:3px;padding:3px;background-color:'.$COL[$CONFIG['color']].';">';
		echo '<div style="color:#fff;text-align:center;white-space:nowrap;overflow:hidden;width:290px;padding:5px;float:left;background-color:'.$COL[$CONFIG['color']].';">'.$title.'</div>';
		echo '<div style="color:#fff;text-align:center;white-space:nowrap;overflow:hidden;width:290px;padding:5px;float:left;background-color:'.$COL[$CONFIG['color']].';font-size:xx-large;">'.sumup($results,$items).'人</div>';
		$color=array("#fff",$lCOL[$CONFIG['color']],$lCOL[$CONFIG['color']],"#fff");
		$co = 0;
		if($mode == "*"){
			$names = names($results,$items);
			foreach($names as $name){
				echo '<div style="text-align:center;white-space:nowrap;overflow:hidden;width:140px;border-bottom:solid 1px #393e4f;border-right:solid 1px #393e4f;border-top:solid 1px #d7a98c;border-left:solid 1px #d7a98c;padding:4px;float:left;background-color:'.$color[$co++%4].';">'.$name.'</div>';
			}
		}
		echo "</div>";
	}
	echo '<br style="clear:both;"/>';
}

function getMailBody($name,$nakami,$choice,$URL,$id,$sign){
	$mail   =$name."　様\n\n";
	$mail  .=$nakami;
	$mail  .= "\n\n=====\n";
	foreach($choice as $k => $item){
		$mail .= trim($item)."\n　".$URL."?u=".$id."&v=".$k."\n\n";
	}
	$mail .= "=====\n現在の登録状況を見る：\n　　".$URL."?u=".$id."\n\n";
	$mail .= $sign;
	return $mail;
}
?><html>
<head>
	<title><?=$CONFIG['title']?> - 宴会さん</title>
    <script   src="https://code.jquery.com/jquery-3.1.1.min.js"   integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="   crossorigin="anonymous"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<?php
if(!isset($_REQUEST["u"])){
	if(isset($_REQUEST["admin"]) and $PASSWORD == $_REQUEST["admin"]){
		if(!isset($_REQUEST["mode"])){
			//管理画面
			if(!file_exists($DIR.$FILE["result"])){
?><div class="jumbotron">
<h1><?~$CONFIG['title']?>　<small>宴会さん</small></h1>
</div>
<form enctype="multipart/form-data" action="<?=$URL?>" method="POST">
<h1>アンケート作成</h1>
<h2>メールヘッダ情報</h2>
メール件名:<input type="text" name="mail_" size="70" /><br />
差出人名:<input type="text" name="mail_fromname" size="70" /><br />
差出人メールアドレス:<input type="text" name="mail_from" size="70" /><br />
※差出人は相手先に表示されるアドレス<br/>
<h2>メール通知文</h2>
●●●●様　(メール冒頭に宛名が自動で挿入されます)<br/>
<textarea name="mail" rows="10" cols="60" wrap="hard">お世話になっております。以下のアンケートにお答えください。

真に勝手ながら●月●日までにご回答を頂きたくお願い申し上げます。

回答方法ですが、ご希望の項目の直下にあるURLをクリックしてください。締切までは何度でもご回答いただけます。複数回のご回答を頂いたときは最後にご登録頂いたものをご回答とさせていただきます。</textarea>
<h2>アンケート選択肢</h2>
<textarea name="choice" rows="4" cols="60" wrap="soft">参加する
参加するが遅刻する
参加しない</textarea>
<h2>署名</h2>
<textarea name="sign" rows="3" cols="60" wrap="hard">以上の件よろしくお願いします。
--
静岡大学情報学部</textarea><br /><br />
<h2>回答〆切(YYYY/MM/DD)</h2>
<input type="text" name="deadline" size="50"/> 本当の〆切:＋<input type="text" name="deadline_" size="5" value="9" />時間後
<h2>集計項目</h2>
<input type="checkbox" name="public" value="checked">集計結果を回答者にも公開する。</input><br/>
<textarea name="total" rows="4" cols="60" wrap="hard" style="background-color:#ddd;">-|参加・不参加一覧|
*|参加者数|0,1
*|不参加数|2
-|参加内訳|
+|フル参加|0
+|遅刻|1</textarea><br />
[<a href="#" onclick="document.getElementById('setsumei').style.display='block';return false;">説明</a>]
<div id="setsumei" style="display:none;margin:0% 5%;padding:5px 20px;border: 1px dotted #ff0000;"><p>
<b>概要</b><br/>
集計結果ページへ表示する項目を定義します。各行、見出しの表示、名簿の表示、人数の表示を定義でき、それらは集計結果ページに順に表示されます。
</p><p>
<b>-|参加・不参加一覧|</b><br/>
<b>-|</b>で始まる行は大見出しを表します。結果を見やすくする為に使います。
</p><p>
<b>*|参加者数|0,1</b><br/>
<b>*|</b>で始まる行は名簿の表示を意味します。項目見出しに続いて、集計項目をカンマで区切って定義します。この例ではアンケート選択肢の0番目の項目(参加する)と1番目の項目(参加するが遅刻する)を併せて表示します。
</p><p>
<b>+|フル参加|0</b><br/>
<b>+|</b>で始まる行は人数のみの表示を意味します。項目見出しに続いて、集計項目をカンマで区切って定義します。この例ではアンケート選択肢の0番目の項目(参加する)を登録した人数を表示します。
</p>[<a href="#" onclick="document.getElementById('setsumei').style.display='none';return false;">閉じる</a>]</div>
<h2>送信先リスト</h2>
名簿CSVファイル: <input name="mailtolist" type="file" /><br/>
※各行「名前,メールアドレス」の順<br/>
※エクセルで作成し、ファイルの種類を「<b>CSV(カンマ区切り)</b>」で保存したファイルも読み込めます。<br/>
<br/>
<input type="submit" name="mode" value="create" style="font-size:36px;padding-left:80px;padding-right:80px;"><br/>
※createを押すと、確認画面が表示されます。(メールはまだ送信されません)
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
</form>
<?php
			}else{
?>
<h1 style="border-top: inset 10px <?=$COL[$CONFIG['color']]?>;">管理メニュー</h1>
<h2 style="border-left: inset 50px <?=$COL[$CONFIG['color']]?>;border-top: inset 1px <?=$COL[$CONFIG['color']]?>;">アンケート送付先追加</h2>
<form action="<?=$URL?>" method="POST">
メールアドレス<input type="text" name="mail" size="70" /><br/>
氏名<input type="text" name="name" size="70" /><br/>
<input type="checkbox" name="now" value="checked" checked>今すぐメールを送信する</input><br/>
<input type="submit" name="mode" value="add" style="font-size:36px;padding-left:80px;padding-right:80px;"><br />
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
</form>
<h2 style="border-left: inset 50px <?=$COL[$CONFIG['color']]?>;border-top: inset 1px <?=$COL[$CONFIG['color']]?>;">アンケート再送(未報告者のみ)</h2>
<form action="<?=$URL?>" method="POST">
<input type="submit" name="mode" value="send" style="font-size:36px;padding-left:80px;padding-right:80px;"><br />
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
<input type="hidden" name="resend" value="true">
</form>
<h2 style="border-left: inset 50px <?=$COL[$CONFIG['color']]?>;border-top: inset 1px <?=$COL[$CONFIG['color']]?>;">アンケート初期化</h2>
<form action="<?=$URL?>" method="POST">
管理者パスワード:<input type="text" name="admin"/><br/>
<input type="submit" name="mode" value="delete" style="font-size:36px;padding-left:80px;padding-right:80px;"><br />
※アンケート結果も全て消去されます。
</form>
<h1 style="border-top: inset 10px <?=$COL[$CONFIG['color']]?>;">集計結果</h1>
<?php
				$result = file($DIR.$FILE["result"]);
				$results = array();
				foreach($result as $res){
					list($id,$time,$name,$choice,$note) = explode(",",$res);
					$results[$id] = array("name"=>$name,"choice"=>$choice,"note"=>trim($note));
				}
				if(count($results) == 0){
					echo "まだ登録がありません<br/>";
				}else{
					printTotal(file($DIR.$FILE["total"]),$results);
				}
?><h1 style="border-top: inset 10px <?=$COL[$CONFIG['color']]?>;">全結果</h1>
<?php
				if(count($results) == 0){
					echo "まだ登録がありません<br/>";
				}else{
					echo '※エクセルにコピペできます。<br/>';
					echo '<textarea onclick="this.focus();this.select()" rows="'.count($results).'" cols="100" wrap="off">';
					foreach($results as $result){
						echo "".$result["name"]."\t".$result["note"]."\r";
					}
					echo "</textarea>";
				}
?><hr/>
<?php
			}
		}else{
			if($_REQUEST["mode"] == "create"){
				//vote作成
				echo "<h1>アンケート作成</h1>";
				//入力値チェック
				$ERROR = array();
				$NOTICE = array();
				foreach($FILE as $file){
					if(file_exists($DIR.$file)){
						$ERROR[] ="既にアンケート用ファイルが存在します。新規のアンケートを作成する場合は、古いファイルを削除してください。";
						break;
					}
				}
				if($_FILES['mailtolist']['size'] <= 0 or $_FILES['mailtolist']['error'] != 0){
					$ERROR[] = "CVSファイルのアップロードに失敗しました。";

				}
				if(!isset($_REQUEST["mail_from"]) or $_REQUEST["mail_from"] == ""){
					$ERROR[] = "差出人メールアドレスが記入されていません。";
				}
				if(!isset($_REQUEST["mail_fromname"]) or $_REQUEST["mail_fromname"] == ""){
					$ERROR[] = "差出人名が記入されていません。";
				}
				if(!isset($_REQUEST["mail_"]) or $_REQUEST["mail_"] == ""){
					$ERROR[] = "メール題名が記入されていません。";
				}
				if(!isset($_REQUEST["mail"]) or $_REQUEST["mail"] == ""){
					$ERROR[] = "メール本文が記入されていません。";
				}
				if(!isset($_REQUEST["choice"]) or $_REQUEST["choice"] == ""){
					$ERROR[] = "アンケート選択肢が記入されていません。";
				}
				if(!isset($_REQUEST["sign"]) or $_REQUEST["sign"] == ""){
					$ERROR[] = "メール署名が記入されていません。";
				}
				if(isset($_REQUEST["deadline"]) and $_REQUEST["deadline"] != ""){
					$time = strtotime($_REQUEST["deadline"]);
					if($time === FALSE){
						$ERROR[] = "〆切日の日付フォーマットが異なっています。";
					}else{
						$softdead = $time + 24 * 60 * 60;//その日の終わり
					}
				}else{
					$softdead = time();
					$ERROR[] = "アンケート〆切日が記入されていません";
				}
				if(isset($_REQUEST["deadline_"]) and $_REQUEST["deadline_"] != ""){
					if(is_numeric($_REQUEST["deadline_"])){
						if(intval($_REQUEST["deadline_"]) < 0){
							$ERROR[] = "本当の〆切には正の整数値を入れてください．";
						}else{
							$harddead = $softdead + (intval($_REQUEST["deadline_"]) * 60 * 60);
						}
					}else{
						$ERROR[] = "本当の〆切には正の整数値を入れてください。";
					}
				}else{
					$ERROR[] = "本当の〆切が記入されていません。";
				}
				if(count($ERROR) != 0){
					echo '<h2 style="color:red;">[失敗]アンケートは作成されませんでした</h2>';
					echo implode("<br/>",$ERROR);
					exit(0);
				}
				//アンケート生成
				$MAIL = $_REQUEST["mail_from"];
				$KANJI = $_REQUEST["mail_fromname"];
				$SENDER = file_get_contents($DIR.".sender");
				file_put_contents($DIR.$FILE["mail_from"],$SENDER."*".$MAIL."*".$KANJI);
				file_put_contents($DIR.$FILE["subject"],$_REQUEST["mail_"]);
				file_put_contents($DIR.$FILE["mail"],$_REQUEST["mail"]);
				file_put_contents($DIR.$FILE["sign"],$_REQUEST["sign"]);
				$choice = preg_replace("/(\r\n|\n|\r)/", "\n", $_REQUEST["choice"]);
				file_put_contents($DIR.$FILE["choice"],$choice);
				if(!isset($_REQUEST["total"]) or trim($_REQUEST["total"]) == ""){
					$cho = explode("\n",$choice);
					$_REQUEST["total"] = "-|集計結果|\n";
					$c = 0;
					foreach($cho as $ch){
						$_REQUEST["total"] .= "*|".$ch."|".$c++."\n";
 					}
				}
				file_put_contents($DIR.$FILE["total"],preg_replace("/(\r\n|\n|\r)/", "\n", $_REQUEST["total"]));
				if(isset($_REQUEST["public"]) and $_REQUEST["public"] == "checked"){
					file_put_contents($DIR.$FILE["pub_total"],preg_replace("/(\r\n|\n|\r)/", "\n", $_REQUEST["total"]));
				}
				file_put_contents($DIR.$FILE["result"],"");
				//list($y,$m,$d)=explode("/",$_REQUEST["deadline"]);
				//file_put_contents($DIR.$FILE["deadline"],mktime(23,59,59,$m,$d,$y).",".(mktime(23,59,59,$m,$d,$y)+($_REQUEST["deadline_"]*60*60)));
				file_put_contents($DIR.$FILE["deadline"],$softdead.",".$harddead);
				//$members = file($SRC);
				
				$mailtolist = mb_convert_encoding(file_get_contents($_FILES['mailtolist']['tmp_name']), "UTF-8", "auto");

				$fp = tmpfile();
				fwrite($fp, $mailtolist);
				rewind($fp);
				$mems = array();
				$c = 0;
				echo "<h2>ご確認ください</h2><table border=1>";
				while($line = fgetcsv($fp)) {
					array_push($mems,md5(uniqid()).",".$line[0].",".$line[1]."\n");
					echo "<tr><td>".(++$c)."</td><td>".$line[0]."</td><td>".$line[1]."</td></tr>";
				}
				echo "</table>";
				fclose($fp);
				file_put_contents($DIR.$FILE["source"],implode("",$mems));
				
				echo "アンケート生成完了!<br/>";
				//例文作成
				$members = file($DIR.$FILE["source"]);
				list($id,$name,$mailto) = explode(",",$members[0]);
				$mailto = trim($mailto);
				$mail = getMailBody($name,file_get_contents($DIR.$FILE["mail"]),file($DIR.$FILE["choice"]),$URL,$id, file_get_contents($DIR.$FILE["sign"]));
?>

<form action="<?=$URL?>" method="POST">
<h1>アンケートを作り直す</h1>
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
<input type="submit" name="mode" value="delete" style="font-size:36px;padding-left:80px;padding-right:80px;"><br />
</form>

<form action="<?=$URL?>" method="POST">
<h1>アンケート送信</h1>
<input type="submit" name="mode" value="send" style="font-size:36px;padding-left:80px;padding-right:80px;"><br/>
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
</form>
<hr/>
<?php
				echo "<pre style=\"width:500px;padding:10px;background-color:#ddd;\">".$mail."</pre>";
			}elseif($_REQUEST["mode"] == "add"){
				list($SENDER,$MAIL,$KANJI) = explode("*",file_get_contents($DIR.$FILE["mail_from"]),3);
				$MAILHEADER = mb_encode_mimeheader (mb_convert_encoding($KANJI,"ISO-2022-JP","AUTO")).
       				"<".$MAIL.">";
				$subject=file_get_contents($DIR.$FILE["subject"]);
				if(isset($_REQUEST["resend"])){
					$subject="再送 - ".$subject;
				}
				$nakami = file_get_contents($DIR.$FILE["mail"]);
				$sign = file_get_contents($DIR.$FILE["sign"]);
				$choice = file($DIR.$FILE["choice"]);
				$id= md5(uniqid());
				$name= $_REQUEST["name"];
				$mailto= $_REQUEST["mail"];
				file_put_contents($DIR.$FILE["source"],$id.",".$name.",".$mailto."\n",FILE_APPEND | LOCK_EX);
				$mail = getMailBody($name,$nakami,$choice,$URL,$id,$sign);
				if(isset($_REQUEST["now"]) and $_REQUEST["now"] == "checked"){
					echo $name."様(".$mailto.")へ送信--";
					//$r = mb_send_mail($mailto,$subject,$mail,$MAILHEADER,"-f".$SENDER);
                    $r = sendMail($mailto,$subject,$mail,$MAILHEADER,$SENDER);
					echo $r?"[成功]<br/>":"[失敗]<br/>";
				}else{
					echo "<h2>以下のようなアンケートメールが再送時に送られます</h2>";
					echo "<pre style=\"width:500px;padding:10px;background-color:#ddd;\">".$mail."</pre>"; 
				}
			}elseif($_REQUEST["mode"] == "delete"){
				foreach($FILE as $k => $file){
					if(file_exists($DIR.$file)){
						unlink($DIR.$file);
					}
				}
				echo '<h1>アンケート削除完了！</h1><p><a href="'.$URL.'?admin='.$PASSWORD.'">新規アンケート作成画面</a>へ</p>';
			}elseif($_REQUEST["mode"] == "send"){
				//vote送信
				list($SENDER,$MAIL,$KANJI) = explode("*",file_get_contents($DIR.$FILE["mail_from"]),3);
				$MAILHEADER = mb_encode_mimeheader (mb_convert_encoding($KANJI,"ISO-2022-JP","AUTO"))."<".$MAIL.">";
				echo "<h1>アンケート送信！</h1>";
				$members = file($DIR.$FILE["source"]);
				$sent = array();
				if(isset($_REQUEST["resend"])){
						$results=file($DIR.$FILE["result"]);
						foreach($results as $result){
							list($id,$sonota) = explode(",",$result,2);
							$sent[$id] = true;
						}
				}
				$subject=file_get_contents($DIR.$FILE["subject"]);
				if(isset($_REQUEST["resend"])){
					$subject="再送 - ".$subject;
				}
				$nakami = file_get_contents($DIR.$FILE["mail"]);
				$sign = file_get_contents($DIR.$FILE["sign"]);
				$choice = file($DIR.$FILE["choice"]);
				foreach($members as $member){
					list($id,$name,$mailto) = explode(",",$member);
					if(isset($sent[$id])){
						continue;
					}
					$mailto = trim($mailto);
					$mail = getMailBody($name,$nakami,$choice,$URL,$id,$sign);
					echo $name."様(".$mailto.")へ送信--";
					//$r = mb_send_mail($mailto,$subject,$mail,$MAILHEADER,"-f".$SENDER);
                    $r = sendMail($mailto,$subject,$mail,$MAILHEADER,$SENDER);
					echo $r?"[成功]<br/>":"[失敗]<br/>";
				}
				echo "<h1>管理・集計ページ</h1>";
				echo "アンケートの管理・集計結果の閲覧は以下のページをご覧ください。<br/>";
				echo '<a href="'.$URL.'?admin='.$_REQUEST["admin"].'">'.$URL.'?admin='.$_REQUEST["admin"].'</a>';
			}
		}
	}else{
		//無視
		echo "hello world!";
	}
}else{
	$m = file($DIR.$FILE["source"]);
	$members = array();
	foreach($m as $n){
		list($id,$name,$mailto) = explode(",",$n);
		$members[$id]= $name;
	}
	$choice = file($DIR.$FILE["choice"]);
	if(!isset($members[$_REQUEST["u"]])){?>
	<div style="overflow:auto;height:250px;width:400px;margin-left : auto ; margin-right : auto ;background-image:url(votebg.gif);">
		<div style="text-align:center;margin:40px 40px 10px 40px;font-size:16px;">エラー</div>
		<div style="text-align:justify;margin:30px 40px 20px 40px;font-size:16px;">　この投票URLは有効ではありません。既にアンケートは修了したか、もしくはURLの一部が欠損している可能性があります。メール内に記載されたURLをもう一度ご確認ください。</div>
	</div>
<?php		exit;
	}
	if(!isset($_REQUEST["v"])){
		//結果一覧モード
		$NAME=$members[$_REQUEST["u"]];
		$NOTE="まだご登録頂いておりません。";
		$result = file($DIR.$FILE["result"]);
		$results = array();
		foreach($result as $res){
			list($id,$time,$name,$choice,$note) = explode(",",$res);
			$results[$id] = array("name"=>$name,"choice"=>$choice,"note"=>trim($note));
			if($NAME == $name){
				$NOTE = trim($note);
			}
		}
		?>
	<div style="overflow:auto;height:250px;width:400px;margin-left : auto ; margin-right : auto ;background-image:url(votebg.gif);">
		<div style="text-align:center;margin:40px 40px 10px 40px;font-size:16px;"><?=$NAME?>　様</div>
		<div style="text-align:justify;margin:30px 40px 20px 40px;font-size:16px;">　現在の登録状況は以下のようになっております。登録内容を修正する場合はアンケートメールのURLをクリックしてください。</div>
		<div style="color:<?=$COL[$CONFIG['color']]?>;text-align:center;margin:10px 40px 10px 40px;font-size:16px;"><?=$NOTE?></div>
	</div>
<?php
		if(file_exists($DIR.$FILE["pub_total"])){
			echo '<h1 style="border-top: inset 10px <?=$COL[$CONFIG[\'color\']]?>;">集計結果</h1>';
			printTotal(file($DIR.$FILE["pub_total"]),$results);
		}
	}else{
		//投票モード
		list($SENDER,$MAIL,$KANJI) = explode("*",file_get_contents($DIR.$FILE["mail_from"]),3);
		$NAME=$members[$_REQUEST["u"]];
		list($d,$deadline)=explode(",",file_get_contents($DIR.$FILE["deadline"]));
		if(time() > intval($deadline)){
			//期限切れ
			?>
	<div style="overflow:auto;height:250px;width:400px;margin-left : auto ; margin-right : auto ;background-image:url(votebg.gif);">
		<div style="text-align:center;margin:40px 40px 10px 40px;font-size:16px;"><?=$NAME?>　様</div>
		<div style="text-align:justify;margin:30px 40px 20px 40px;font-size:16px;">　本アンケートの回答は締め切られました。回答の変更等は直接[<a href="mailto:<? echo $MAIL; ?>"><? echo $KANJI; ?></a>]へご連絡ください。</div>
		<div style="color:<?=$COL[$CONFIG['color']]?>;text-align:center;margin:10px 40px 10px 40px;font-size:16px;">期限：<? echo date("Y年m月d日",$d) ?></div>
	</div>
<?php			exit;
		}
		$NAME=$members[$_REQUEST["u"]];
		$ID  = $_REQUEST["u"];
		$VALUE= $_REQUEST["v"];
		$CHOICE = trim($choice[$_REQUEST["v"]]); ?>
	<div style="overflow:auto;height:250px;width:400px;position:absolute;top:50%;left:50%;margin-top:-125px;margin-left:-200px;background-image:url(votebg.gif);">
		<div style="text-align:center;margin:40px 40px 10px 40px;font-size:16px;"><?=$NAME?>　様</div>
		<div style="text-align:justify;margin:30px 40px 20px 40px;font-size:16px;">　アンケートへのご協力ありがとうございました。下記のご回答承りました。</div>
		<div style="color:<?=$COL[$CONFIG['color']]?>;text-align:center;margin:10px 40px 10px 40px;font-size:16px;"><?=$CHOICE?></div>
<?php
		if(file_exists($DIR.$FILE["pub_total"])){
?>		<div style="text-align:center;margin:20px 40px 10px 40px;font-size:16px;">[<a href="<?=$URL?>?u=<?=$ID?>">集計結果を見る</a>]</div>
<?php
		}
?>
	</div>
<?php
		file_put_contents($DIR.$FILE["result"], $ID.",".microtime (true).",".$NAME.",".$VALUE.",".$CHOICE."\n", FILE_APPEND | LOCK_EX);
	}
}
?>
</body>
</html>