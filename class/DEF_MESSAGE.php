<?php
//////////////////////////////////////////////////////////////////
// システムメッセージ定義
//////////////////////////////////////////////////////////////////
define("MESSAGE_UNKNONW_MODE",          "\$modeの値が不明です");
define("MESSAGE_WRONG_MODE",            "不明な\$modeです。");
define("MESSAGE_ZERO_WRITE",            "まだ書き込みがありません");
define("MESSAGE_COOKIE_ERROR",          "クッキーを有効にしないと書き込めません。");
define("MESSAGE_EMPTY_THREAD_TITLE",    "スレッドタイトルが空白です。");
define("MESSAGE_EMPTY_THREAD_BODY",     "スレッド本文が空白です。");
define("MESSAGE_EMPTY_RES_BODY",        "書き込み本文が空白です。");
define("MESSAGE_FAILURE_LOG_GET",       "該当ログデータ取得に失敗しました。");
define("MESSAGE_EMPTY_THREAD_NUMBER",   "スレッド番号がありません。");
define("MESSAGE_WRONG_THREAD_NUMBER",   "スレッド番号が不正です。");
define("MESSAGE_EMPTY_ADMIN_PASS",      "管理パスワードが空白です。");
define("MESSAGE_WRONG_ADMIN_PASS",      "管理パスワードが間違っています。");
define("MESSAGE_WRONG_VALUE",           "不正な値です。");
define("MESSAGE_ADMIN_NO_CHECK",         "削除にチェックが１つもついていません。");
define("MESSAGE_ADMIN_RELOAD_DONE",      "更新しました。");
define("MESSAGE_ADMIN_LOGINED",          "管理画面です。");
define("MESSAGE_NOT_MAKE",               "を作成できません。");
define("MESSAGE_CHANGE_PERMISSION_0777", "の書き込み権限を'0777'に変更してください。");


// 単語定義
define("WORD_LOGOUT",                   "ログアウト");
define("WORD_DEFAULT_TITLE",            "Rami-BBS");
define("WORD_DEFAULT_DESC",             "オープンソースの簡易掲示板システムRami-BBSです。");
define("WORD_DEFAULT_KEYWORD",          "BBS,Rami-BBS,掲示板,オープンソース");
define("WORD_DEFAULT_BASE_URL",         "./");
define("WORD_DEFAULT_AUTHOR",           "rutti.net");
define("WORD_DEFAULT_SKIN",             "default");
define("WORD_DEFAULT_NO_NAME",          "NO NAME");
define("WORD_DEFAULT_SHORT_RS",         5);
define("WORD_DEFAULT_THREAD_NUM_PP",    5);
define("WORD_DEFAULT_ADMIN_PASS",       "ramibbs");

define("WORD_ADMIN_TABLE_NO",           "No");
define("WORD_ADMIN_TABLE_COMMENT",      "コメント");
define("WORD_ADMIN_TABLE_NAME",         "名前");
define("WORD_ADMIN_TABLE_IP",           "IP");
define("WORD_ADMIN_TABLE_DATE",         "書き込み日時");
define("WORD_ADMIN_TABLE_DELETE",       "削除");
define("WORD_ADMIN_SEND",               "送信する");
define("WORD_ADMIN_RESET",              "リセット");
define("WORD_ADMIN_TITLE",              "管理画面");

?>