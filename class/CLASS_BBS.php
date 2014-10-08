<?php
//////////////////////////////////////////////////////////////////
// BBS クラス
// 掲示板としての全体的な処理を扱う
//////////////////////////////////////////////////////////////////
class BBS {

    //************************************************************
    // メンバ変数
    protected $mode;      // BBS内の処理内容
    protected $pagetitle; // ページごとのタイトル
    protected $head;      // ヘッダのソース
    protected $foot;      // フッタのソース
    protected $body;      // メインコンテンツのソース
    protected $cookie;    // 書き込み処理で用いるクッキーの値
    protected $error;     // エラー時にコメントを格納する
    protected $page;      // 表示ページ数
    protected $thread;    // 表示スレッド番号を格納

    //************************************************************
    // 以下、メソッド

    //////////////////////////////////////////////////////////////
    // コンストラクタ
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    public function BBS() {
        $this->checkInitFile();
    }

    //////////////////////////////////////////////////////////////
    // 処理の開始
    // これがこのBBSのmain関数みたいなもの。
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    public function start() {
        $this->pagetitle = "";
        $this->mode = $this->getMode();
        $this->preProcess();
        $this->head = $this->setHeader();
        $this->body = $this->setBody();
        $this->foot = $this->setFooter();
        ob_start( array("BBS","convertSettingTextAndArea") );
        $this->view();
        ob_end_flush();
    }

    //////////////////////////////////////////////////////////////
    // 設定ファイルの確認。設定ファイルが無ければ作成する。
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    protected function checkInitFile() {
        if ( !file_exists( INIT_SERVER ) )      { $this->makeServerInit(); }
        if ( !file_exists( INIT_BBS_SETTING ) ) { $this->makeBBSInit(); }

        // 定義読み込み
        require_once(INIT_SERVER);
        require_once(INIT_BBS_SETTING);
    }

    //////////////////////////////////////////////////////////////
    // server.phpの作成。
    // 主に設置対象のサーバに関する情報を格納する
    // これ動くのは設置時のみ
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    protected function makeServerInit() {

		$base_path = reset( pathinfo( dirname(__FILE__) ) );

		// ログ保存用フォルダのパーミッションを調べる
		$perm_log = substr(sprintf('%o', fileperms(DIR_LOG)), -4);
		if ( $perm_log != '0777' ) {
            // ログフォルダに書き込み権限が無ければ終了
            $path_target = $base_path."/".DIR_LOG;
            $comment_error = "「".$path_target."」".MESSAGE_CHANGE_PERMISSION_0777;
            $this->errorEnd($comment_error);
		}

        $rami_version = THIS_VER;           // この掲示板のバージョン(一応server.iniにも残しておく)
        $php_version = phpversion();        // PHPのバージョン取得
        $serverpath = $base_path;           // このフォルダがあるサーバパスを取得
        $host = $_SERVER["SERVER_NAME"];    // ホスト名取得
        $prefix = "";                       // プレフィックスをランダム生成
        for( $i=0; $i<NUM_PREFIX; $i++ ) $prefix .= chr(rand(0,25)+65);
        $prefix .= "_";

        // 内容をまとめる
        $server_init = '<'.'?php'."\n";
        $server_init.= 'define("RAMI_VER","'.$rami_version.'");'."\n";
        $server_init.= 'define("PHP_VER","'.$php_version.'");'."\n";
        $server_init.= 'define("SERVER_PATH","'.$serverpath.'");'. "\n";
        $server_init.= 'define("HOST","'.$host.'");'."\n";
        $server_init.= 'define("PREFIX","'.$prefix.'");';
        $server_init.= "\n?".'>';

        // ファイルに書き込む
        $fp = @fopen(INIT_SERVER,"w");
        if (!$fp) {
            // ファイル生成に失敗したら終了
            $comment_error  = INIT_SERVER.MESSAGE_NOT_MAKE."<br />";
            $comment_error .= "「".$base_path."」".MESSAGE_CHANGE_PERMISSION_0777;
            $this->errorEnd($comment_error);
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $server_init);
        flock($fp, LOCK_UN);
        chmod(INIT_SERVER, 0644);
    }

