<?php
/**
 * Luxeritas WordPress Theme - free/libre wordpress platform
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * @copyright Copyright (C) 2015 Thought is free.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 * @author LunaNuko
 * @link https://thk.kanzae.net/
 * @translators rakeem( http://rakeem.jp/ )
 */

global $_is;

if( isset( $_GET['url'] ) && stripos( $_GET['url'], THK_HOME_URL ) !== false ) {
	$permalink = $_GET['url'];
}
if( !isset( $permalink ) ) $permalink = THK_HOME_URL;

if( $_is['preview'] === true ) {
	if( stripos( $permalink, '?' ) === false  ) {
		$permalink .= '?preview=true';
	}
	else {
		$permalink .= '&preview=true';
	}
}

if( stripos( THK_HOME_URL, '?' ) === false  ) {
	$concat1 = '?';
}
else {
	$concat1 = '&';
}

if( stripos( $permalink, '?' ) === false  ) {
	$concat2 = '?';
}
else {
	$concat2 = '&';
}

$enc_url = rawurlencode( $permalink );

if( isset( $_GET['device'] ) && $_GET['device'] === 'tablet' ) {
	$width = isset( $_GET['rotate'] ) ? '1024px' : '768px';
	$height = isset( $_GET['rotate'] ) ? '768px' : '1024px';
}
else {
	$width = isset( $_GET['rotate'] ) ? '667px' : '375px';
	$height = isset( $_GET['rotate'] ) ? '375px' : '667px';
}

$rotate_icon = isset( $_GET['rotate'] ) ? '\f166' : '\f167';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
<link rel="stylesheet" id="dashicons-css" href="<?php echo includes_url() ?>css/dashicons.min.css" media="screen" />
<style>
html, body {
	height: 100%;
	margin: 0;
	padding: 0;
}
body {
	font-size: 16px;
	padding: 10px;
	background: #fff;
}
a {
	text-decoration: none;
}
#wrap {
	position: absolute;
	overflow: visible;
	top: 10px;
	bottom: 0;
	left: 0;
	right: 0;
	height: 100%;
	min-width: 0;
	transition: all .5s ease-out;
	transform-origin: top center;
}
#luxe-adminbar {
	position: fixed;
	bottom: 20px;
	left: 10px;
	padding: 10px;
	background:rgba(0,0,0,.6);
}
#around {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;

	transition: all .2s;
	margin: auto;
	box-sizing: content-box;
	width: <?php echo $width ?>;
	height: <?php echo $height ?>;
	min-width: <?php echo $width ?>;
	min-height: <?php echo $height ?>;
	max-height: 100%;
	max-width: 100%;

	padding: <?php echo isset( $_GET['rotate'] ) ? '20px 40px' : '40px 20px' ?>;
	background: #333;
	border: 5px solid #666;
	border-radius: 25px;
}
iframe {
	box-sizing: border-box;
	width: 100%;
	height: 100%;
	border-top: 2px solid #ccc;
	border-left: 2px solid #ccc;
	border-bottom: 1px solid #fff;
	border-right: 1px solid #fff;
}
#ladmin {
	line-height: 0;
	border-radius: 4px;
}
#ladmin * {
	margin: 0;
	padding: 0;
}
#ladmin,
#ladmin-b a {
	vertical-align: middle;
	color: #fff;
}
#ladmin-b span, #mag100 {
	color: #aaa;
}
#ladmin-b a:hover {
	color: #4cb5e8;
}
#ladmin ul {
	display: table;
	position: fixed;
	left: 10px;
	padding: 15px;
	border-radius: 4px;
	color: #fff;
	background: rgba(0,0,0,.65);
}
#ladmin-b {
	bottom: 20px;
}
#ladmin-p {
	bottom: 80px;
	/*pointer-events: none;*/
}
#ladmin-p span {
	padding: 6px;
	vertical-align: middle;
	font-size: 13px;
	cursor: pointer;
}
#ladmin-p span:first-child {
	padding-left: 0;
}
#ladmin-p span:last-child {
	padding-right: 0;
}
#ladmin li {
	display: table-cell;
	padding: 0 6px;
}
#ladmin i::before {
	display: inline-block;
	-webkit-font-smoothing: antialiased;
	font: normal 20px/20px dashicons;
	vertical-align: middle;
}
#ladmin-b i.preview-desktop::before {
	content: "\f472";
}
#ladmin-b i.preview-tablet::before {
	content: "\f471";
}
#ladmin-b i.preview-mobile::before {
	content: "\f470";
}
#ladmin-b i.rotate::before {
	content: "<?php echo $rotate_icon ?>";
	<?php echo isset( $_GET['rotate'] ) ? "transform: rotate(90deg);\n" : '' ?>
}
#wrap.mag50 { transform: scale(.5, .5); }
#wrap.mag75 { transform: scale(.75, .75); }
#wrap.mag100 { transform: scale(1, 1) }
#wrap.mag125 { transform: scale(1.25, 1.25) }
#wrap.mag150 { transform: scale(1.5, 1.5) }
</style>
</head>
<body>
<div id="wrap">
<div id="around">
<<?php echo 'i', 'frame' ?> id="if" src="<?php echo $permalink, $concat2; ?>respond_frame=1" onmousewheel sandbox="allow-modals allow-orientation-lock allow-pointer-lock allow-presentation allow-same-origin allow-scripts"></<?php echo 'i', 'frame' ?>>
</div>
</div>
<div id="ladmin">
<ul id="ladmin-p">
<li>
<span id="mag50"<?php if( isset( $_GET['device'] ) && $_GET['device'] === 'mobile' ) echo ' style="display:none"' ?>>50%</span>
<span id="mag75">75%</span>
<span id="mag100">100%</span>
<span id="mag125">125%</span>
<span id="mag150"<?php if( isset( $_GET['device'] ) && $_GET['device'] === 'tablet' ) echo ' style="display:none"' ?>>150%</span>
</li>
</ul>
<ul id="ladmin-b">
<li><a id="desktop" href="<?php echo $permalink ?>" title="PC"><i class="preview-desktop"></i></a></li>
<?php
if( isset( $_GET['device'] ) && $_GET['device'] === 'tablet' ) {
?>
<li><span title="<?php echo __( 'Tablet', 'luxeritas' ) ?>"><i class="preview-tablet"></i></span></li>
<?php
}
else {
?>
<li><a id="tablet" href="<?php echo THK_HOME_URL, $concat1 ?>respond_preview=1&device=tablet&url=<?php echo $enc_url ?>" title="<?php echo __( 'Tablet', 'luxeritas' ) ?>"><i class="preview-tablet"></i></a></li>
<?php
}
?>
<?php
if( isset( $_GET['device'] ) && $_GET['device'] === 'mobile' ) {
?>
<li><span title="<?php echo __( 'Mobile', 'luxeritas' ) ?>"><i class="preview-mobile"></i></span></li>
<?php
}
else {
?>
<li><a id="mobile" href="<?php echo THK_HOME_URL, $concat1 ?>respond_preview=1&device=mobile&url=<?php echo $enc_url ?>" title="<?php echo __( 'Mobile', 'luxeritas' ) ?>"><i class="preview-mobile"></i></a></li>
<?php
}

