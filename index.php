<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
// ***************************************************************
// 簡易掲示板 Rami-BBS
// ***************************************************************
// New BSD ライセンス
// 文書型定義はHTML 4.0
// $_REQUESTを使うのでPHPのバージョンは4.1以上で。
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once("common.php");      // 各クラス・定義の読み込み
$obj_rami = new RAMI_BBS();      // インスタンス生成
$obj_rami->start();              // 処理開始

?>