    //////////////////////////////////////////////////////////////
    // setting.phpの作成。
    // 設置するBBSに関する情報を格納する
    // 基本的にはファイルアップ時に存在しているハズだが、
    // もしも存在しなかったらデフォルトのファイルを作成する
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    protected function makeBBSInit() {
        $bbs_title    = WORD_DEFAULT_TITLE;             // BBS名
        $meta_desc    = WORD_DEFAULT_DESC;              // メタディスクリプション
        $meta_keyword = WORD_DEFAULT_KEYWORD;           // メタキーワード
        $base_url     = WORD_DEFAULT_BASE_URL;          // ベースとなるURL
        $author       = WORD_DEFAULT_AUTHOR;            // 作者名
        $skin         = WORD_DEFAULT_SKIN;              // デフォルトスキン
        $no_name      = WORD_DEFAULT_NO_NAME;           // 名無し
        $short_rs     = WORD_DEFAULT_SHORT_RS;          // 短縮表示時のレス数
        $thread_num_pp= WORD_DEFAULT_THREAD_NUM_PP;     // 1ページ中のスレッド数
        $admin_password = WORD_DEFAULT_ADMIN_PASS;      // 管理画面のパスワード

        // まとめる
        $bbs_init = '<'.'?php'."\n";
        $bbs_init.= '// BBSのタイトル名'."\n".'define("BBS_TITLE","'.$bbs_title.'");'."\n\n";
        $bbs_init.= '// BBSの一言説明文'."\n".'define("META_DESC","'.$meta_desc.'");'."\n\n";
        $bbs_init.= '// BBSの特徴となるキーワードをカンマ区切りで入力'."\n".'define("META_KEYWORD","'.$meta_keyword.'");'."\n\n";
        $bbs_init.= '// // ベースとなるURL(最後はスラッシュで閉じる)'."\n".'define("BASE_URL","'.$base_url.'");'."\n\n";
        $bbs_init.= '// 管理人の名前'."\n".'define("AUTHOR","'.$author.'");'."\n\n";
        $bbs_init.= '// スキン選択'."\n".'define("SKIN_TYPE","'.$skin.'");'."\n\n";
        $bbs_init.= '// 名前未記入時の名前'."\n".'define("NO_NAME","'.$no_name.'");'."\n\n";
        $bbs_init.= '// スレッド短縮表示の表示レス数'."\n".'define("SHORT_RES_NUM",'.$short_rs.');'."\n\n";
        $bbs_init.= '// 1ページに表示させるスレッドの数'."\n".'define("THREAD_NUM_PER_PAGE",'.$thread_num_pp.');'."\n\n";
        $bbs_init.= '// 管理画面へのパスワード'."\n".'define("ADMIN_PASSWORD","'.$admin_password.'");'."\n\n";
        $bbs_init.= '?>';

        // ファイルに書き込む
        $fp = @fopen(INIT_BBS_SETTING,"w");
        if (!$fp) {
            // ファイル生成に失敗したら終了
            $path_target = reset( pathinfo( dirname(__FILE__) ) );
            $comment_error  = INIT_BBS_SETTING.MESSAGE_NOT_MAKE."<br />";
            $comment_error .= "「".$path_target."」".MESSAGE_CHANGE_PERMISSION_0777;
            $this->errorEnd($comment_error);
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $bbs_init);
        flock($fp, LOCK_UN);
        chmod(INIT_BBS_SETTING, 0644);
    }