if( isset( $_GET['rotate'] ) ) {
	$request = str_replace( '&rotate=1', '', $_SERVER["REQUEST_URI"] );
}
else {
	$request = $_SERVER["REQUEST_URI"] . '&rotate=1';
}
?>
<li><a id="rotate" href="<?php echo THK_HOME_URL . $request ?>" title="<?php echo __( 'Rotate', 'luxeritas' ) ?>"><i class="rotate"></i></a></li>
</ul>
</div>
</div>
<script>
!function() {
	var c = document
	,   p = "<?php echo THK_HOME_URL ?>"
	,   m = "<?php echo $permalink ?>"
	,   d = "<?php echo $concat1 ?>respond_preview=1&device="
	,   s = c.getElementById("if")
	,   e = c.getElementsByTagName("div")
	,   u = c.getElementsByTagName("head")[0]
	,   a = c.getElementById("wrap")
	,   m1 = c.getElementById("mag50")
	,   m2 = c.getElementById("mag75")
	,   m3 = c.getElementById("mag100")
	,   m4 = c.getElementById("mag125")
	,   m5 = c.getElementById("mag150")
	,   s1 = m1.style
	,   s2 = m2.style
	,   s3 = m3.style
	,   s4 = m4.style
	,   s5 = m5.style
	,   w = function(e) {
		a.classList.remove("mag50");
		a.classList.remove("mag75");
		a.classList.remove("mag100");
		a.classList.remove("mag125");
		a.classList.remove("mag150");
		a.classList.add(e);
	};

	setInterval(function() {
		s.removeAttribute("style");
		Array.prototype.forEach.call(e, function(e) {
			e.removeAttribute("style")
		})
	}, 1);

	if( m1 !== null ) m1.onclick = function() {
		w("mag50");
		s1.color = "#aaa", s2.color = "#fff", s3.color = "#fff", s4.color = "#fff", s5.color = "#fff";
	}
	if( m2 !== null ) m2.onclick = function() {
		w("mag75");
		s1.color = "#fff", s2.color = "#aaa", s3.color = "#fff", s4.color = "#fff", s5.color = "#fff";
	}
	if( m3 !== null ) m3.onclick = function() {
		w("mag100");
		s1.color = "#fff", s2.color = "#fff", s3.color = "#aaa", s4.color = "#fff", s5.color = "#fff";
	}
	if( m4 !== null ) m4.onclick = function() {
		w("mag125");
		s1.color = "#fff", s2.color = "#fff", s3.color = "#fff", s4.color = "#aaa", s5.color = "#fff";
	}
	if( m5 !== null ) m5.onclick = function() {
		w("mag150");
		s1.color = "#fff", s2.color = "#fff", s3.color = "#fff", s4.color = "#fff", s5.color = "#aaa";
	}

	setInterval(function() {
		var e = s.contentWindow.document.location.href;
		if (m !== e && -1 != e.indexOf(p)) {
			var t = (m = e).replace("?respond_frame=1", "").replace("&respond_frame=1", "")
			,   r = encodeURIComponent(t)
			,   n = c.getElementById("desktop")
			,   o = c.getElementById("tablet")
			,   l = c.getElementById("mobile")
			,   a = c.getElementById("rotate");
			null !== n && n.setAttribute("href", t);
			null !== o && o.setAttribute("href", p + d + "tablet&url=" + r);
			null !== l && l.setAttribute("href", p + d + "mobile&url=" + r);
			null !== a && a.setAttribute("href", p + d + "<?php echo isset( $_GET['device'] ) ? $_GET['device'] : 'mobile' ?>&url=" + r + "<?php echo isset( $_GET['rotate'] ) ? '' : '&rotate=1' ?>")
		}
		var i = u.getElementsByTagName("script");
		Array.prototype.forEach.call(i, function(e) {
			e.parentNode.removeChild(e)
		})
	}, 1000)
}();
</script>
</body>
</html>
