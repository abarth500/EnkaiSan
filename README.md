# 概要

宴会の出欠調査アンケートの為に開発されたシステムです。CSVで参加対象者のメールアドレスリストを登録する事で、簡単にメールによる出欠の調査ができます。参加者はメールに書かれたURLをクリックするだけで、参加／不参加を報告できます。Webブラウザ上での面倒な操作は必要ありません。

# 導入

インストール方法を説明します。宴会さんはWebサーバで公開されているディレクトリにインストールする必要があります。例えば```/var/www/htdocs/```等です。ここでは、新たに```/var/www/enkai/```というディレクトリを作成し、それをVatual HostとしてIPアドレス```192.0.2.123```にて```https://enkai.yokoyama.ac/```というURLで公開する事を例に万度例を示します。実際に設定する際には適宜読み替えてください。

## 環境構築
サーバには事前にHTTPd、SMTP、PHPと、それらに関連したライブラリがインストールされている必要があります。以下、Fedora/CentOSのコマンド例です。
```
sudo yum install httpd php php-mbstring php-pear postfix
sudo systemctl start httpd
sudo systemctl start postfix
sudo systemctl enable httpd
sudo systemctl enable postfix
sudo pear install -a Mail
sudo pear install -a Net_SMTP
```

## DNSの設定

まず```enkai.yokoyama.ac```でアクセスできるようにDNSのAレコードに、サーバのIPアドレスを設定してください。固定IPを持っていない場合は、ダイナミックDNSを利用してください。ただし、そのような環境の場合「Outbound Port 25 Blocking」でメール送信が制限されている可能性があるので注意が必要です。

また、サーバがIPv6の固定IPを持っている場合、AAAAレコードにそれを設定しましょう。

次に送信するメールがスパムフィルタに引っかかりにくくするように、SPFレコードを追加します。SPFとは送信ドメイン認証の事で、メールが正しい場所から送信されているのかを検査するためのプロトコルで、DNSを使って設定します。SPFレコードの書き方の詳細はここでは解説しませんが、インストールするサーバのFQDNが```enkai.yokoyama.ac```で、AレコードにてIPアドレスが指定されている場合は次のようになります。

```
"v=spf1 a:enkai.yokoyama.ac -all"
```

サーバからメールが送信される時、メールの受信側はこのレコードを読んで、```a:enkai.yokoyama.ac```を得ます、次にその```enkai.yokoyama.ac```のAレコードを引いて、IPアドレス```192.0.2.123```を得ます。メールの受信側は、メール送信の接続元、すなわちメールの送信側サーバのIPが```192.0.2.123```であったら、SPF検査をパスさせ、正常に受信します。なおパスしなかったメールがどうなるかは受信側サーバ次第で、迷惑メールフォルダに振り分けられる事が多いと思いますが、削除してしまう事もあるかもしれません。

またSender IDという送信ドメイン認証もあり、それもパスしないとスパム判定するサーバもあるようです。こちらはFromに書かれたメールアドレスに対しての認証で、送信サーバと実際の送信者のメールサーバが異なる時(宴会さんを使うケースでは殆どそうなるでしょう)に問題となります。ただし、手元の環境で確認したところGmail、Office365共に、Sender ID検査に引っかかるものの、SPFをパスしていれば、スパム判定されないようですので、ここでは解決策は説明しません。

## 宴会さんのインストール

さて、宴会さんをGitHubからダウンロードしてインストールする方法を説明します。まずはインストール先ディレクトリを作り、そのディレクトリをVirtual Hostとして公開するようにApacheの設定に追記します。

```bash
$ mkdir /var/www/enkai/
$ cd  /var/www/enkai/
$ cat <<EOF | sudo tee /etc/httpd/conf.d/enkaisan.conf
<VirtualHost 192.0.2.123:80>
  Options None
  DirectoryIndex index.php
  DocumentRoot /var/www/enkai
  ServerName enkai.yokoyama.ac
  <Directory "/var/www/enkai">
    AllowOverride All
  </Directory>
</VirtualHost>
$ systemctl restart httpd
```

次にGitHubから宴会さんのプログラムをダウンロードして展開します。