    //////////////////////////////////////////////////////////////
    // ヘッダ作成
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : $tmp : ヘッダの中身のソース
    //////////////////////////////////////////////////////////////
    protected function setHeader() {
        $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_HEADER);
        return $tmp;
    }

    //////////////////////////////////////////////////////////////
    // フッタ作成
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : $tmp : フッタの中身のソース
    //////////////////////////////////////////////////////////////
    protected function setFooter() {
        $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_FOOTER);
        return $tmp;
    }

    //////////////////////////////////////////////////////////////
    // ボディ作成
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : $tmp : 本文のソース
    //////////////////////////////////////////////////////////////
    protected function setBody() {

        // 初期化
        $main_body = "";

        // モードによって処理を分ける
        switch( $this->mode ) {
            // 通常の表示 ////////////////////////////////////////
            case "view":
                // 書き込みフォーム
                $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_WRITE_BOX);

            // スレッドの表示 ////////////////////////////////////
            case "view_thread":

                // 順番ログを取得
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);

                if ( !$obj_turn->getExist() || $obj_turn->getSize() == 0 ) {
                    // 順番ログが無い場合
                    $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_NO_WRITE);
                    $error_tmp = MESSAGE_ZERO_WRITE;
                    $main_body = str_replace("<!###NO_WRITE###!>",$error_tmp,$main_body);
                    $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                    $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                }
                else {

                    // viewモードで必要な処理
                    if ( $this->mode == "view" ) {

                        // スレッド一覧を取得する
                        $loop_cnt = $obj_turn->getSize();
                        if ( $loop_cnt > NUM_THREAD_LIST ) {
                            $loop_cnt = NUM_THREAD_LIST;
                        }
                        $thread_list = "";
                        for($i=0;$i<$loop_cnt;$i++) {
                            // スレッドの数だけループしてリンクを作成する
                            $board_no = $obj_turn->getValue($i);
                            $log_name = DIR_LOG."/".PREFIX.trim($obj_turn->getValue($i)).".".EXT_LOG;
                            $obj_log = new LOG($log_name);
                            $inner_url = BASE_URL . "?mode=view_thread&thread=" . $board_no;
                            $thread_list .= "<a href=\"".$inner_url."\" class=\"thread_list_link\">".$obj_log->getTitle()."(".$obj_log->getSize().")</a>";
                        }

                        // スレッド一覧を追加
                        $tmp_thread_list = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_THREAD_LIST);
                        $tmp_thread_list = str_replace("<!###THREAD_LIST###!>", $thread_list, $tmp_thread_list);
                        $main_body .= $tmp_thread_list;

                        // ループに使用する値を設定する
                        //   ページは{1,2,3,4....}と1から数えるのに対して
                        //   その他のデータは{0,1,2,3....}と0から数えるのでここで調整
                        if ( $this->page == 1 ) {
                            $start_i = 0;
                            $loop_cnt = $obj_turn->getLoopLimit( 0,THREAD_NUM_PER_PAGE );
                        } else {
                            $start_i = ($this->page - 1) * THREAD_NUM_PER_PAGE;
                            $loop_cnt = $obj_turn->getLoopLimit( $start_i, THREAD_NUM_PER_PAGE );
                        }
                    }
                    else if ( $this->mode == "view_thread" ) {

                        // ループに使用する値を設定する
                        // view_threadでは表示するスレッドは1つだけなので、
                        // ループは1回で済むように設定
                        $start_i = $this->thread;
                        $loop_cnt = $start_i+1;
                    }


                    // 要約すると,
                    // $obj_turn->data[$start_i] ～ [$loop_cnt] までをループしている
                    for($i = $start_i; $i < $loop_cnt && $i < 100; $i++ ) {

                        // 書き込みログのパス
                        if ( $this->mode == "view") {
                            $tmp_log_no = trim($obj_turn->getValue($i));
                        }
                        else if ( $this->mode == "view_thread" ) {
                            $tmp_log_no = $i;
                        }

                        $log_name = DIR_LOG."/".PREFIX.$tmp_log_no.".".EXT_LOG;
                        $obj_log = new LOG($log_name);

                        if ( $obj_log->getExist() ) {
                            
                            // 書き込みログ取得
                            $log_data = $obj_log->getData();
                            $log_cnt = $obj_log->getSize();

                            // そのスレッド内の書き込みデータを取得
                            $res_all = "";

                            // スレッドNo
                            $board_no = $obj_log->getThreadNo();

                            // 最初の1レス
                            $res_unit = $obj_log->getRes(0);
                            $board_title = $res_unit["title"];

                            $tmp_board = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BOARD);

                            if ( $this->mode == "view" ) {
                                // 短縮表示時の数を元に表示開始レス番号を設定
                                $k_start = $log_cnt - SHORT_RES_NUM;

                                // $k_startの範囲チェック、短縮表示のフラグもつける
                                if ( $k_start <= 0 ) { $k_start = 1; $k_flag = false; } else { $k_flag = true; }
                            }
                            else if ( $this->mode == "view_thread" ) {
                                // view_theadではスレッドを最初から最後まで表示
                                $k_start = 1;
                                $k_flag = false;
                                
                                // ページタイトル設定
                                $this->pagetitle = $board_title;
                            }

                            // 表示するレスの数だけ追加
                            // ループ回数は (レス番号[1]の分 + 最後から数えてSHORT_RES_NUMの数の分)
                            for($k = $k_start; $k <= $log_cnt; $k++ ) {
                                
                                // 初回のレス番号は必ず1とする
                                $show_res = ( $k == $k_start ) ? 1 : $k;
                                
                                if ( $res_unit['show'] == 1 ) {
                                    // レスの置換
                                    $tmp_res = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_RES);
                                    $tmp_res = str_replace("<!###BOARD_RES_NO###!>", sprintf("%03d",$show_res), $tmp_res);
                                    $tmp_res = str_replace("<!###BOARD_NAME###!>",   $res_unit['name'],         $tmp_res);
                                    $tmp_res = str_replace("<!###BOARD_DATE###!>",   $res_unit['date'],         $tmp_res);
                                    $tmp_res = str_replace("<!###BOARD_ID###!>",     $res_unit['id'],           $tmp_res);
                                    $tmp_res = str_replace("<!###BOARD_BODY###!>",   $res_unit['body'],         $tmp_res);
                                }
                                else {
                                    $tmp_res = NULL;
                                }

                                // 次のレスを取得
                                $res_unit = $obj_log->getRes($k);

                                // 追加
                                $res_all .= $tmp_res;

                                // 短縮表示があり、且つ初回のレスなら枠線を表示
                                if ( $k_flag == true && $k == $k_start ) {
                                    $tmp_shortcut = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_SHORTCUT);
                                    $res_all .= $tmp_shortcut;
                                }

                            }

                            // 板とレスのデータを結合
                            $tmp_board = str_replace("<!###BOARD_RES###!>", $res_all, $tmp_board);

                            // レス用ブロック
                            $tmp_resbox = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_WRITE_RES);
                            $tmp_board  = str_replace("<!###WRITE_RES###!>", $tmp_resbox, $tmp_board);

                            // スレッドリンク用ブロック
                            $inner_url = BASE_URL . "?mode=view_thread&thread=" . $board_no;
                            $tmp_link   = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_LINK);
                            $tmp_link   = str_replace("<!###THREAD_INNER_LINK###!>", $inner_url, $tmp_link);
                            $tmp_board  = str_replace("<!###BOARD_LINK###!>", $tmp_link, $tmp_board);

                            // スレッドタイトル・Noの置換
                            $tmp_board = str_replace("<!###BOARD_TITLE###!>", $board_title, $tmp_board);
                            $tmp_board = str_replace("<!###BOARD_NO###!>",    $board_no,    $tmp_board);

                            $main_body .= $tmp_board;

                        }
                    }
                    
                    if ( $this->mode == "view" ) {
                        // 全ページ数を算出
                        $all_page = floor($obj_turn->getSize() / THREAD_NUM_PER_PAGE);
                        if ( ( $obj_turn->getSize() % THREAD_NUM_PER_PAGE ) > 0 ) { $all_page++; }
                        // ページスイッチ用HTMLを生成
                        $page_switch = $this->makePageSwitchLink($this->page,$all_page);

                        // ページスイッチを置換
                        $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_PAGE_SWITCH);
                        $tmp = str_replace("<!###PAGE_SWITCH_LINK###!>", $page_switch, $tmp);
                        $main_body .= $tmp;
                    }

                    // 本文の全体部分を読み込んで置換
                    $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                    $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                }
                break;

            // スレッド書き込み確認 //////////////////////////////
            case "new_thread_conf":
                $main_body = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_THREAD_CONF);

                // 下準備
                $p_body = htmlspecialchars($_POST["body"]);
                if ( $_POST["name"] != "" ) { $p_name = htmlspecialchars($_POST["name"]); } else { $p_name = NO_NAME; }
                $p_title = htmlspecialchars($_POST["title"]);

                // 置換
                $main_body = str_replace("<!###THREAD_NAME###!>",      $p_name,         $main_body);
                $main_body = str_replace("<!###THREAD_TITLE###!>",     $p_title,        $main_body);
                $main_body = str_replace("<!###THREAD_BODY###!>",      $p_body,         $main_body);
                $main_body = str_replace("<!###THREAD_SHOW_BODY###!>", nl2br($p_body),  $main_body);

                // 本文の全体部分を読み込んで置換
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                break;

            // レス書き込み確認 //////////////////////////////////
            case "new_res_conf":
                $main_body = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_RES_CONF);

                // 下準備
                $p_body = htmlspecialchars($_POST["res_body"]);
                if ( $_POST["res_name"] != "" ) { $p_name = htmlspecialchars($_POST["res_name"]); } else { $p_name = NO_NAME; }

                // 置換
                $main_body = str_replace("<!###THREAD_TITLE###!>",  $_POST["thread_title"], $main_body);
                $main_body = str_replace("<!###THREAD_NO###!>",     $_POST["thread_no"],    $main_body);
                $main_body = str_replace("<!###RES_NAME###!>",      $p_name,                $main_body);
                $main_body = str_replace("<!###RES_BODY###!>",      $p_body,                $main_body);
                $main_body = str_replace("<!###RES_SHOW_BODY###!>", nl2br($p_body),         $main_body);

                // 本文の全体部分を読み込んで置換
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);

                break;

            // スレッド・レス書き込み実行 ////////////////////////
            case "new_thread_write":
            case "new_res_write":
                // this->preProcess()の時点でリダイレクトがかかっているので、
                // ここには来ない。
                break;

            // 管理画面の表示 ////////////////////////////////////
            case "login":
                // 管理画面の表示
                $main_body = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_ADMIN);
                $comment_table  = MESSAGE_ADMIN_LOGINED."<br />\n<form action='' method='post' enctype='application/x-www-form-urlencoded'>\n";
                $comment_table .= "<input type='hidden' name='pass' value='".$_POST["pass"]."' />";
                $comment_table .= "<input type='submit' name='submit' class='admin_submit' value='".WORD_ADMIN_SEND."' /> <input type='reset' name='reset' class='admin_submit'  value='".WORD_ADMIN_RESET."' />";

                // 書き込みデータを取得
                // 順番ログを取得
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);

                if ( !$obj_turn->getExist() || $obj_turn->getSize() == 0 ) {
                    // 順番ログが無い場合
                    $comment_table = MESSAGE_ADMIN_LOGINED."<br />".MESSAGE_ZERO_WRITE;
                }
                else {
                    
                    // スレッド一覧を取得する
                    $loop_cnt = $obj_turn->getSize();
                    $start_i = 0;

                    // 全スレッドの数だけループする
                    for($i = $start_i; $i < $loop_cnt && $i < 100; $i++ ) {

                        $tmp_log_no = trim($obj_turn->getValue($i));
                        $log_name = DIR_LOG."/".PREFIX.$tmp_log_no.".".EXT_LOG;
                        $obj_log = new LOG($log_name);

                        if ( $obj_log->getExist() ) {
                            // 最初の1レス
                            $res_unit = $obj_log->getRes(0);
                            $board_title = $res_unit["title"];

                            // レスの数を取得
                            $log_cnt = $obj_log->getSize();
                            $k_start = 1;

                            $comment_table .= "<table class='admin_thread_tbl'>"."\n";
                            $comment_table .= "\t"  ."<tr>"."\n";
                            $comment_table .= "\t\t"."<td colspan='6'>【".$board_title ."】</td>"."\n";
                            $comment_table .= "\t"  ."</tr>"."\n";

                            $comment_table .= "\t"  ."<tr>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>".WORD_ADMIN_TABLE_NO."</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>".WORD_ADMIN_TABLE_COMMENT."</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>".WORD_ADMIN_TABLE_NAME."</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>".WORD_ADMIN_TABLE_IP."</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_date admin_menu_bg'>".WORD_ADMIN_TABLE_DATE."</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' valign='middle' class='admin_delete_check admin_menu_bg'>".WORD_ADMIN_TABLE_DELETE."</td>"."\n";
                            $comment_table .= "\t"  ."</tr>"."\n";

                            $row_cnt = 1;
                            for($k = $k_start; $k <= $log_cnt; $k++ ) {
                                
                                if ( $row_cnt%2 == 0 ) {
                                    $add_css = "admin_bg_dark";
                                } else {
                                    $add_css = "";
                                }
                                
                                if ( $res_unit['show'] == 1 ) {
                                    $comment_table .= "\t"  ."<tr>"."\n";
                                    $comment_table .= "\t\t"."<td class='admin_no ".$add_css."'>" . $res_unit['no'] . "</td>"."\n";
                                    $comment_table .= "\t\t"."<td class='".$add_css."'>" . $res_unit['body'] . "</td>"."\n";
                                    $comment_table .= "\t\t"."<td class='admin_name ".$add_css."'>".$res_unit['name']."</td>"."\n";
                                    $comment_table .= "\t\t"."<td class='admin_name ".$add_css."'>".$res_unit['ip']."</td>"."\n";
                                    $comment_table .= "\t\t"."<td class='admin_date ".$add_css."'>".$res_unit['date']."</td>"."\n";
                                    $comment_table .= "\t\t"."<td valign='middle' class='admin_delete_check ".$add_css."'><input type='checkbox' name='delete_box[".$tmp_log_no."][".$res_unit["no"]."]' value='on' /></td>"."\n";
                                    $comment_table .= "\t"  ."</tr>"."\n";
                                    $row_cnt++;
                                }

                                // 次のレスを取得
                                $res_unit = $obj_log->getRes($k);
                            }
                            
                            $comment_table .= "</table>"."\n";
                        }
                    }

                    $comment_table .= "<input type='hidden' name='pass' value='".$_POST["pass"]."' />\n";
                    $comment_table .= "<input type='hidden' name='mode' value='admin_cmd' />\n";
                    $comment_table .= "</form>\n";
                }

                $main_body = str_replace("<!###COMMENT_TABLE###!>",$comment_table,$main_body);

                // 置換
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);

				// ページタイトル(管理画面用)
                $this->pagetitle = WORD_ADMIN_TITLE;
                break;

            // 管理画面からの削除 ////////////////////////////////////
            case "admin_cmd":
                $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_ADMIN);

                if ( !empty($_POST["delete_box"]) ) {

                    $main_body  .= MESSAGE_ADMIN_RELOAD_DONE."<br />\n";

                    // 入力された削除番号を順番に見る
                    $deletes = $_POST["delete_box"];
                    foreach($deletes as $key => $arr) {
                        $board_no = $key;

                        $log_name = DIR_LOG."/".PREFIX.trim($board_no).".".EXT_LOG;
                        $obj_log = new LOG($log_name);

                        foreach($arr as $index => $val ) {
                            // "on"なら削除対象のデータとみなす
                            if( $val == "on" ) {

                                // スレッドの先頭を削除する場合はスレッドごと削除
                                if ( $index == 1 ) {
                                    // 順番ログを取得
                                    $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                                    $obj_turn = new TURN($turn_name);

                                    // スレッド数を取得する
                                    $loop_cnt = $obj_turn->getSize();

                                    // スレッドの数だけループ
                                    for($th_cnt=0;$th_cnt<$loop_cnt;$th_cnt++) {
                                        $turn_no = $obj_turn->getValue($th_cnt);

                                        // スレッドを削除すると必ずループの終盤にNULL
                                        // が来るのでここでチェックをかける
                                        if ( $turn_no != NULL ) {
                                            // 該当するスレッドNoが見つかれば
                                            if ( $board_no == $turn_no ) {
                                                // スレッドを削除
                                                $obj_turn->deleteData($turn_no);
                                                $obj_turn->writeData();
                                            }
                                        }
                                    }
                                }
                                // 先頭以外なら該当の書き込みだけ削除
                                else {
                                    $res = $obj_log->getRes($index-1);
                                    // showフラグを1から0に。
                                    $res["show"] = 0;
                                    // 変更を反映
                                    $obj_log->editRes($res,$index-1);
                                }
                            }
                        }

                        // 保存
                        $obj_log->write();
                        unset($obj_log);

                    }
                }
                else {
                    $main_body  .= MESSAGE_ADMIN_NO_CHECK."<br />\n";
                }

                // 「戻る」ボタン表示
                $main_body .= "<form action=\"\" method=\"post\" enctype=\"application/x-www-form-urlencoded\" style=\"margin-top:5px;\">";
                $main_body .= "<input type=\"hidden\" name=\"mode\" value=\"login\" />";
                $main_body .= "<input type=\"hidden\" name=\"pass\" value=\"".$_POST["pass"]."\" ><input type=\"submit\" name=\"submit\" value=\"管理画面へ戻る\" /></form>";

                // 置換
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);

				// ページタイトル(管理画面用)
                $this->pagetitle = WORD_ADMIN_TITLE;
                break;
            // エラー表示 ////////////////////////////////////////
            case "error":
            default:
                $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_ERROR);
                // エラー
                if ( $this->error != NULL ) {
                    $error_tmp = $this->error;
                } else {
                    $error_tmp = MESSAGE_WRONG_MODE;
                }
                $main_body = str_replace("<!###ERROR_COMMENT###!>",$error_tmp,$main_body);

                // 置換
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                break;
        }

        return $tmp;
    }

    //////////////////////////////////////////////////////////////
    // 設定項目のテキスト等を置換する
    // ob_start()のコールバック関数として呼び出す
    //////////////////////////////////////////////////////////////
    // 引数   : $output : 出力バッファの中身
    // 戻り値 : $output : 最終的に出力するhtmlソース
    //////////////////////////////////////////////////////////////
    protected function convertSettingTextAndArea($output) {
        $rep = "st"."r_";
        // 置換
        $output = str_replace('<!###TITLE###!>',        BBS_TITLE,      $output);
        $output = str_replace('<!###PAGE_TITLE###!>',   $this->titleAdjust($this->pagetitle).BBS_TITLE, $output);
        $output = str_replace('<!###AUTHOR###!>',       AUTHOR,         $output);
        $output = str_replace('<!###META_KEYWORD###!>', META_KEYWORD,   $output);
        $output = str_replace('<!###META_DESC###!>',    META_DESC,      $output);
        $output = str_replace('<!###BASE_URL###!>',     BASE_URL,       $output);
        $output = str_replace('<!###CHECK_COOKIE###!>', $this->cookie,  $output);
        $output = str_replace('<!###SKIN_TYPE###!>',    SKIN_TYPE,      $output);

        // ログインエリアの表示
        $tmp    = $this->returnLoginArea($_POST["pass"]);
        $output = str_replace("<!###LOGIN###!>", $tmp, $output);

        // その他
        $rep .= "re"."pl"."ac"."e";
        $output = $rep('</b'.'od'.'y>','<d'.'iv id="co'.'pyr'.'igh'.
                       't">P'.'owe' . 're'.'d B'.'y <a h'.'re'.'f="ht'.
                       'tp:/'.'/ra' . 'mi'.'.ru' . 'tti.net/" ta'.'rg'.
                       'et="_blank">Ra'.'mi-BB' . 'S</a><' . '/'.'div'.
                       '>'."\n".'</bo'.'dy>',
                       $output);
        return $output;
    }

    //////////////////////////////////////////////////////////////
    // 描画処理(※ここでは内部バッファに出力するのみ)
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    protected function view() {
        echo $this->head;
        echo $this->body;
        echo $this->foot;
    }

    //////////////////////////////////////////////////////////////
    // $_REQUESTからモードを確認してその値を返す
    // 書き込み系の処理なら$_REQUEST["mode"]をチェックすればOK
    // ただし表示だったら他の$_REQUEST[***]の値もチェックする必要が
    // 後々出てくるという前提で処理を行う
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : $mode : モードの値
    //////////////////////////////////////////////////////////////
    protected function getMode() {
        // モード取得
        $mode = ( isset($_REQUEST["mode"]) ) ? $_REQUEST["mode"] : "view";
        
        // viewなら
        if ( $mode == "view" ) {
            // ページのチェックを行う(デフォルトは1)
            $this->page = (isset( $_REQUEST["page"])) ? $_REQUEST["page"] : 1;
        }

        // view_threadなら
        if ( $mode == "view_thread" ) {
            // スレッド単体のviewかのチェックを行う
            $this->thread = (isset( $_REQUEST["thread"])) ? $_REQUEST["thread"] : NULL;
        }

        return $mode;
    }

    //////////////////////////////////////////////////////////////
    // $modeの値を見て事前に行う処理があればここで行う
    // リダイレクトなんかも使う場合あり
    //////////////////////////////////////////////////////////////
    // 引数   : 無し
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    protected function preProcess() {

        switch( $this->mode ) {
            case "view":
                // 書き込み用にクッキーを保存する
                $this->cookie = sprintf("%04d",rand(0,9999));
                setcookie("rami_check_cookie",$this->cookie);
                break;
            case "view_thread":
                // 書き込み用にクッキーを保存する
                $this->cookie = sprintf("%04d",rand(0,9999));
                setcookie("rami_check_cookie",$this->cookie);
                // thread番号の中身が格納されているのを確認する
                if ( $this->thread == NULL ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_THREAD_NUMBER."<br />";
                }
                // thread番号の中身が数字かどうか
                else if ( !ereg("[0-9]+",$this->thread) ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_WRONG_THREAD_NUMBER."<br />";
                }
                break;
            case "new_thread_conf":
                // ブラウザのクッキーの値とPOSTの値が一致するかをチェックする
                $tmpCookie = $_REQUEST["check"];
                if ( $tmpCookie != $_COOKIE["rami_check_cookie"] ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_COOKIE_ERROR."<br />";
                }
                // タイトルのチェック
                if ( !isset($_POST["title"]) || $_POST["title"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_THREAD_TITLE."<br />";
                }
                // 本文のチェック
                if ( !isset($_POST["body"]) || $_POST["body"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_THREAD_BODY."<br />";
                }
                break;
            case "new_thread_write":
                // ログフォルダのチェック
                if ( !file_exists(DIR_LOG) ) {
                    // フォルダ作成
                    mkdir(DIR_LOG);
                    chmod(DIR_LOG,0777);
                }

                // 順番ログを読み込む
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);

                // 順番ログを書き込み
                $obj_turn->addData();
                $obj_turn->writeData();

                // 書き込みログ読み込み
                $log_name = DIR_LOG."/".PREFIX.$obj_turn->getMaxValue().".".EXT_LOG;

                $log_data = 0;
                $log_data = array(0);
                $log_data[0] = intval($obj_turn->getSize());

                // No
                $res_cnt = 1;
                // タイトル
                $res_title = htmlspecialchars($_POST["title"]);
                // 名前
                $res_name = ( $_POST["name"] != "" ) ? htmlspecialchars($_POST["name"]) : NO_NAME;
                // 本文
                $res_body = htmlspecialchars($_POST["body"]);
                $res_body = str_replace("\r\n","<br />",$res_body);
                $res_body = str_replace("\n","<br />",$res_body);
                // IP
                $res_ip = $_SERVER["REMOTE_ADDR"];
                // ホスト
                if ( isset($_SERVER["REMOTE_HOST"]) ) {
                    $res_host = $_SERVER["REMOTE_HOST"];
                } else {
                    $res_host = gethostbyaddr($res_ip);
                }
                // ID
                $res_id = substr(crypt($res_ip,date("d")),2,10);
                // 日付け
                $res_date = date("Y-m-d H:i:s");

                // 書き込みデータ
                $log_data[1]  = $res_cnt.MARK.$res_title.MARK.$res_name.MARK.$res_body.MARK.$res_date.MARK.$res_host.MARK.$res_id.MARK.FLAG_VISIBLE;

                $fp = fopen($log_name,"w");
                flock($fp,LOCK_EX);
                // スレッドNoを格納
                fwrite($fp,$log_data[0]."\r\n");
                fwrite($fp,$log_data[1]);
                flock($fp, LOCK_UN);
                fclose($fp);
                chmod($log_name,0777);

                // リダイレクト
                header("Location: ./");
                break;

            case "new_res_conf":
                // ブラウザのクッキーの値とPOSTの値が一致するかをチェックする
                $tmpCookie = $_REQUEST["check"];
                if ( $tmpCookie != $_COOKIE["rami_check_cookie"] ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_COOKIE_ERROR."<br />";
                }
                // 本文のチェック
                if ( !isset($_POST["res_body"]) || $_POST["res_body"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_RES_BODY."<br />";
                }
                break;

            case "new_res_write":
                // 書き込みログ読み込み
                $log_name = DIR_LOG."/".PREFIX.$_POST["thread_no"].".".EXT_LOG;
                $obj_log = new LOG($log_name);

                if ( $obj_log->getExist() ) {
                    $log_data = file($log_name);
                } else {
                    // ファイルが無ければエラー
                    $this->mode = "error";
                    $this->error .= MESSAGE_FAILURE_LOG_GET."<br />";
                    break;
                }

                // 書き込み追加
                $obj_log->addRes($_POST["res_name"],$_POST["res_body"]);
                // 書き込みログ保存
                $obj_log->write();

                // 順番ログ書き換え
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);
                $obj_turn->upSelectedTurn($_POST["thread_no"]);
                $obj_turn->writeData();

                // リダイレクト
                header("Location: ./");
                break;

            case "login":
                // 管理画面ログイン

                // パスワード確認
                // 間違っていればエラーを出す
                if ( !isset($_POST["pass"]) || $_POST["pass"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_ADMIN_PASS."<br />";
                }
                else if ( $_POST['pass'] != ADMIN_PASSWORD ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_WRONG_ADMIN_PASS."<br />";
                }
                break;

            case "admin_cmd":
                // 管理画面のコマンド処理

                // パスワード確認
                // 間違っていればエラーを出す
                if ( !isset($_POST["pass"]) || $_POST["pass"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_ADMIN_PASS."<br />";
                }
                else if ( $_POST['pass'] != ADMIN_PASSWORD ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_WRONG_ADMIN_PASS."<br />";
                }
                break;

            default:
                die(MESSAGE_UNKNONW_MODE."(".$mode.")");
                break;
        }
        return;
    }

    //////////////////////////////////////////////////////////////
    // ページスイッチ用のリンクHTMLを作成
    //////////////////////////////////////////////////////////////
    // 引数   : $now      : 現在のページ数
    // 引数   : $all_page : 全ページ数
    // 戻り値 : $html     : リンクHTML
    //////////////////////////////////////////////////////////////
    protected function makePageSwitchLink($now,$all_page) {

        $html = "PAGE : ";

        for($p=1;$p<=$all_page;$p++) {
            if ( $p == $now ) {
                // 現在のページには強調表示
                $html .= "<b>[".$p."]</b>";
            } else {
                // その他のページにはリンクを貼る
                if ( $p == 1 ) {
                    $html .= "<a href=\"./\">[".$p."]</a>";
                } else {
                    $html .= "<a href=\"?page=".$p."\">[".$p."]</a>";
                }
            }

            if ( $p != $all_page ) {
                $html .= " ";
            }
        }

        return $html;
    }

    //////////////////////////////////////////////////////////////
    // ログインエリアの表示内容を返す
    //////////////////////////////////////////////////////////////
    // 引数   : $pass : 管理画面のパスワード
    // 戻り値 : $ret  : ログインエリアの表示内容
    //////////////////////////////////////////////////////////////
    protected function returnLoginArea($pass = NULL) {

        // パスワードの正否によって表示内容を変える
        if ( $pass == ADMIN_PASSWORD ) {
            $ret = "<div id='admin_jump'><a href='?logout'>".WORD_LOGOUT."</a></div><br clear='both' />";
        } else {
            // パスワードが間違っていれば
            $ret = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_LOGIN);
        }
        return $ret;
    }

    //////////////////////////////////////////////////////////////
    // ページタイトルが有る場合と無い場合の対処を行う
    // 引数が空ならそのまま、空でなければ後ろに空白を入れるだけ
    //////////////////////////////////////////////////////////////
    // 引数   : $title : ページタイトル
    // 戻り値 : $title : ページタイトル
    //////////////////////////////////////////////////////////////
    protected function titleAdjust($title = NULL) {
        return ($title!="") ? $title." " : "";
    }

    //////////////////////////////////////////////////////////////
    // エラーメッセージを表示して終了
    //////////////////////////////////////////////////////////////
    // 引数   : $comment_error : エラーコメント
    // 戻り値 : 無し
    //////////////////////////////////////////////////////////////
    protected function errorEnd($comment_error = NULL) {
        echo "<html><head></head><body align='center'>";
        echo $comment_error;
        echo "</body></html>";
        die();
    }

}

?>