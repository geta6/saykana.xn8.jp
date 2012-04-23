たい.jp / ない.jp
=================

* サブドメインに打ち込んだ文字列を発音します
* サブドメインで回すと余計なエスケープ処理書かなくていいから楽です

Ex
--

* トイレ行き.たい.jp
* トイレ行けばいいじゃ.ない.jp

Requires
--------

* Mac OS
* SayKana
* MeCab
* Net/IDNA

System
------

* Silex
* nginx + php5-fpm (ubuntu, gateway + app + cache)
* apache (osx, backend)


ほげほげ
--------

* システムは1時間くらいで適当に書いた
  * MeCabしてSayKanaするだけ

* データベースとか適当


データベェス
------------

集計用なので無くても動きます

    CREATE TABLE IF NOT EXISTS `たい` (
      `fqdn` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `query` text COLLATE utf8_unicode_ci NOT NULL,
      `times` int(11) NOT NULL DEFAULT '1',
      `date` datetime NOT NULL,
      `last` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY `fqdn` (`fqdn`),
      KEY `last` (`last`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

