<?php
//==============================================================
//　Rapid Q & A システム　「宴会さん」
//   - Shohei Yokoyama
//==============================================================

//おまじない
mb_language("ja");
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");
setlocale(LC_ALL, 'ja_JP.UTF-8');
date_default_timezone_set('Asia/Tokyo');

//結果格納ディレクトリ(デフォルトから変更する時はここを修正してください)
//  -個人情報の為、必ずWeb非公開に
$DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . "vote" . DIRECTORY_SEPARATOR;

//--------------------------------------------------------------プログラム本体
$PASSWORD = "";
if (file_exists($DIR . '.password')) {
    $PASSWORD = trim(file_get_contents($DIR . '.password'));
}
$CONFIG = array();
if (file_exists($DIR . '.config')) {
    $CONFIG = json_decode(trim(file_get_contents($DIR . '.config')), true);
} else {
    $CONFIG = array(
        "title" => "Enkai San (Rapid QA Service)",
        "color" => "Indigo",
    );
}

if (!is_writable($DIR)) {
    echo "[ERROR] " . $DIR . " isn't writeable.";
    exit;
}

$COLOR = array(
    "Red" => array("dark" => "#f44336", "light" => "#ffcdd2"),
    "Pink" => array("dark" => "#E91E63", "light" => "#F8BBD0"),
    "Purple" => array("dark" => "#9C27B0", "light" => "#E1BEE7"),
    "DeepPurple" => array("dark" => "#673AB7", "light" => "#D1C4E9"),
    "Indigo" => array("dark" => "#3F51B5", "light" => "#C5CAE9"),
    "Blue" => array("dark" => "#2196F3", "light" => "#BBDEFB"),
    "LightBlue" => array("dark" => "#03A9F4", "light" => "#B3E5FC"),
    "Cyan" => array("dark" => "#00BCD4", "light" => "#B2EBF2"),
    "Teal" => array("dark" => "#009688", "light" => "#B2DFDB"),
    "Green" => array("dark" => "#4CAF50", "light" => "#C8E6C9"),
    "LightGreen" => array("dark" => "#8BC34A", "light" => "#DCEDC8"),
    "Lime" => array("dark" => "#CDDC39", "light" => "#F0F4C3"),
    "Yellow" => array("dark" => "#FFEB3B", "light" => "#FFF9C4"),
    "Amber" => array("dark" => "#FFC107", "light" => "#FFECB3"),
    "Orange" => array("dark" => "#FF9800", "light" => "#FFE0B2"),
    "DeepOrange" => array("dark" => "#FF5722", "light" => "#FFCCBC"),
    "Brown" => array("dark" => "#795548", "light" => "#D7CCC8"),
    "Grey" => array("dark" => "#9E9E9E", "light" => "#E0E0E0"),
    "BlueGrey" => array("dark" => "#607D8B", "light" => "#CFD8DC"),
);

if (php_sapi_name() == 'cli') {
    //--
    echo "Admin Password:        \t\t(default=uniqid())\n> ";
    $PASSWORD = trim(fgets(STDIN));
    if ($PASSWORD == "") {
        $PASSWORD = uniqid();
    }
    $rtn = file_put_contents($DIR . '.password', $PASSWORD);
    //--
    echo "\t->set:\t" . ($rtn ? 'true' : 'false') . "\n";
    echo "\n";
    //--
    echo "Envelope From Address:\n> ";
    $SENDER = trim(fgets(STDIN));
    $rtn = file_put_contents($DIR . '.sender', $SENDER);
    echo "\t->set:\t" . ($rtn ? 'true' : 'false') . "\n";
    echo "\n";
    //--
    echo "Username of Web Server:\t\t(default=apache)\n>";
    $acc = trim(fgets(STDIN));
    if ($acc == "") {
        $acc = 'apache';
    }
    //--
    echo "\n";
    echo "Title of Your Service\t\t(default=" . $CONFIG['title'] . ")\n> ";
    $rtn = trim(fgets(STDIN));
    if ($rtn != "") {
        $CONFIG['title'] = $rtn;
    }
    echo "\n";
    //--
    $init = true;
    do {
        if (!$init) {
            echo "[ERROR]===Wrong color. Choose one of the following list.===\n";
        }
        echo "Key color of your service\t\t(default=" . $CONFIG['color'] . ")\n\t";
        $colors = array_keys($COLOR);
        $width = 0;
        foreach ($colors as $color) {
            $width = max($width, strlen($color));
        }
        $width += 4;
        $format = '%-' . $width . 's';
        $c = 0;
        foreach ($colors as $color) {
            if ($c++ % 4 == 0) {
                echo "\n\t";
            }
            printf($format, $color);
        }
        echo "\n>";
        $rtn = trim(fgets(STDIN));
        if ($rtn == "") {
            break;
        }
        $init = false;
    } while (!array_key_exists($rtn, $COLOR));
    if ($rtn != "") {
        $CONFIG['color'] = $rtn;
    }
    $rtn = file_put_contents($DIR . '.config', json_encode($CONFIG));
    echo "\n";
    //--
    $rtn = chgrp($DIR, $acc);
    echo "chgrp Dir:\t" . ($rtn ? 'true' : 'false') . "\n";
    $rtn = chgrp($DIR . '.password', $acc);
    echo "chgrp File[.password]:\t" . ($rtn ? 'true' : 'false') . "\n";
    $rtn = chgrp($DIR . '.sender', $acc);
    echo "chgrp File[.sender]:\t" . ($rtn ? 'true' : 'false') . "\n";
    $rtn = chgrp($DIR . '.config', $acc);
    echo "chgrp File[.config]:\t" . ($rtn ? 'true' : 'false') . "\n";
    $rtn = chmod($DIR, 0770);
    echo "chmod Dir:\t" . ($rtn ? 'true' : 'false') . "\n";
    $rtn = chmod($DIR . '.password', 0640);
    echo "chmod File[.password]:\t" . ($rtn ? 'true' : 'false') . "\n";
    $rtn = chmod($DIR . '.sender', 0640);
    echo "chmod File[.sender]:\t" . ($rtn ? 'true' : 'false') . "\n";
    $rtn = chmod($DIR . '.config', 0640);
    echo "chmod File[.config]:\t" . ($rtn ? 'true' : 'false') . "\n";
    echo "\n";
    echo "[Done!]\n\tSee index.php?admin=" . $PASSWORD . "\n\n";
    exit;
} else if ($PASSWORD == "") {
    echo "Please run index.php from command line to setup EnkaiSan.";
    exit;
}
//===============================================================

