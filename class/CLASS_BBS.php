<?php
//////////////////////////////////////////////////////////////////
// BBS ���饹
// �Ǽ��ĤȤ��Ƥ�����Ū�ʽ����򰷤�
// �������ݥ��饹�Ȥ����������٤��Ȥϻפ���ΤΡ�
// �������PHP4��ư��ʤ���ǽ��������Τǡ�
// ��������ݲ����Ƥ��ʤ���
//////////////////////////////////////////////////////////////////
class BBS {

    //************************************************************
    // �����ѿ�
    protected $mode;    // BBS��ν�������
    protected $html;    // (��������̤����)
    protected $head;    // �إå��Υ�����
    protected $foot;    // �եå��Υ�����
    protected $body;    // �ᥤ�󥳥�ƥ�ĤΥ�����
    protected $cookie;  // �񤭹��߽������Ѥ��륯�å�������
    protected $error;   // ���顼���˥����Ȥ��Ǽ����
    protected $page;    // ɽ���ڡ�����
    protected $thread;  // ɽ������å��ֹ���Ǽ

    //************************************************************
    // �ʲ����᥽�å�

    //////////////////////////////////////////////////////////////
    // ���󥹥ȥ饯��
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    public function BBS() {
        $this->checkInitFile();
    }

    //////////////////////////////////////////////////////////////
    // �����γ���
    // ���줬����BBS��main�ؿ��ߤ����ʤ�Ρ�
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    public function start() {
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
    // ����ե�����γ�ǧ������ե����뤬̵����к������롣
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    protected function checkInitFile() {
        if ( !file_exists( INIT_SERVER ) )      { $this->makeServerInit(); }
        if ( !file_exists( INIT_BBS_SETTING ) ) { $this->makeBBSInit(); }

        // ����ɤ߹���
        require_once(INIT_SERVER);
        require_once(INIT_BBS_SETTING);
    }

    //////////////////////////////////////////////////////////////
    // server.php�κ�����
    // ��������оݤΥ����Ф˴ؤ��������Ǽ����
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    protected function makeServerInit() {
        $rami_version = THIS_VER;           // ���ηǼ��ĤΥС������(���server.ini�ˤ�Ĥ��Ƥ���)
        $php_version = phpversion();        // PHP�ΥС���������
        $serverpath = dirname(__FILE__);    // ���Υե���������륵���Хѥ������
        $host = $_SERVER["SERVER_NAME"];    // �ۥ���̾����
        $prefix = "";                       // �ץ�ե��å��������������
        for( $i=0; $i<NUM_PREFIX; $i++ ) $prefix .= chr(rand(0,25)+65);
        $prefix .= "_";

        // ���Ƥ�ޤȤ��
        $server_init = '<'.'?php'."\n";
        $server_init.= 'define("RAMI_VER","'.$rami_version.'");'."\n";
        $server_init.= 'define("PHP_VER","'.$php_version.'");'."\n";
        $server_init.= 'define("SERVER_PATH","'.$serverpath.'");'. "\n";
        $server_init.= 'define("HOST","'.$host.'");'."\n";
        $server_init.= 'define("PREFIX","'.$prefix.'");';
        $server_init.= "\n?".'>';

        // �ե�����˽񤭹���
        $fp = fopen(INIT_SERVER,"w");
        flock($fp, LOCK_EX);
        fwrite($fp, $server_init);
        flock($fp, LOCK_UN);
        chmod(INIT_SERVER, 0644);
    }

    //////////////////////////////////////////////////////////////
    // setting.php�κ�����
    // ���֤���BBS�˴ؤ��������Ǽ����
    // ����Ū�ˤϥե����륢�å׻���¸�ߤ��Ƥ���ϥ�������
    // �⤷��¸�ߤ��ʤ��ä���ǥե���ȤΥե�������������
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    protected function makeBBSInit() {
        $bbs_title    = WORD_DEFAULT_TITLE;             // BBS̾
        $meta_desc    = WORD_DEFAULT_DESC;              // �᥿�ǥ�������ץ����
        $meta_keyword = WORD_DEFAULT_KEYWORD;           // �᥿�������
        $base_url     = WORD_DEFAULT_BASE_URL;          // �١����Ȥʤ�URL
        $author       = WORD_DEFAULT_AUTHOR;            // ���̾
        $skin         = WORD_DEFAULT_SKIN;              // �ǥե���ȥ�����
        $no_name      = WORD_DEFAULT_NO_NAME;           // ̵̾��
        $short_rs     = WORD_DEFAULT_SHORT_RS;          // û��ɽ�����Υ쥹��
        $thread_num_pp= WORD_DEFAULT_THREAD_NUM_PP;     // 1�ڡ�����Υ���åɿ�
        $admin_password = WORD_DEFAULT_ADMIN_PASS;      // �������̤Υѥ����

        // �ޤȤ��
        $bbs_init = '<'.'?php'."\n";
        $bbs_init.= '// BBS�Υ����ȥ�̾'."\n".'define("BBS_TITLE","'.$bbs_title.'");'."\n\n";
        $bbs_init.= '// BBS�ΰ������ʸ'."\n".'define("META_DESC","'.$meta_desc.'");'."\n\n";
        $bbs_init.= '// BBS����ħ�Ȥʤ륭����ɤ򥫥�޶��ڤ������'."\n".'define("META_KEYWORD","'.$meta_keyword.'");'."\n\n";
        $bbs_init.= '// // �١����Ȥʤ�URL(�Ǹ�ϥ���å�����Ĥ���)'."\n".'define("BASE_URL","'.$author.'");'."\n\n";
        $bbs_init.= '// �����ͤ�̾��'."\n".'define("AUTHOR","'.$author.'");'."\n\n";
        $bbs_init.= '// ����������'."\n".'define("SKIN_TYPE","'.$skin.'");'."\n\n";
        $bbs_init.= '// ̾��̤��������̾��'."\n".'define("NO_NAME","'.$no_name.'");'."\n\n";
        $bbs_init.= '// ����å�û��ɽ����ɽ���쥹��'."\n".'define("SHORT_RES_NUM",'.$short_rs.');'."\n\n";
        $bbs_init.= '// 1�ڡ�����ɽ�������륹��åɤο�'."\n".'define("THREAD_NUM_PER_PAGE",'.$thread_num_pp.');'."\n\n";
        $bbs_init.= '// �������̤ؤΥѥ����'."\n".'define("ADMIN_PASSWORD","'.$admin_password.'");'."\n\n";
        $bbs_init.= '?>';

        // �ե�����˽񤭹���
        $fp = fopen(INIT_BBS_SETTING,"w");
        flock($fp, LOCK_EX);
        fwrite($fp, $bbs_init);
        flock($fp, LOCK_UN);
        chmod(INIT_BBS_SETTING, 0644);
    }

    //////////////////////////////////////////////////////////////
    // �إå�����
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $tmp : �إå�����ȤΥ�����
    //////////////////////////////////////////////////////////////
    protected function setHeader() {
        $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_HEADER);
        return $tmp;
    }

