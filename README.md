# 概要

宴会の出欠調査アンケートの為に開発されたシステムです。CSVで参加対象者のメールアドレスリストを登録する事で、簡単にメールによる出欠の調査ができます。参加者はメールに書かれたURLをクリックするだけで、参加／不参加を報告できます。Webブラウザ上での面倒な操作は必要ありません。

# 導入
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

## 宴会さんのインストール
宴会さんはWebサーバで公開されているディレクトリにインストールする必要があります。例えば```/var/www/htdocs/```等です。ここでは、新たに```/var/www/enkai/```というディレクトリを作成し、それをVatual HostとしてIPアドレス```192.0.2.123```にて```https://enkai.yokoyama.ac/```というURLで公開する事を例に万度例を示します。実際に設定する際には適宜読み替えてください。

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
$ php index.php
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
Envelope Fromとして設定するアドレスを入力してください。スパム判定される可能性を少なくするには、このPHPプログラムがあるサーバが持っているメールアドレスを設定してください。また、DNSでSPFレコードに登録する事も検討してください。尚、ここの設定とは別に、アンケート作成時にFrom(送信者)アドレスが指定できます。

```bash
Username of Web Server:         (default=apache)
>
```

Webサーバの実行ユーザ名を入力してください。なにも入力しない場合は```apache```となります。これは、ログディレクトリや設定ファイルのパーミッション設定に使用されます。

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
