function initHTMLSize() {
	var wWidth = document.documentElement.clientWidth || document.body.clientWidth;
	var size = wWidth / 7.5;
	document.getElementsByTagName('html')[0].style.fontSize = (size > 50 ? 50 : size) + 'px';
}
$(document).ready(function () {
	initHTMLSize();
});
$(window).resize(function() {
	initHTMLSize()
});