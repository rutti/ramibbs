<input id="write_button" type="button" value="スレッド書き込みボックス　表　示" />
<form action="" method="post" enctype="applicaton/x-www-form-urlencoded" id="write_box">
        <table>
                <tr>
                        <th>お名前：</th>
                        <td><input type="text" name="name" value="" /></td>
                </tr>
                <tr>
                        <th>タイトル(<span class="red">必須</span>)：</th>
                        <td><input type="text" name="title" value="" /></td>
                </tr>
                <tr>
                        <th>本文(<span class="red">必須</span>)：</th>
                        <td><textarea name="body"></textarea></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>
                <tr>
                        <td id="submit_btn" colspan="2">
                                <input type="hidden" name="check" value="<!###CHECK_COOKIE###!>" />
                                <input type="hidden" name="mode"    value="new_thread_conf" />
                                <input type="submit" name="submit"  value="内容確認" />
                        </td>
                </tr>
        </table>
</form>
<br class="clear" />