$FILE = array("source" => "member.csv", "subject" => "subject.txt", "mail_from" => "from.txt", "mail" => "mail.txt", "sign" => "sign.txt", "choice" => "choice.txt", "deadline" => "deadline.csv", "total" => "total.txt", "pub_total" => "pubtot.txt", "result" => "result.csv", "event" => "event.txt");

$URL = ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != "off") ? "https://" : "http://") . $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"];

function sendMail($to, $subject, $body, $from, $sender)
{
    if ((@include_once ('Mail.php')) !== false) {
        $headers = array(
            "To" => $to,
            "From" => $from,
            "Return-Path" => $sender,
            "Subject" => mb_encode_mimeheader($subject),
        );
        $body = mb_convert_encoding($body, "ISO-2022-JP", "AUTO");
        $smtp = Mail::factory('smtp', array());
        $mail = $smtp->send($to, $headers, $body);
        return !(PEAR::isError($mail));
    } else {
        return mb_send_mail($to, $subject, $body, "From: " . $from . " \n", "-f" . $sender);
    }
}

function sumup($results, $items)
{
    $sum = 0;
    foreach ($items as $i) {
        foreach ($results as $r) {
            if ($r["choice"] == $i) {
                $sum++;
            }
        }
    }
    return $sum;
}

function names($results, $items)
{
    $names = array();
    foreach ($items as $i) {
        foreach ($results as $id => $r) {
            if ($r["choice"] == $i) {
                array_push($names, $r["name"]);
            }
        }
    }
    return $names;
}
function printTotal($totals, $results)
{
    global $COLOR, $CONFIG;
    $c = 0;
    echo '<div class="row">';
    foreach ($totals as $total) {
        list($mode, $title, $item) = explode("|", $total);
        if ($mode == "-") {
            //if($c != 0){
            //    echo "<br style=\"clear:both;\" />";
            //}
            echo '<div class="w-100"></div>';
            if ($title != "") {
                echo '<div class="col-12"><h2>' . $title . "</h2></div>";
            }
            continue;
        }
        $c++;
        $items = explode(",", trim($item));
        echo '<div class="col">';
        echo '<div class="container" style="background-color:' . $COLOR[$CONFIG['color']]['dark'] . ';">';
        echo '<div class="row">';
        echo '<div class="col-12 text-white text-center"><h3>' . $title . '</h3></div>';
        echo '<div class="col-12 text-white text-center"><h4>' . sumup($results, $items) . '人</h4></div>';
        $color = array("#fff", $COLOR[$CONFIG['color']]["light"], $COLOR[$CONFIG['color']]["light"], "#fff");
        $co = 0;
        if ($mode == "*") {
            $names = names($results, $items);
            foreach ($names as $name) {
                //echo '<div class="col-md-6" style="background-color:'.$color[$co++%4].';">'.$name.'</div>';
                echo '<span class="col-md-6 badge badge-pill badge-light" style="background-color:' . $color[$co++ % 4] . ';">' . $name . '</span>';
            }
        }
        echo "</div>";
        echo '<div class="col-12">&nbsp;</div>';
        echo "</div>";
        echo "</div>";
    }
    echo '</div>';
}

function getMailBody($name, $nakami, $choice, $URL, $id, $sign)
{
    $mail = $name . "　様\n\n";
    $mail .= $nakami;
    $mail .= "\n\n=====\n";
    foreach ($choice as $k => $item) {
        $mail .= trim($item) . "\n　" . $URL . "?u=" . $id . "&v=" . $k . "\n\n";
    }
    $mail .= "=====\n現在の登録状況を見る：\n　　" . $URL . "?u=" . $id . "\n\n";
    $mail .= $sign;
    return $mail;
}

