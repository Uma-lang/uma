<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>ホーム</title>
	<link rel="stylesheet" href="css/style.css">
	<script src="js/openclose.js"></script>
	<script src="js/fixmenu_pagetop.js"></script>
</head>

<body>

	<div id="container">
		<header>
			<h1 id="logo"><a href="index.php"><img src="images/logo.png" alt="Obaradai Hotel"></a></h1>
			<p id="tel">ご予約・お問い合わせ<br>
				<span class="big1">電話番号: 046-841-3810(代表)</span><br>
				<span class="mini1">24時間 365日営業</span>
			</p>
		</header>

		<!--PC用メニュー-->
		<nav id="menubar">
			<ul>
				<li><a href="index.php">Home<span>ホーム</span></a></li>
				<li><a href="info.html">Information<span>ホテルのご案内</span></a></li>
				<li class="current"><a href="plan.html">Plan<span>ご宿泊プラン</span></a></li>
				<li><a href="access.html">Access<span>アクセス・周辺環境</span></a></li>
				<li><a href="reservation.html">Reservation<span>ご予約</span></a></li>
				<li><a href="contact.html">Contact<span>お問合せ</span></a></li>
			</ul>
		</nav>

		<!--小さな端末用メニュー-->
		<nav id="menubar-s">
			<ul>
				<li><a href="index.php">Home<span>ホーム</span></a></li>
				<li><a href="info.html">Information<span>ホテルのご案内</span></a></li>
				<li class="current"><a href="plan.html">Plan<span>ご宿泊プラン</span></a></li>
				<li><a href="access.html">Access<span>アクセス・周辺環境</span></a></li>
				<li><a href="reservation.html">Reservation<span>ご予約</span></a></li>
				<li><a href="contact.html">Contact<span>お問合せ</span></a></li>
			</ul>
		</nav>

		<div id="contents">
			<div id="main">
				<article>
					<h2 class="mb30">ご宿泊プラン</h2>
					<ul class="mark">
						<li class="mark3">カップル向け</li>
					</ul>


					<table class="ta1">
						<caption>乾燥室</caption>
						<tr>
							<th>料金</th>
							<td>¥1,000 / 泊</td>
						</tr>
						<tr>
							<th>収容人数</th>
							<td>10人</td>
						</tr>
					</table>
                    <h2>ご予約状況</h2>
                    <?php include 'api/calendar_kansou.php'; ?>
				</article>
				<p><a href="javascript:history.back()">&lt;&lt; 前のページに戻る</a></p>
			</div>
		</div>
	</div>
	<footer class="footer">
		<div class="footer-top">
			<div class="footer-logo">
				<img src="images/logo2.png" alt="ロゴ">
				<div class="footer-logo-text">
					小原台ホテル<br>
					<span>Obaradai Hotel</span>
				</div>
			</div>
			<div class="footer-contact">
				〒239-8686 神奈川県横須賀市走水1丁目10番20号<br>
				TEL: 046-841-3810(代表)
			</div>
		</div>
		<div class="footer-links">
			<a href="#">サイトマップ</a> |
			<a href="#">利用規約</a> |
			<a href="#">プライバシーポリシー</a> |
			<a href="#">関連リンク</a>
		</div>
		<div class="footer-bottom">
			&copy; <a href="index.php">Obaradai Hotel | 小原台ホテル</a>. All Rights Reserved.
		</div>
	</footer>
	<p class="nav-fix-pos-pagetop"><a href="#">↑</a></p>
	<div id="menubar_hdr" class="close"></div>
	<script>
		if (OCwindowWidth() <= 800) {
			open_close("menubar_hdr", "menubar-s");
		}
	</script>
</body>

</html>
