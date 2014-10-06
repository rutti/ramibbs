<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
// ***************************************************************
// CLASS_LOG.php
// ***************************************************************
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

//////////////////////////////////////////////////////////////////
// LOG ���饹
// �񤭹��ޤ줿�ǡ����δ�����Ԥ����饹
//////////////////////////////////////////////////////////////////
class LOG {

    //************************************************************
    // �����ѿ�
    protected $flag_exist;  // �����ե�����¸�ߥե饰
    protected $path;        // �����ե�����Υѥ�
    protected $data;        // �񤭹��ߥǡ���
    protected $size;        // �񤭹�������
    protected $thread_no;   // ����å�No
    protected $m_count;     // ����������

    //************************************************************
    // �ʲ����᥽�å�

    //////////////////////////////////////////////////////////////
    // ���󥹥ȥ饯��
    //////////////////////////////////////////////////////////////
    // ����   : $p   : �ɤ߹�������ǡ����Υѥ�
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    public function LOG($p = NULL) {
        $this->flag_exist = false;
        $this->path = NULL;
        $this->data = NULL;
        $this->size = 0;
        $this->thread_no = 0;
        $this->m_count = 0;

        if ( $p != NULL ) {
            $this->open($p);
        }
    }

    //////////////////////////////////////////////////////////////
    // �ե������ɤ߹���
    //////////////////////////////////////////////////////////////
    // ����   : $p     : �ɤ߹�������ǡ����Υѥ�
    // ����� : bool��
    //////////////////////////////////////////////////////////////
    public function open($p) {
        // �ե����뤬¸�ߤ��뤫��ǽ�˳�ǧ
        if ( !file_exists($p) ) {
            return false;
        }

        // �ѥ�����¸
        $this->path = $p;
        // �ǡ��������
        $this->data = file($p);
        // ����å�No�����
        $this->thread_no = intval($this->data[0]);
        // �񤭹��߿�����¸
        $this->size = count($this->data) - 1;
        // �ե�����¸�ߥե饰��on�ˤ���
        $this->flag_exist = true;

        // �ǡ�������Ƭ�Υ���å�No�Υǡ�����ä�
        array_shift($this->data);

        return true;
    }

    //////////////////////////////////////////////////////////////
    // �ե������̵ͭ���֤�
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $file_exist : �ե������̵ͭ��bool�ͤ��֤�
    //////////////////////////////////////////////////////////////
    public function getExist() {
        return $this->flag_exist;
    }

    //////////////////////////////////////////////////////////////
    // �ե�����Υ��������֤�
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $size : �񤭹��ߤο����֤�
    //////////////////////////////////////////////////////////////
    public function getSize() {
        return $this->size;
    }

    //////////////////////////////////////////////////////////////
    // �񤭹��ߥǡ�������Ȥ��֤�
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $data : �񤭹��ߥǡ���
    //////////////////////////////////////////////////////////////
    public function getData() {
        return $this->data;
    }

    //////////////////////////////////////////////////////////////
    // ����å�No���֤�
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : $thread_no : ����å�No
    //////////////////////////////////////////////////////////////
    public function getThreadNo() {
        return $this->thread_no;
    }

    //////////////////////////////////////////////////////////////
    // ���ꤵ�줿�ֹ�ν񤭹��ߤ��֤�
    //////////////////////////////////////////////////////////////
    // ����   : $no  : �񤭹��ߤ��ֹ�
    // ����� : $res : ���ꤵ�줿�񤭹��ߤΥǡ���
    //////////////////////////////////////////////////////////////
    public function getRes($no = NULL) {

        $flag_count = false;

        // �ֹ�λ��̵꤬��������������󥿤���������
        if ( $no == NULL ) {
            $no = $this->m_count;
            $flag_count = true;
        }
        
        // �����ֹ椬��Ǽ���Ƥ����ϰϳ��ʤ�NULL���֤�
        if ($no < 0 || $this->size <= $no ) {
            return NULL;
        }

        if ( $this->data[$no] == NULL ) {
            var_dump( $no );
            die("end");
        }

        // �����ֹ�ν񤭹��ߥǡ�������Ф�
        $res = array();
        list(
            $res['no'],
            $res['title'],
            $res['name'],
            $res['body'],
            $res['date'],
            $res['ip'],
            $res['id'],
            $res['show']
        ) = explode(MARK,trim($this->data[$no]));

        // ���������󥿤��ͤ���Ѥ��Ƥ���Х����󥿤�û�
        if ( $flag_count == true ) {
            $this->m_count++;
        }

        return $res;
    }
   