function printResults()
{
    global $DIR,$FILE;
    $result = file($DIR . $FILE["result"]);
    $results = array();
    foreach ($result as $res) {
        list($id, $time, $name, $choice, $note) = explode(",", $res);
        $results[$id] = array("name" => $name, "choice" => $choice, "note" => trim($note));
    }
    if (count($results) == 0) {
        echo "まだ登録がありません<br/>";
    } else {
        printTotal(file($DIR . $FILE["total"]), $results);
    }
    return $results;
}

function getCurrentResult($NAME)
{
    global $DIR,$FILE;
    $NOTE = "まだご登録を頂いておりません。";
    $result = file($DIR . $FILE["result"]);
    $results = array();
    foreach ($result as $res) {
        list($id, $time, $name, $choice, $note) = explode(",", $res);
        $results[$id] = array("name" => $name, "choice" => $choice, "note" => trim($note));
        if ($NAME == $name) {
            $NOTE = trim($note);
        }
    }
    return $NOTE;
}

?><!DOCTYPE html>
<html>
<head lang="ja">
	<meta charset="utf-8">
	<title><?=$CONFIG['title']?> - 宴会さん</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>
<body>
<?php
if (!isset($_REQUEST["u"])) {
    if (isset($_REQUEST["admin"]) and $PASSWORD == $_REQUEST["admin"]) {
        if (!isset($_REQUEST["mode"])) {
            //管理画面
            if (!file_exists($DIR . $FILE["result"])) {
                ?>
<div class="container">
<div class="jumbotron text-white" style="background:<?=$COLOR[$CONFIG['color']]['dark']?>;">
  <h1 class="display-4"><?=$CONFIG['title']?></h1>
  <p class="lead">宴会の出欠調査をメールで簡単に行うためのシステムです。</p>
  <hr class="my-4">
	<p class="lead">
    <a class="btn btn-primary btn-lg" href="#formHead" role="button">はじめる</a>
  </p>
</div>

<a name="formHead">
<form enctype="multipart/form-data" action="<?=$URL?>" method="POST">
<h2>アンケート作成</h2>
<h3>イベントについて</h3>
<div class="form-group">
    <label for="inputEvent">イベント名</label>
    <input type="text" name="event" class="form-control" id="inputEvent" placeholder="「教員親睦会 新任教員歓迎会 (〇月●日)」等イベント名を入力してください。">
</div>
<h3>メールヘッダ情報</h3>
<div class="form-group">
    <label for="inputMail">メール件名</label>
    <input type="text" name="mail_" class="form-control" id="inputMail" placeholder="ここに記入したメール件名で送信されます。">
</div>

<div class="form-group">
    <label for="inputFromName">差出人名</label>
    <input type="text" name="mail_fromname" class="form-control" id="inputFromName" placeholder="メールの送信元として表示される名前を入力してください。">
</div>

<div class="form-group">
    <label for="inputFrom">差出人メールアドレス</label>
    <input type="email" name="mail_from" class="form-control" id="inputFrom" placeholder="メールの送信元として表示されるメールアドレスを入力してください。">
</div>
<hr class="my-4">
<h3>メール通知文</h3>
●●●●様　(メール冒頭に宛名が自動で挿入されます)<br/>
<div class="form-group">
<textarea name="mail" rows="10" cols="60" class="form-control" wrap="hard">
お世話になっております。以下のアンケートにお答えください。

真に勝手ながら●月●日までにご回答を頂きたくお願い申し上げます。

回答方法ですが、ご希望の項目の直下にあるURLをクリックしてください。締切までは何度でもご回答いただけます。複数回のご回答を頂いたときは最後にご登録頂いたものをご回答とさせていただきます。
</textarea>
</div>

<h3>アンケート選択肢</h3>
<div class="form-group">
<textarea name="choice" rows="4" cols="60" class="form-control" wrap="soft">参加する
参加するが遅刻する
参加しない</textarea>
</div>

<h3>署名</h3>
<div class="form-group">
<textarea name="sign" rows="3" cols="60" class="form-control" wrap="hard">以上の件よろしくお願いします。
--
静岡大学情報学部
</textarea>
</div>
<hr class="my-4">
<h3>回答〆切(YYYY/MM/DD)</h3>
<div class="form-group row">
	<input type="text" class="form-control col-sm-4" name="deadline" size="50"  placeholder="「2018/05/12」形式で入力してください。">
	<label class="col-sm-3 col-form-label"><p class="text-right">本当の〆切:＋</p></label>
	<input type="text"  class="form-control col-sm-3" name="deadline_" size="5" value="9">
	<label class="col-sm-2 col-form-label">時間後</label>
</div>
<hr class="my-4">
<h3>集計項目</h3>
<div class="form-check">
    <input type="checkbox" class="form-check-input" name="public" id="labelChecked" value="checked" checked>
    <label class="form-check-label" for="labelChecked">集計結果を回答者にも公開する。</label>
</div>
<div class="form-group">
<textarea name="total" rows="4" cols="60"  class="form-control" wrap="hard">-|参加・不参加一覧|
*|参加者数|0,1
*|不参加数|2
-|参加内訳|
+|フル参加|0
+|遅刻|1</textarea>
</div>
<p class="text-right">
<button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#modalFormat">
集計項目の記述フォーマットについて
</button>
</p>
<div class="modal fade" id="modalFormat" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">集計項目記述フォーマット</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
<h4>概要</h4>
<p>集計結果ページへ表示する項目を定義します。各行、見出しの表示、名簿の表示、人数の表示を定義でき、それらは集計結果ページに順に表示されます。</p>
<h4>フォーマット</h4>
<ul class="list-group list-group-flush">
  <li class="list-group-item">
  <h5>大見出し</h5>
  <p><b>-|</b>で始めると大見出しを定義する事ができます。結果を見やすくする為に使います。</p>
  <p>例<span class="badge badge-secondary">-|参加・不参加一覧|</span></p>
  </li>
  <li class="list-group-item">
  <h5>参加者リスト(名前と人数)</h3>
  <p><b>*|</b>で始めると名簿を表示します。項目名と集計項目(カンマ区切りで複数設定可)を定義します。
  下記の例では、アンケート選択肢0番目と1番目の項目（つまり1行目と2行目）を選択した人の人数と名簿を「参加者」という項目名で表示します。</p>
  <p>例<span class="badge badge-secondary">*|参加者数|0,1</span></p>
  </li>
  <li class="list-group-item">
  <h5>参加者リスト(人数のみ)</h3>
  <p><b>-|</b>で始めると名簿無しで集計人数のみ表示します。項目名と集計項目の指定方法は上記と同じです。</p>
  <p>例<span class="badge badge-secondary">+|フル参加|0</span></p>
  </li>
</ul>
<h4>例示</h4>
<p>バス送迎有りのケースで参加不参加のアンケートをとる例で設定を示します。往復共にバス送迎があるとして、選択肢は次のようになります。
<div class="bg-info"><pre class="text-white"><code>参加する(往路のみバス利用)
参加する(復路のみバス利用)
参加する(往復共にバス利用)
参加する(バスを利用しない)
参加しない</code></pre></div>
<p>その場合の集計は項目は次の様に設定すると良いでしょう。</p>
<div><pre class="bg-info text-white"><code>-|参加・不参加一覧|
*|参加者数|0,1,2,3
*|不参加数|4
-|バス搭乗人数|
+|往路|0,2
+|復路|1,2
</code></pre></div>
</p>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<hr class="my-4">
<h3>送信先リスト</h3>
<div class="form-group">
<label for="formFile">名簿CSVファイル:</label>
<input type="file" name="mailtolist" class="form-control-file" id="formFile" aria-describedby="helpFile">
<small id="helpFile" class="form-text text-muted">
※各行「名前,メールアドレス」の順<br/>
※エクセルで作成し、ファイルの種類を「<b>CSV(カンマ区切り)</b>」で保存したファイルも読み込めます。<br/>
</small>
</div>
<hr class="my-4">
<div class="form-group">
<button class="btn btn-primary" name="mode" value="create" type="submit" aria-describedby="helpSubmit">アンケート作成</button>
<small id="helpSubmit" class="form-text text-muted">
※確認画面が表示されます。(メールはまだ送信されません)
</small>
</div>

<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
</form>
</div>
<?php
} else {
                ?>
<div class="container">
<div class="jumbotron text-white" style="background:<?=$COLOR[$CONFIG['color']]['dark']?>;">
  <h1 class="display-4"><?=$CONFIG['title']?></h1>
  <p class="lead">宴会の出欠調査をメールで簡単に行うためのシステムです。</p>
</div>
<h1>管理メニュー</h1>
<h2>アンケート送付先追加</h2>
<form action="<?=$URL?>" method="POST">
<div class="form-group row">
    <label for="mail" class="col-sm-2 col-form-label">メールアドレス</label>
    <div class="col-sm-10">
      <input type="email" class="form-control" name="mail" id="mail" placeholder="Email">
    </div>
</div>
<div class="form-group row">
    <label for="name" class="col-sm-2 col-form-label">氏名</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="name" id="name" placeholder="Name">
    </div>
</div>
<div class="form-group row">
    <div class="col-sm-2">確認</div>
    <div class="col-sm-10">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="now" name="now" value="checked" checked>
        <label class="form-check-label" for="now">
		今すぐメールを送信する
        </label>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-2">&nbsp;</div>
    <div class="col-sm-10">
	<input type="submit" class="btn btn-primary" name="mode" value="add">
    </div>
  </div>
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
</form>
<hr>
<h2>アンケート再送(未報告者のみ)</h2>
<form action="<?=$URL?>" method="POST">
<div class="form-group row">
    <div class="col-sm-2">&nbsp;</div>
    <div class="col-sm-10">
	<input type="submit" class="btn btn-primary" name="mode" value="send">
    </div>
  </div>
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
<input type="hidden" name="resend" value="true">
</form>
<hr>
<h2>アンケート初期化</h2>
<form action="<?=$URL?>" method="POST">
<div class="form-group row">
    <label for="admin" class="col-sm-2 col-form-label">管理者パスワード</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="admin" id="admin" placeholder="Password">
    </div>
</div>
<div class="form-group row">
    <div class="col-sm-2">&nbsp;</div>
    <div class="col-sm-10">
	<input type="submit" class="btn btn-danger" name="mode" value="delete"><small>※アンケート結果も全て消去されます。</small>
    </div>
  </div>

</form>
<h1>集計結果</h1>
<?php
$results = printResults();
?>
<h1>全結果</h1>
<?php
if (count($results) == 0) {
                    echo "まだ登録がありません<br/>";
                } else {
                    echo '※エクセルにコピペできます。<br/>';
                    echo '<textarea onclick="this.focus();this.select()" rows="' . count($results) . '" cols="100" wrap="off">';
                    foreach ($results as $result) {
                        echo "" . $result["name"] . "\t" . $result["note"] . "\r";
                    }
                    echo "</textarea>";
                }
                ?><hr/>
<?php
}
        } else {
            if ($_REQUEST["mode"] == "create") {
                //vote作成
                ?>
<div class="container">
<div class="jumbotron text-white" style="background:<?=$COLOR[$CONFIG['color']]['dark']?>;">
  <h1 class="display-4"><?=$CONFIG['title']?></h1>
  <p class="lead">宴会の出欠調査をメールで簡単に行うためのシステムです。</p>
</div>
<?php
//入力値チェック
                $ERROR = array();
                $NOTICE = array();
                foreach ($FILE as $file) {
                    if (file_exists($DIR . $file)) {
                        $ERROR[] = '<li class="list-group-item list-group-item-danger">既にアンケート用ファイルが存在します。新規のアンケートを作成する場合は、古いファイルを削除してください。</li>';
                        break;
                    }
                }
                if ($_FILES['mailtolist']['size'] <= 0 or $_FILES['mailtolist']['error'] != 0) {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">CVSファイルのアップロードに失敗しました。</li>';

                }
                if (!isset($_REQUEST["event"]) or $_REQUEST["event"] == "") {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">イベント名が記入されていません。</li>';
                }
                if (!isset($_REQUEST["mail_from"]) or $_REQUEST["mail_from"] == "") {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">差出人メールアドレスが記入されていません。</li>';
                }
                if (!isset($_REQUEST["mail_fromname"]) or $_REQUEST["mail_fromname"] == "") {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">差出人名が記入されていません。</li>';
                }
                if (!isset($_REQUEST["mail_"]) or $_REQUEST["mail_"] == "") {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">メール題名が記入されていません。</li>';
                }
                if (!isset($_REQUEST["mail"]) or $_REQUEST["mail"] == "") {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">メール本文が記入されていません。</li>';
                }
                if (!isset($_REQUEST["choice"]) or $_REQUEST["choice"] == "") {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">アンケート選択肢が記入されていません。</li>';
                }
                if (!isset($_REQUEST["sign"]) or $_REQUEST["sign"] == "") {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">メール署名が記入されていません。</li>';
                }
                if (isset($_REQUEST["deadline"]) and $_REQUEST["deadline"] != "") {
                    $time = strtotime($_REQUEST["deadline"]);
                    if ($time === false) {
                        $ERROR[] = '<li class="list-group-item list-group-item-danger">〆切日の日付フォーマットが異なっています。';
                    } else {
                        $softdead = $time + 24 * 60 * 60; //その日の終わり
                    }
                } else {
                    $softdead = time();
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">アンケート〆切日が記入されていません</li>';
                }
                if (isset($_REQUEST["deadline_"]) and $_REQUEST["deadline_"] != "") {
                    if (is_numeric($_REQUEST["deadline_"])) {
                        if (intval($_REQUEST["deadline_"]) < 0) {
                            $ERROR[] = '<li class="list-group-item list-group-item-danger">本当の〆切には正の整数値を入れてください．</li>';
                        } else {
                            $harddead = $softdead + (intval($_REQUEST["deadline_"]) * 60 * 60);
                        }
                    } else {
                        $ERROR[] = '<li class="list-group-item list-group-item-danger">本当の〆切には正の整数値を入れてください。</li>';
                    }
                } else {
                    $ERROR[] = '<li class="list-group-item list-group-item-danger">本当の〆切が記入されていません。</li>';
                }
                if (count($ERROR) != 0) {
                    ?>
</div>
<div class="alert alert-danger" role="alert">
<h4 class="alert-heading">[失敗]アンケートは作成されませんでした</h4>
</div>
<ul class="list-group">
<?php
echo implode("\n", $ERROR);
                    ?>
</ul>
<p>ブラウザの戻るボタンを使って、エラーを修正してください。</p>
<?php
echo "</div>";
                    exit(0);
                }
                //アンケート生成
                $MAIL = $_REQUEST["mail_from"];
                $KANJI = $_REQUEST["mail_fromname"];
                $SENDER = file_get_contents($DIR . ".sender");
                file_put_contents($DIR . $FILE["mail_from"], $SENDER . "*" . $MAIL . "*" . $KANJI);
                file_put_contents($DIR . $FILE["subject"], $_REQUEST["mail_"]);
                file_put_contents($DIR . $FILE["mail"], $_REQUEST["mail"]);
                file_put_contents($DIR . $FILE["sign"], $_REQUEST["sign"]);
                file_put_contents($DIR . $FILE["event"], $_REQUEST["event"]);
                $choice = preg_replace("/(\r\n|\n|\r)/", "\n", $_REQUEST["choice"]);
                file_put_contents($DIR . $FILE["choice"], $choice);
                if (!isset($_REQUEST["total"]) or trim($_REQUEST["total"]) == "") {
                    $cho = explode("\n", $choice);
                    $_REQUEST["total"] = "-|集計結果|\n";
                    $c = 0;
                    foreach ($cho as $ch) {
                        $_REQUEST["total"] .= "*|" . $ch . "|" . $c++ . "\n";
                    }
                }
                file_put_contents($DIR . $FILE["total"], preg_replace("/(\r\n|\n|\r)/", "\n", $_REQUEST["total"]));
                if (isset($_REQUEST["public"]) and $_REQUEST["public"] == "checked") {
                    file_put_contents($DIR . $FILE["pub_total"], preg_replace("/(\r\n|\n|\r)/", "\n", $_REQUEST["total"]));
                }
                file_put_contents($DIR . $FILE["result"], "");
                //list($y,$m,$d)=explode("/",$_REQUEST["deadline"]);
                //file_put_contents($DIR.$FILE["deadline"],mktime(23,59,59,$m,$d,$y).",".(mktime(23,59,59,$m,$d,$y)+($_REQUEST["deadline_"]*60*60)));
                file_put_contents($DIR . $FILE["deadline"], $softdead . "," . $harddead);
                //$members = file($SRC);

                $mailtolist = mb_convert_encoding(file_get_contents($_FILES['mailtolist']['tmp_name']), "UTF-8", "auto");

                $fp = tmpfile();
                fwrite($fp, $mailtolist);
                rewind($fp);
                $mems = array();
                $c = 0;
                ?>
<h2>ご確認ください</h2>
<table  class="table table-striped">
<thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">名前</th>
      <th scope="col">メールアドレス</th>
    </tr>
</thead>
<tbody>
<?php
while ($line = fgetcsv($fp)) {
                    array_push($mems, md5(uniqid()) . "," . $line[0] . "," . $line[1] . "\n");
                    echo '<tr><th scope="row">' . (++$c) . '</th><td>' . $line[0] . '</td><td>' . $line[1] . '</td></tr>';
                }
                ?>
</tbody>
</table>
<?php
fclose($fp);
                file_put_contents($DIR . $FILE["source"], implode("", $mems));
                ?>
<div class="alert alert-primary" role="alert">
<strong>※アンケート生成完了!</strong>
</div>
<?php
//例文作成
                $members = file($DIR . $FILE["source"]);
                list($id, $name, $mailto) = explode(",", $members[0]);
                $mailto = trim($mailto);
                $mail = getMailBody($name, file_get_contents($DIR . $FILE["mail"]), file($DIR . $FILE["choice"]), $URL, $id, file_get_contents($DIR . $FILE["sign"]));
                ?>
<hr class="my-4">
<form action="<?=$URL?>" method="POST">
<h3>アンケートを作り直す</h3>
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
<input type="submit" name="mode" value="delete" class="btn btn-outline-success btn-lg">
</form>
<hr class="my-4">
<form action="<?=$URL?>" method="POST">
<h3>アンケート送信</h3>
<input type="hidden" name="admin" value="<?=$_REQUEST["admin"]?>">
<input type="submit" name="mode" value="send" class="btn btn-outline-primary btn-lg">
</form>
<hr class="my-4">
<h3>メール文面プレビュー</h3>
<pre style="background:#37474F;color:#ECEFF1;padding:10px;"><?=$mail?></pre>
</div>a
<?php
} elseif ($_REQUEST["mode"] == "add") {
                list($SENDER, $MAIL, $KANJI) = explode("*", file_get_contents($DIR . $FILE["mail_from"]), 3);
                $MAILHEADER = mb_encode_mimeheader(mb_convert_encoding($KANJI, "ISO-2022-JP", "AUTO")) .
                    "<" . $MAIL . ">";
                $subject = file_get_contents($DIR . $FILE["subject"]);
                if (isset($_REQUEST["resend"])) {
                    $subject = "再送 - " . $subject;
                }
                $nakami = file_get_contents($DIR . $FILE["mail"]);
                $sign = file_get_contents($DIR . $FILE["sign"]);
                $choice = file($DIR . $FILE["choice"]);
                $id = md5(uniqid());
                $name = $_REQUEST["name"];
                $mailto = $_REQUEST["mail"];
                file_put_contents($DIR . $FILE["source"], $id . "," . $name . "," . $mailto . "\n", FILE_APPEND | LOCK_EX);
                $mail = getMailBody($name, $nakami, $choice, $URL, $id, $sign);
                if (isset($_REQUEST["now"]) and $_REQUEST["now"] == "checked") {
                    echo $name . "様(" . $mailto . ")へ送信--";
                    //$r = mb_send_mail($mailto,$subject,$mail,$MAILHEADER,"-f".$SENDER);
                    $r = sendMail($mailto, $subject, $mail, $MAILHEADER, $SENDER);
                    echo $r ? "[成功]<br/>" : "[失敗]<br/>";
                } else {
                    echo "<h2>以下のようなアンケートメールが再送時に送られます</h2>";
                    echo "<pre style=\"width:500px;padding:10px;background-color:#ddd;\">" . $mail . "</pre>";
                }
            } elseif ($_REQUEST["mode"] == "delete") {
                foreach ($FILE as $k => $file) {
                    if (file_exists($DIR . $file)) {
                        unlink($DIR . $file);
                    }
                }
                ?>
<div class="container">
<div class="jumbotron text-white" style="background:<?=$COLOR[$CONFIG['color']]['dark']?>;">
  <h1 class="display-4"><?=$CONFIG['title']?></h1>
  <p class="lead">宴会の出欠調査をメールで簡単に行うためのシステムです。</p>
</div>
<div class="alert alert-info" role="alert">
<strong>アンケート削除完了！</strong>　続けて新しいアンケートを作成する場合は<a class="alert-link" href="<?=$URL?>?admin=<?=$PASSWORD?>">こちら</a>から。
</div>
<?php
} elseif ($_REQUEST["mode"] == "send") {
                //vote送信
                ?>
<div class="container">
<div class="jumbotron text-white" style="background:<?=$COLOR[$CONFIG['color']]['dark']?>;">
  <h1 class="display-4"><?=$CONFIG['title']?></h1>
  <p class="lead">宴会の出欠調査をメールで簡単に行うためのシステムです。</p>
</div>
<h2>アンケート送信！</h2>
<?php
list($SENDER, $MAIL, $KANJI) = explode("*", file_get_contents($DIR . $FILE["mail_from"]), 3);
                $MAILHEADER = mb_encode_mimeheader(mb_convert_encoding($KANJI, "ISO-2022-JP", "AUTO")) . "<" . $MAIL . ">";
                $members = file($DIR . $FILE["source"]);
                $sent = array();
                if (isset($_REQUEST["resend"])) {
                    $results = file($DIR . $FILE["result"]);
                    foreach ($results as $result) {
                        list($id, $sonota) = explode(",", $result, 2);
                        $sent[$id] = true;
                    }
                }
                $subject = file_get_contents($DIR . $FILE["subject"]);
                if (isset($_REQUEST["resend"])) {
                    $subject = "再送 - " . $subject;
                }
                $nakami = file_get_contents($DIR . $FILE["mail"]);
                $sign = file_get_contents($DIR . $FILE["sign"]);
                $choice = file($DIR . $FILE["choice"]);
                foreach ($members as $member) {
                    list($id, $name, $mailto) = explode(",", $member);
                    if (isset($sent[$id])) {
                        continue;
                    }
                    $mailto = trim($mailto);
                    $mail = getMailBody($name, $nakami, $choice, $URL, $id, $sign);
                    echo $name . "様(" . $mailto . ")へ送信";
                    //$r = mb_send_mail($mailto,$subject,$mail,$MAILHEADER,"-f".$SENDER);
                    $r = sendMail($mailto, $subject, $mail, $MAILHEADER, $SENDER);
                    echo $r ? '<span class="badge badge-pill badge-success">成功</span><br>' : '<span class="badge badge-pill badge-danger">失敗</span><br>';
                }
                ?>
<hr class="my-4">
<h2>管理・集計ページ</h2>
<p>アンケートの管理・集計結果の閲覧は以下のページをご覧ください。(ブックマークをしておくと便利です。)<br>
<a href="<?=$URL?>?admin=<?=$_REQUEST["admin"]?>"><?=$URL?>?admin=<?=$_REQUEST["admin"]?></a>
</p>
</div>
<?php
}
        }
    } else {
        //無視
        echo "hello world!";
    }
} else {
    $m = file($DIR . $FILE["source"]);
    $members = array();
    foreach ($m as $n) {
        list($id, $name, $mailto) = explode(",", $n);
        $members[$id] = $name;
    }
    $choice = file($DIR . $FILE["choice"]);
    if (!isset($members[$_REQUEST["u"]])) {?>
<div class="container">
        <table style="height: 100vh;">
            <tbody>
                <tr>
                    <td class="align-middle">
                        <div class="alert alert alert-danger" role="alert">
                            <h4 class="alert-heading">エラー</h4>
                            <p>この投票URLは有効ではありません。既にアンケートは修了したか、もしくはURLの一部が欠損している可能性があります。メール内に記載されたURLをもう一度ご確認ください。</p>
                            <hr>
                            <p class="mb-0">ヒント：メールに記載されているURLが途中で改行されている場合等にこのエラーが出る場合があります。もしURLに予期せぬ改行が含まれている場合は、お手数ですがコピー&amp;ペーストで繋げて正しいURLにアクセスしてください。</p>
                            <hr>
                            <p class="mb-0 text-right small"><a href="https://github.com/abarth500/EnkaiSan" target="_blank">宴会さん by Shohei Yokoyama</a></p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
<?php	exit;
    }
    if (!isset($_REQUEST["v"])) {
        //結果一覧モード
        $NAME = $members[$_REQUEST["u"]];
        $NOTE = getCurrentResult($NAME);
        ?>
	<div class="modal fade" id="answerModal" tabindex="-1" role="dialog" aria-labelledby="answerModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="answerModalLabel">
                            <?=$NAME?>様の現在の登録情報</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
                </div>
                <div class="modal-body">
                    <h3 class="text-center"><span class="badge badge-primary"><?=$NOTE?></span></h3>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php
if (file_exists($DIR . $FILE["pub_total"])) {
            /*echo '<h1 style="border-top: inset 10px <?=$COL[$CONFIG[\'color\']]?>;">集計結果</h1>';*/
            ?>
			<div class="container">
			<div class="jumbotron text-white" style="background:<?=$COLOR[$CONFIG['color']]['dark']?>;">
  					<h1 class="display-4"><?=file_get_contents($DIR . $FILE["event"])?></h1>
  					<p class="lead">上記のイベントに関するアンケートの集計結果です。</p>
				</div>
			<?php
            printResults();
            echo '</div>';
        }
    } else {
        //投票モード
        list($SENDER, $MAIL, $KANJI) = explode("*", file_get_contents($DIR . $FILE["mail_from"]), 3);
        $NAME = $members[$_REQUEST["u"]];
        list($d, $deadline) = explode(",", file_get_contents($DIR . $FILE["deadline"]));
        if (time() > intval($deadline)) {
            //期限切れ
            ?>
	<div class="container">
        <table style="height: 100vh;">
            <tbody>
                <tr>
                    <td class="align-middle">
                        <div class="alert alert alert-danger" role="alert">
                            <h4 class="alert-heading">期限切れ</h4>
                            <p><strong><?=$NAME?></strong>様。本アンケートの回答は締め切られました。 [期限:<?php echo date("Y年m月d日", $d); ?>]</p>
                            <hr>
                            <p class="mb-0">回答の変更等は直接メールにて[<a href="mailto:<?echo $MAIL; ?>"><?echo $KANJI; ?></a>]へご連絡ください。</p>
                            <hr>
                            <p class="mb-0 text-right small"><a href="https://github.com/abarth500/EnkaiSan" target="_blank">宴会さん by Shohei Yokoyama</a></p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

<?php
}
        $NAME = $members[$_REQUEST["u"]];
        $ID = $_REQUEST["u"];
        $VALUE = $_REQUEST["v"];
        $CHOICE = trim($choice[$_REQUEST["v"]]);
        ?>
	<div class="modal fade" id="answerModal" tabindex="-1" role="dialog" aria-labelledby="answerModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="answerModalLabel">
                            <?=$NAME?>様</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
                </div>
                <div class="modal-body">
					<p>アンケートへの回答誠にありがとうございます。以下の回答を承りました。</p>
                    <h3 class="text-center"><span class="badge badge-primary"><?=$CHOICE?></span></h3>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php

file_put_contents($DIR . $FILE["result"], $ID . "," . microtime(true) . "," . $NAME . "," . $VALUE . "," . $CHOICE . "\n", FILE_APPEND | LOCK_EX);
if (file_exists($DIR . $FILE["pub_total"])) {
            /*echo '<h1 style="border-top: inset 10px <?=$COL[$CONFIG[\'color\']]?>;">集計結果</h1>';*/
            ?>
			<div class="container">
			<div class="jumbotron text-white" style="background:<?=$COLOR[$CONFIG['color']]['dark']?>;">
  					<h1 class="display-4"><?=file_get_contents($DIR . $FILE["event"])?></h1>
  					<p class="lead">上記のイベントに関するアンケートの集計結果です。</p>
				</div>
			<?php
echo '<div class="container">';
            printResults();
            echo '</div>';
        }
    }
}
?>
<div class="fixed-bottom text-right">
        <a class="btn btn-outline-secondary btn-sm" href="https://github.com/abarth500/EnkaiSan" target="_blank">宴会さん by Shohei Yokoyama</a>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
<script>
	$(document).ready(function() {
		if ($('#answerModal')[0]) {
			$('#answerModal').modal('show');
		}
	});
</script>
</body>
</html>