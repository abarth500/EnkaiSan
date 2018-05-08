# 概要

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
$ git --depth 1 https://github.com/abarth500/EnkaiSan.git
$ cp -r EnkaiSan/* .
```
これで、インストールとApacheの設定が出来ました。指定したURLにアクセスして```HELLO WORLD```と表示されれば成功です。また```/vote/.htaccess```にアクセスしてファイルが表示されない事を確認してください。この```vote```フォルダがWebに公開されていると、ニュースとかを良く騒がせる、いわゆる「個人情報がWebでだれでも見られる状態になったいた」という事になりますので、必ず確認してください。尚、上記のコマンドが正しく実行されて居れば、```vote```フォルダへのアクセスは拒否されます。

さて、初期設定をしましょう。index.phpをコマンドラインで実行します。
```bash
$ php index.php
```
