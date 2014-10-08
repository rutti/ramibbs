<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja">
<head>
<title><!###PAGE_TITLE###!></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="content-script-type" content="text/javascript">
<meta http-equiv="content-style-type" content="text/css">
<meta name="robots" content="index,follow">
<meta name="author" content="<!###AUTHOR###!>">
<meta name="keywords" content="<!###META_KEYWORD###!>">
<meta name="description" content="<!###META_DESC###!>">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0" />
<link href="./parts/css/base.css" type="text/css" rel="stylesheet">
<link href="./skin/<!###SKIN_TYPE###!>/bbs.css" type="text/css" rel="stylesheet">
<script src="http://www.google.com/jsapi"></script>
<script>
  google.load("jquery", "1.3.2");
</script>
<script type="text/javascript">
	$(function(){
		scriptCharset:"EUC-JP";
		$("#write_box").css("display","none");
		$("#write_button").click(function(){
			if ( $("#write_box").css("display") == "none" ) {
				$("#write_box").slideDown("normal");
				$("#write_button").attr("value","スレッド書き込みボックス　非表示");
			} else {
				$("#write_box").slideUp("fast");
				$("#write_button").attr("value","スレッド書き込みボックス　表　示");
			}
		});
	});
</script>
</head>
<body>
