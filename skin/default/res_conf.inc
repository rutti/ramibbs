<script type="text/javascript">
	$(function(){
		$("#write_box").css("display","block");
	});
</script>
<form action="" method="post" enctype="applicaton/x-www-form-urlencoded" id="write_box">
    <table>
        <tr>
            <th class="title" colspan="2">��<!###THREAD_TITLE###!>�פؤν񤭹���</th>
        </tr>
        <tr>
            <th>��̾����</th><td class="show"><!###RES_NAME###!></span></td>
        </tr>
        <tr>
            <th>��ʸ��</th><td class="show"><!###RES_SHOW_BODY###!></span></td>
        </tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
            <td id="submit_btn" colspan="2">
				�嵭�����ƤǤ��������Сֽ񤭹���פ򡢽������������ϡֽ����פ򲡤��Ƥ���������<br />
				<input type="hidden" name="res_name"  value="<!###RES_NAME###!>" />
				<input type="hidden" name="res_body"  value="<!###RES_BODY###!>" />
				<input type="hidden" name="check"     value="<!###CHECK_COOKIE###!>" />
				<input type="hidden" name="thread_no" value="<!###THREAD_NO###!>" />
                <input type="hidden" name="mode"      value="new_res_write" />
                <input type="submit" name="submit"    value="�񤭹���" />
                <input type="button" value="����"     onClick="history.back(); return false;" />
            </td>
        </tr>
    </table>
</form>