    //////////////////////////////////////////////////////////////
    // �եå�����
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $tmp : �եå�����ȤΥ�����
    //////////////////////////////////////////////////////////////
    protected function setFooter() {
        $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_FOOTER);
        return $tmp;
    }

    //////////////////////////////////////////////////////////////
    // �ܥǥ�����
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $tmp : ��ʸ�Υ�����
    //////////////////////////////////////////////////////////////
    protected function setBody() {

        // �����
        $main_body = "";

        // �⡼�ɤˤ�äƽ�����ʬ����
        switch( $this->mode ) {
            // �̾��ɽ�� ////////////////////////////////////////
            case "view":
                // �񤭹��ߥե�����
                $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_WRITE_BOX);

            // ����åɤ�ɽ�� ////////////////////////////////////
            case "view_thread":

                // ���֥������
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);

                if ( !$obj_turn->getExist() ) {
                    // ���֥���̵�����
                    $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_NO_WRITE);
                    $error_tmp = MESSAGE_ZERO_WRITE;
                    $main_body = str_replace("<!###NO_WRITE###!>",$error_tmp,$main_body);
                    $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                    $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                }
                else {

                    // view�⡼�ɤ�ɬ�פʽ���
                    if ( $this->mode == "view" ) {

                        // ����åɰ������������
                        $loop_cnt = $obj_turn->getSize();
                        if ( $loop_cnt > NUM_THREAD_LIST ) {
                            $loop_cnt = NUM_THREAD_LIST;
                        }
                        $thread_list = "";
                        for($i=0;$i<$loop_cnt;$i++) {
                            // ����åɤο������롼�פ��ƥ�󥯤��������
                            $board_no = $obj_turn->getValue($i);
                            $log_name = DIR_LOG."/".PREFIX.trim($obj_turn->getValue($i)).".".EXT_LOG;
                            $obj_log = new LOG($log_name);
                            $inner_url = BASE_URL . "?mode=view_thread&thread=" . $board_no;
                            $thread_list .= "<a href=\"".$inner_url."\" class=\"thread_list_link\">".$obj_log->getTitle()."(".$obj_log->getSize().")</a>";
                        }

                        // ����åɰ������ɲ�
                        $tmp_thread_list = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_THREAD_LIST);
                        $tmp_thread_list = str_replace("<!###THREAD_LIST###!>", $thread_list, $tmp_thread_list);
                        $main_body .= $tmp_thread_list;

                        // �롼�פ˻��Ѥ����ͤ����ꤹ��
                        //   �ڡ�����{1,2,3,4....}��1���������Τ��Ф���
                        //   ����¾�Υǡ�����{0,1,2,3....}��0���������ΤǤ�����Ĵ��
                        if ( $this->page == 1 ) {
                            $start_i = 0;
                            $loop_cnt = $obj_turn->getLoopLimit( 0,THREAD_NUM_PER_PAGE );
                        } else {
                            $start_i = ($this->page - 1) * THREAD_NUM_PER_PAGE;
                            $loop_cnt = $obj_turn->getLoopLimit( $start_i, THREAD_NUM_PER_PAGE );
                        }
                    }
                    else if ( $this->mode == "view_thread" ) {

                        // �롼�פ˻��Ѥ����ͤ����ꤹ��
                        // view_thread�Ǥ�ɽ�����륹��åɤ�1�Ĥ����ʤΤǡ�
                        // �롼�פ�1��ǺѤ�褦������
                        $start_i = $this->thread;
                        $loop_cnt = $start_i+1;
                    }


                    // ���󤹤��,
                    // $obj_turn->data[$start_i] �� [$loop_cnt] �ޤǤ�롼�פ��Ƥ���
                    for($i = $start_i; $i < $loop_cnt && $i < 100; $i++ ) {

                        // �񤭹��ߥ��Υѥ�
                        if ( $this->mode == "view") {
                            $tmp_log_no = trim($obj_turn->getValue($i));
                        }
                        else if ( $this->mode == "view_thread" ) {
                            $tmp_log_no = $i;
                        }

                        $log_name = DIR_LOG."/".PREFIX.$tmp_log_no.".".EXT_LOG;
                        $obj_log = new LOG($log_name);

                        if ( $obj_log->getExist() ) {
                            
                            // �񤭹��ߥ�����
                            $log_data = $obj_log->getData();
                            $log_cnt = $obj_log->getSize();

                            // ���Υ���å���ν񤭹��ߥǡ��������
                            $res_all = "";

                            // ����å�No
                            $board_no = $obj_log->getThreadNo();

                            // �ǽ��1�쥹
                            $res_unit = $obj_log->getRes(0);
                            $board_title = $res_unit["title"];

                            $tmp_board = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BOARD);

                            if ( $this->mode == "view" ) {
                                // û��ɽ�����ο��򸵤�ɽ�����ϥ쥹�ֹ������
                                $k_start = $log_cnt - SHORT_RES_NUM;

                                // $k_start���ϰϥ����å���û��ɽ���Υե饰��Ĥ���
                                if ( $k_start <= 0 ) { $k_start = 1; $k_flag = false; } else { $k_flag = true; }
                            }
                            else if ( $this->mode == "view_thread" ) {
                                // view_thead�Ǥϥ���åɤ�ǽ餫��Ǹ�ޤ�ɽ��
                                $k_start = 1;
                                $k_flag = false;
                            }

                            // ɽ������쥹�ο������ɲ�
                            // �롼�ײ���� (�쥹�ֹ�[1]��ʬ + �Ǹ夫�������SHORT_RES_NUM�ο���ʬ)
                            for($k = $k_start; $k <= $log_cnt; $k++ ) {
                                
                                // ���Υ쥹�ֹ��ɬ��1�Ȥ���
                                $show_res = ( $k == $k_start ) ? 1 : $k;
                                
                                if ( $res_unit['show'] == 1 ) {
	                                // �쥹���ִ�
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

                                // ���Υ쥹�����
                                $res_unit = $obj_log->getRes($k);

                                // �ɲ�
                                $res_all .= $tmp_res;

                                // û��ɽ�������ꡢ��Ľ��Υ쥹�ʤ�������ɽ��
                                if ( $k_flag == true && $k == $k_start ) {
                                    $tmp_shortcut = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_SHORTCUT);
                                    $res_all .= $tmp_shortcut;
                                }

                            }

                            // �Ĥȥ쥹�Υǡ�������
                            $tmp_board = str_replace("<!###BOARD_RES###!>", $res_all, $tmp_board);

                            // �쥹�ѥ֥�å�
                            $tmp_resbox = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_WRITE_RES);
                            $tmp_board  = str_replace("<!###WRITE_RES###!>", $tmp_resbox, $tmp_board);

                            // ����åɥ���ѥ֥�å�
                            $inner_url = BASE_URL . "?mode=view_thread&thread=" . $board_no;
                            $tmp_link   = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_LINK);
                            $tmp_link   = str_replace("<!###THREAD_INNER_LINK###!>", $inner_url, $tmp_link);
                            $tmp_board  = str_replace("<!###BOARD_LINK###!>", $tmp_link, $tmp_board);

                            // ����åɥ����ȥ롦No���ִ�
                            $tmp_board = str_replace("<!###BOARD_TITLE###!>", $board_title, $tmp_board);
                            $tmp_board = str_replace("<!###BOARD_NO###!>",    $board_no,    $tmp_board);

                            $main_body .= $tmp_board;

                        }
                    }
                    
                    if ( $this->mode == "view" ) {
                        // ���ڡ������򻻽�
                        $all_page = floor($obj_turn->getSize() / THREAD_NUM_PER_PAGE);
                        if ( ( $obj_turn->getSize() % THREAD_NUM_PER_PAGE ) > 0 ) { $all_page++; }
                        // �ڡ��������å���HTML������
                        $page_switch = $this->makePageSwitchLink($this->page,$all_page);

                        // �ڡ��������å����ִ�
                        $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_PAGE_SWITCH);
                        $tmp = str_replace("<!###PAGE_SWITCH_LINK###!>", $page_switch, $tmp);
                        $main_body .= $tmp;
                    }

                    // ��ʸ��������ʬ���ɤ߹�����ִ�
                    $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                    $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                }
                break;

            // ����åɽ񤭹��߳�ǧ //////////////////////////////
            case "new_thread_conf":
                $main_body = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_THREAD_CONF);

                // ������
                $p_body = htmlspecialchars($_POST["body"]);
                if ( $_POST["name"] != "" ) { $p_name = $_POST["name"]; } else { $p_name = NO_NAME; }

                // �ִ�
                $main_body = str_replace("<!###THREAD_NAME###!>",      $p_name,         $main_body);
                $main_body = str_replace("<!###THREAD_TITLE###!>",     $_POST["title"], $main_body);
                $main_body = str_replace("<!###THREAD_BODY###!>",      $p_body,         $main_body);
                $main_body = str_replace("<!###THREAD_SHOW_BODY###!>", nl2br($p_body),  $main_body);

                // ��ʸ��������ʬ���ɤ߹�����ִ�
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                break;

            // �쥹�񤭹��߳�ǧ //////////////////////////////////
            case "new_res_conf":
                $main_body = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_RES_CONF);

                // ������
                $p_body = htmlspecialchars($_POST["res_body"]);
                if ( $_POST["res_name"] != "" ) { $p_name = $_POST["res_name"]; } else { $p_name = NO_NAME; }

                // �ִ�
                $main_body = str_replace("<!###THREAD_TITLE###!>",  $_POST["thread_title"], $main_body);
                $main_body = str_replace("<!###THREAD_NO###!>",     $_POST["thread_no"],    $main_body);
                $main_body = str_replace("<!###RES_NAME###!>",      $p_name,                $main_body);
                $main_body = str_replace("<!###RES_BODY###!>",      $p_body,                $main_body);
                $main_body = str_replace("<!###RES_SHOW_BODY###!>", nl2br($p_body),         $main_body);

                // ��ʸ��������ʬ���ɤ߹�����ִ�
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);

                break;

            // ����åɡ��쥹�񤭹��߼¹� ////////////////////////
            case "new_thread_write":
            case "new_res_write":
                // this->preProcess()�λ����ǥ�����쥯�Ȥ������äƤ���Τǡ�
                // �����ˤ���ʤ���
                break;

            // �������̤�ɽ�� ////////////////////////////////////
            case "login":
                // �������̤�ɽ��
                $main_body = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_ADMIN);
                $comment_table  = "�������̤Ǥ���<br />\n<form action='' method='post' enctype='application/x-www-form-urlencoded'>\n";
                $comment_table .= "<input type='hidden' name='pass' value='".$_POST["pass"]."' />";
                $comment_table .= "<input type='submit' name='submit' class='admin_submit' value='��������' /> <input type='reset' name='reset' class='admin_submit'  value='�ꥻ�å�' />";

                // �񤭹��ߥǡ��������
                // ���֥������
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);

                if ( !$obj_turn->getExist() ) {
                    // ���֥���̵�����
                    $comment_table = MESSAGE_ZERO_WRITE;
                }
                else {
                    
                    // ����åɰ������������
                    $loop_cnt = $obj_turn->getSize();
                    $start_i = 0;

                    // ������åɤο������롼�פ���
                    for($i = $start_i; $i < $loop_cnt && $i < 100; $i++ ) {

                        $tmp_log_no = trim($obj_turn->getValue($i));
                        $log_name = DIR_LOG."/".PREFIX.$tmp_log_no.".".EXT_LOG;
                        $obj_log = new LOG($log_name);

                        if ( $obj_log->getExist() ) {
                            // �ǽ��1�쥹
                            $res_unit = $obj_log->getRes(0);
                            $board_title = $res_unit["title"];

                            // �쥹�ο������
                            $log_cnt = $obj_log->getSize();
                            $k_start = 1;

                            $comment_table .= "<table class='admin_thread_tbl'>"."\n";
                            $comment_table .= "\t"  ."<tr>"."\n";
                            $comment_table .= "\t\t"."<td colspan='6'>��".$board_title ."��</td>"."\n";
                            $comment_table .= "\t"  ."</tr>"."\n";

                            $comment_table .= "\t"  ."<tr>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>No</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>������</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>̾��</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_menu_bg'>IP</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' class='admin_date admin_menu_bg'>�񤭹��߻���</td>"."\n";
                            $comment_table .= "\t\t"."<td align='center' valign='middle' class='admin_delete_check admin_menu_bg'>���</td>"."\n";
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

                                // ���Υ쥹�����
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

                // �ִ�
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                break;

            // �������̤���κ�� ////////////////////////////////////
            case "admin_cmd":
                $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_ADMIN);

				if ( !empty($_POST["delete_box"]) ) {

	                $main_body  .= "�������ޤ�����<br />\n";

					// ���Ϥ��줿����ֹ����֤˸���
					$deletes = $_POST["delete_box"];
					foreach($deletes as $key => $arr) {
	                    $board_no = $key;
	                    
                    	// ����åɤκǽ�ν񤭹��ߤ�����������ϥ���åɤ��Ⱥ��
	                    if ( $board_no == 1 ) {

			                // ���֥������
			                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
			                $obj_turn = new TURN($turn_name);
			                
	                    }
						// ����ʳ������̤˽񤭹��ߤ���
	                    else {
		                    $log_name = DIR_LOG."/".PREFIX.trim($board_no).".".EXT_LOG;
		                    $obj_log = new LOG($log_name);

							foreach($arr as $index => $val ) {
								if( $val == "on" ) {
									$res = $obj_log->getRes($index-1);
									// show�ե饰��1����0�ˡ�
									$res["show"] = 0;
									// �ѹ���ȿ��
									$obj_log->editRes($res,$index-1);
								}
							}

							// ��¸
							$obj_log->write();
							unset($obj_log);
						}

					}
				}
				else {
	                $main_body  .= "����˥����å������Ĥ�Ĥ��Ƥ��ޤ���<br />\n";
				}

				// �����ץܥ���ɽ��
                $main_body .= "<form action=\"\" method=\"post\" enctype=\"application/x-www-form-urlencoded\" style=\"margin-top:5px;\">";
                $main_body .= "<input type=\"hidden\" name=\"mode\" value=\"login\" />";
                $main_body .= "<input type=\"hidden\" name=\"pass\" value=\"".$_POST["pass"]."\" ><input type=\"submit\" name=\"submit\" value=\"�������̤����\" /></form>";

                // �ִ�
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
            	break;
            // ���顼ɽ�� ////////////////////////////////////////
            case "error":
            default:
                $main_body .= file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_ERROR);
                // ���顼
                if ( $this->error != NULL ) {
                    $error_tmp = $this->error;
                } else {
                    $error_tmp = MESSAGE_WRONG_MODE;
                }
                $main_body = str_replace("<!###ERROR_COMMENT###!>",$error_tmp,$main_body);

                // �ִ�
                $tmp = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_BODY);
                $tmp = str_replace("<!###MAIN_BODY###!>", $main_body, $tmp);
                break;
        }

        return $tmp;
    }

    //////////////////////////////////////////////////////////////
    // ������ܤΥƥ����������ִ�����
    // ob_start()�Υ�����Хå��ؿ��Ȥ��ƸƤӽФ�
    //////////////////////////////////////////////////////////////
    // ����   : $output : ���ϥХåե������
    // ����� : $output : �ǽ�Ū�˽��Ϥ���html������
    //////////////////////////////////////////////////////////////
    protected function convertSettingTextAndArea($output) {
        $rep = "st"."r_";
        // �ִ�
        $output = str_replace('<!###TITLE###!>',            BBS_TITLE,     $output);
        $output = str_replace('<!###AUTHOR###!>',           AUTHOR,        $output);
        $output = str_replace('<!###META_KEYWORD###!>',     META_KEYWORD,  $output);
        $output = str_replace('<!###META_DESC###!>',        META_DESC,     $output);
        $output = str_replace('<!###BASE_URL###!>',         BASE_URL,      $output);
        $output = str_replace('<!###CHECK_COOKIE###!>',     $this->cookie, $output);
        $output = str_replace('<!###SKIN_TYPE###!>',        SKIN_TYPE,     $output);

        // �����󥨥ꥢ��ɽ��
        $tmp    = $this->returnLoginArea($_POST["pass"]);
        $output = str_replace("<!###LOGIN###!>", $tmp, $output);

        // ����¾
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
    // �������(�������Ǥ������Хåե��˽��Ϥ���Τ�)
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    protected function view() {
        echo $this->head;
        echo $this->body;
        echo $this->foot;
    }

    //////////////////////////////////////////////////////////////
    // $_REQUEST����⡼�ɤ��ǧ���Ƥ����ͤ��֤�
    // �񤭹��߷Ϥν����ʤ�$_REQUEST["mode"]������å������OK
    // ������ɽ�����ä���¾��$_REQUEST[***]���ͤ�����å�����ɬ�פ�
    // �塹�ФƤ���Ȥ�������ǽ�����Ԥ�
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $mode : �⡼�ɤ���
    //////////////////////////////////////////////////////////////
    protected function getMode() {
        // �⡼�ɼ���
        $mode = ( isset($_REQUEST["mode"]) ) ? $_REQUEST["mode"] : "view";
        
        // view�ʤ�
        if ( $mode == "view" ) {
            // �ڡ����Υ����å���Ԥ�(�ǥե���Ȥ�1)
            $this->page = (isset( $_REQUEST["page"])) ? $_REQUEST["page"] : 1;
        }

        // view_thread�ʤ�
        if ( $mode == "view_thread" ) {
            // ����å�ñ�Τ�view���Υ����å���Ԥ�
            $this->thread = (isset( $_REQUEST["thread"])) ? $_REQUEST["thread"] : NULL;
        }

        return $mode;
    }

    //////////////////////////////////////////////////////////////
    // $mode���ͤ򸫤ƻ����˹Ԥ�����������Ф����ǹԤ�
    // ������쥯�Ȥʤ󤫤�Ȥ���礢��
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    protected function preProcess() {

        switch( $this->mode ) {
            case "view":
                // �񤭹����Ѥ˥��å�������¸����
                $this->cookie = sprintf("%04d",rand(0,9999));
                setcookie("rami_check_cookie",$this->cookie);
                break;
            case "view_thread":
                // �񤭹����Ѥ˥��å�������¸����
                $this->cookie = sprintf("%04d",rand(0,9999));
                setcookie("rami_check_cookie",$this->cookie);
                // thread�ֹ����Ȥ���Ǽ����Ƥ���Τ��ǧ����
                if ( $this->thread == NULL ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_THREAD_NUMBER."<br />";
                }
                // thread�ֹ����Ȥ��������ɤ���
                else if ( !ereg("[0-9]+",$this->thread) ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_WRONG_THREAD_NUMBER."<br />";
                }
                break;
            case "new_thread_conf":
                // �֥饦���Υ��å������ͤ�POST���ͤ����פ��뤫������å�����
                $tmpCookie = $_REQUEST["check"];
                if ( $tmpCookie != $_COOKIE["rami_check_cookie"] ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_COOKIE_ERROR."<br />";
                }
                // �����ȥ�Υ����å�
                if ( !isset($_POST["title"]) || $_POST["title"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_THREAD_TITLE."<br />";
                }
                // ��ʸ�Υ����å�
                if ( !isset($_POST["body"]) || $_POST["body"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_THREAD_BODY."<br />";
                }
                break;
            case "new_thread_write":
                // ���ե�����Υ����å�
                if ( !file_exists(DIR_LOG) ) {
                    // �ե��������
                    mkdir(DIR_LOG);
                    chmod(DIR_LOG,0777);
                }

                // ���֥����ɤ߹���
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);

                // ���֥���񤭹���
                $obj_turn->addData();
                $obj_turn->writeData();

                // �񤭹��ߥ��ɤ߹���
                $log_name = DIR_LOG."/".PREFIX.$obj_turn->getSize().".".EXT_LOG;

                $log_data = 0;
                $log_data = array(0);
                $log_data[0] = intval($obj_turn->getSize());

                // No
                $res_cnt = 1;
                // �����ȥ�
                $res_title = $_POST["title"];
                // ̾��
                $res_name = ( $_POST["name"] != "" ) ? $_POST["name"] : NO_NAME;
                // ��ʸ
                $res_body = htmlspecialchars($_POST["body"]);
                $res_body = str_replace("\r\n","<br />",$res_body);
                $res_body = str_replace("\n","<br />",$res_body);
                // IP
                $res_ip = $_SERVER["REMOTE_ADDR"];
                // �ۥ���
                if ( isset($_SERVER["REMOTE_HOST"]) ) {
                    $res_host = $_SERVER["REMOTE_HOST"];
                } else {
                    $res_host = gethostbyaddr($res_ip);
                }
                // ID
                $res_id = substr(crypt($res_ip,date("d")),2,10);
                // ���դ�
                $res_date = date("Y-m-d H:i:s");

                // �񤭹��ߥǡ���
                $log_data[1]  = $res_cnt.MARK.$res_title.MARK.$res_name.MARK.$res_body.MARK.$res_date.MARK.$res_host.MARK.$res_id.MARK.FLAG_VISIBLE;

                $fp = fopen($log_name,"w");
                flock($fp,LOCK_EX);
                // ����å�No���Ǽ
                fwrite($fp,$log_data[0]."\r\n");
                fwrite($fp,$log_data[1]);
                flock($fp, LOCK_UN);
                fclose($fp);
                chmod($log_name,0777);

                // ������쥯��
                header("Location: ./");
                break;

            case "new_res_conf":
                // �֥饦���Υ��å������ͤ�POST���ͤ����פ��뤫������å�����
                $tmpCookie = $_REQUEST["check"];
                if ( $tmpCookie != $_COOKIE["rami_check_cookie"] ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_COOKIE_ERROR."<br />";
                }
                // ��ʸ�Υ����å�
                if ( !isset($_POST["res_body"]) || $_POST["res_body"] == "" ) {
                    $this->mode = "error";
                    $this->error .= MESSAGE_EMPTY_RES_BODY."<br />";
                }
                break;

            case "new_res_write":
                // �񤭹��ߥ��ɤ߹���
                $log_name = DIR_LOG."/".PREFIX.$_POST["thread_no"].".".EXT_LOG;
                $obj_log = new LOG($log_name);

                if ( $obj_log->getExist() ) {
                    $log_data = file($log_name);
                } else {
                    // �ե����뤬̵����Х��顼
                    $this->mode = "error";
                    $this->error .= MESSAGE_FAILURE_LOG_GET."<br />";
                    break;
                }

                // �񤭹����ɲ�
                $obj_log->addRes($_POST["res_name"],$_POST["res_body"]);
                // �񤭹��ߥ���¸
                $obj_log->write();

                // ���֥��񤭴���
                $turn_name = DIR_LOG."/".FN_TURN.".".EXT_LOG;
                $obj_turn = new TURN($turn_name);
                $obj_turn->upSelectedTurn($_POST["thread_no"]);
                $obj_turn->writeData();

                // ������쥯��
                header("Location: ./");
                break;

            case "login":
                // �������̥�����

                // �ѥ���ɳ�ǧ
                // �ְ�äƤ���Х��顼��Ф�
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
                // �������̤Υ��ޥ�ɽ���

                // �ѥ���ɳ�ǧ
                // �ְ�äƤ���Х��顼��Ф�
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
    // �ڡ��������å��ѤΥ��HTML�����
    //////////////////////////////////////////////////////////////
    // ����   : $now      : ���ߤΥڡ�����
    // ����   : $all_page : ���ڡ�����
    // ����� : $html     : ���HTML
    //////////////////////////////////////////////////////////////
    protected function makePageSwitchLink($now,$all_page) {

        $html = "PAGE : ";

        for($p=1;$p<=$all_page;$p++) {
            if ( $p == $now ) {
                // ���ߤΥڡ����ˤ϶�Ĵɽ��
                $html .= "<b>[".$p."]</b>";
            } else {
                // ����¾�Υڡ����ˤϥ�󥯤�Ž��
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
    // �����󥨥ꥢ��ɽ�����Ƥ��֤�
    //////////////////////////////////////////////////////////////
    // ����   : $pass : �������̤Υѥ����
    // ����� : $ret  : �����󥨥ꥢ��ɽ������
    //////////////////////////////////////////////////////////////
    protected function returnLoginArea($pass = NULL) {

        // �ѥ���ɤ����ݤˤ�ä�ɽ�����Ƥ��Ѥ���
        if ( $pass == ADMIN_PASSWORD ) {
            $ret = "<div id='admin_jump'><a href='?logout'>".WORD_LOGOUT."</a></div><br clear='both' />";
        } else {
            // �ѥ���ɤ��ְ�äƤ����
            $ret = file_get_contents(DIR_SKIN."/".SKIN_TYPE."/".INC_LOGIN);
        }
        return $ret;
    }

}

?>