    //////////////////////////////////////////////////////////////
    // ���ꤵ�줿�ֹ�ν񤭹��ߤ��Խ�����
    //////////////////////////////////////////////////////////////
    // ���� : $res : ���ꤵ�줿�񤭹��ߤΥǡ���
    // ����   : $no  : �񤭹��ߤ��ֹ�
    //////////////////////////////////////////////////////////////
    public function editRes($res, $no = NULL) {
        // �ͤ����ʤ�NULL���֤�
        if ( $res == NULL || $no == NULL ) {
            return;
        }

		// ��¸�Ѥη����ˤޤȤ��
        $response  = $res["no"]         . MARK;
        $response .= $res["title"]      . MARK;
        $response .= $res["name"]       . MARK;
        $response .= $res["body"]       . MARK;
        $response .= $res["date"] . MARK;
        $response .= $res["ip"]       . MARK;
        $response .= $res["id"]         . MARK;
        $response .= $res["show"];

		// �ǡ������
        $this->data[$no] = $response;
        return true;
    }


    //////////////////////////////////////////////////////////////
    // �����񤭹��ߤ��ɲä���
    //////////////////////////////////////////////////////////////
    // ����   : $p_name : ̾��
    // ����   : $p_body : ��ʸ
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    public function addRes( $p_name, $p_body, $p_title = "" ) {

        $response = "";

        // ������(��������)
        $no         = $this->getSize() + 1;                     // �񤭹����ֹ�
        $title      = $p_title;                                 // ����åɥ����ȥ�
        $name       = ( $p_name != "" ) ? $p_name : NO_NAME;    // ̾��
        $body       = htmlspecialchars($p_body);                // ��ʸ
        $body       = str_replace("\r\n","<br />", $body);
        $body       = str_replace("\n","<br />",   $body);
        $write_date = date("Y-m-d H:i:s");                      // �񤭹�������
        $ip         = $_SERVER["REMOTE_ADDR"];                  // IP
        if ( isset($_SERVER["REMOTE_HOST"]) ) {                 // �ۥ���
            $host = $_SERVER["REMOTE_HOST"];
        } else {
            $host = gethostbyaddr($ip);
        }
        $id         = substr(crypt($ip,date("d")),2,10);        // ID
        $show       = FLAG_VISIBLE;                             // ɽ���ե饰
        // ������(�����ޤ�)

        // �ޤȤ��
        $response  = $no         . MARK;
        $response .= $title      . MARK;
        $response .= $name       . MARK;
        $response .= $body       . MARK;
        $response .= $write_date . MARK;
        $response .= $host       . MARK;
        $response .= $id         . MARK;
        $response .= $show;

        // �ɲ�
        $this->data[] = $response;
        $this->size++;
    }

    //////////////////////////////////////////////////////////////
    // �ǡ�������¸����
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    public function write() {

        // �񤭹��ߥǡ�����ޤȤ��
        $all = $this->thread_no;
        foreach( $this->data as $value ) {
            $all .= "\n" . trim($value);
        }

        // �񤭹��ߥ����񤭹���
        $fp = fopen($this->path,"w");
        flock($fp,LOCK_EX);
        fwrite($fp,$all);
        flock($fp,LOCK_UN);
        fclose($fp);
    }

    //////////////////////////////////////////////////////////////
    // ����åɥ����ȥ�����
    //////////////////////////////////////////////////////////////
    // ����   : ̵��
    // ����� : ̵��
    //////////////////////////////////////////////////////////////
    public function getTitle() {
        $res = $this->getRes(0);
        $title = $res['title'];
        return $title;
    }

}
?>