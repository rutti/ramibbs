        <div class="write_res_whole">
                <div class="write_res_box">
                        <form action="" method="post" enctype="application/x-www-form-urlencoded" >
                                <div class="write_res_menu">名前：</div>
                                <input type="text" name="res_name" class="in_name" />
                                <div class="write_res_menu">書き込み本文(<span class="red">必須</span>)：</div>
                                <textarea name="res_body"></textarea>
                                <input type="hidden" name="check"        value="<!###CHECK_COOKIE###!>" />
                                <input type="hidden" name="thread_title" value="<!###BOARD_TITLE###!>" />
                                <input type="hidden" name="thread_no"    value="<!###BOARD_NO###!>" />
                                <input type="hidden" name="mode"         value="new_res_conf" />
                                <input type="submit" name="submit"       value="書き込み内容確認" class="in_submit" />
                        </form>
                </div>
        </div>