```bash
$ git clone --depth 1 https://github.com/abarth500/EnkaiSan.git
$ cp -r EnkaiSan/* .
```
これで、インストールとApacheの設定が出来ました。指定したURLにアクセスして```HELLO WORLD```と表示されれば成功です。また```/vote/.htaccess```にアクセスしてファイルが表示されない事を確認してください。この```vote```フォルダがWebに公開されていると、ニュースとかを良く騒がせる、いわゆる「個人情報がWebでだれでも見られる状態になったいた」という事になりますので、必ず確認してください。尚、上記のコマンドが正しく実行されて居れば、```vote```フォルダへのアクセスは拒否されます。

さて、初期設定をしましょう。index.phpをコマンドラインで実行します。
```bash
$ sudo php index.php
```

色々と初期設定に必要な質問が出てきますので答えてください。

```bash
Admin Password:                 (default=uniqid())
>
```

宴会さん管理者のパスワードです。このパスワードを知っている人だけが、アンケートの作成・送付・集計を行えます。何も入力せずにEnterを押すとランダムな文字列が生成されてパスワードとなります。

```bash
Envelope From Address:
>
```

Envelope Fromとして設定するアドレスを入力してください。スパム判定される可能性を少なくするには、このPHPプログラムがあるサーバが持っているメールアドレスを設定してください。今回の例示に従うと```kanji@enkai.yokoyama.ac```等のアドレスです。尚、Aliaseを設定した転送アドレスでも構いません。

```bash
Username of Web Server:         (default=apache)
>
```

Webサーバの実行ユーザ名を入力してください。なにも入力しない場合は```apache```となります。これは、ログディレクトリや設定ファイルのパーミッション設定に使用されます。

```bash
Organisation/Comittee Name           (default=Comitteee of Banquet)
> 
```

組織名とありますが、例えば「●●親睦会」の「お花見飲み会」の出欠調査に本システムを使うような場合は「●●親睦会」を入力してください。（「お花見飲み会」の方は、アンケート作成時に入力します。）

```bash
Key color of your service               (default=Indigo)
        Red           Pink          Purple        DeepPurple
        Indigo        Blue          LightBlue     Cyan
        Teal          Green         LightGreen    Lime
        Yellow        Amber         Orange        DeepOrange
        Brown         Grey          BlueGrey
> 
```
最後に、ページのアクセントカラーを選びます。複数の宴会さんを利用する際は別の色を割り当てる事によって、混同を避ける事かできます。

設定項目はこれで完了です。インストールが成功すれば以下のようなメッセージが最後に表示されます。

```bash
[Done!]
        See index.php?admin=yourPassword
```

## 新規アンケート作成

Webブラウザで管理者ページを開きます。上記の例では```https://enkai.yokoyama.ac/index.php?admin=yourPassword```が管理者ページのURLになります。

# オプション：https対応

ここまでの設定で十分に動作しますが、https対応もしておきましょう。 [Let's Encrypt](https://letsencrypt.org/)で無料のCertを手に入れる方法を説明します。やり方は簡単です。まずCertbotをインストールします。

```bash
sudo yum install certbot-apache
```

次にCertbotを起動します。
```bash
sudo certbot --apache
```

いくつか質問が出てきますので答えましょう。メールアドレスは貴方のメールアドレスを入れれば良いでしょう。Apacheで定義されたHost NameおよびVirtual Host Nameの一覧が出てきますので、宴会さんで使うHost Nameを選びましょう。カンマで繋げて複数を選ぶ事も出来ますし、ワイルドカードを使う事も出来ます。成功すると、Apacheに取得したCertが設定されます。Apacheの設定ファイルも自動で追加されています。```/etc/httpd/conf.d/enkaisan-le-ssl.conf```というファイルが追加されているでしょう。手順が簡単すぎて不安な場合は、以下のコマンドで設定内容を確認できます。

```bash
less /etc/httpd/conf.d/enkaisan-le-ssl.conf
```

尚、Fedora/CentOSおよびApacheを使う事を前提で説明していますが、他の環境でもSSL対応は簡単です。以下のページを参考にしてください。

https://certbot.eff.org/

# ライセンス

The MIT License (MIT)

Copyright (c) 2016-2018 Shohei Yokoyama

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.