$(document).ready(function() {
	$('ul#my_quest article').each(function(i) { // Check each submenu:
		if ($.cookie('submenuMark-' + i)) {  // If index of submenu is marked in cookies:
			$(this).show().prev().removeClass('collapsed').addClass('expanded'); // Show it (add apropriate classes)
		}else {
			$(this).hide().prev().removeClass('expanded').addClass('collapsed'); // Hide it
		}
		$(this).prev().addClass('collapsible').click(function() { // Attach an event listener
			var this_i = $('ul#my_quest article').index($(this).next()); // The index of the submenu of the clicked link
			if ($(this).next().css('display') == 'none') {
				
				// When opening one submenu, we hide all same level submenus:
				$(this).parent('li').parent('ul').find('article').each(function(j) {
					if (j != this_i) {
						$(this).slideUp(200, function () {
							$(this).prev().removeClass('expanded').addClass('collapsed');
							cookieDel($('ul#my_quest article').index($(this)));
						});
					}
				});
				// :end
				
				$(this).next().slideDown(200, function () { // Show submenu:
					$(this).prev().removeClass('collapsed').addClass('expanded');
					cookieSet(this_i);
				});
			}else {
				$(this).next().slideUp(200, function () { // Hide submenu:
					$(this).prev().removeClass('expanded').addClass('collapsed');
					cookieDel(this_i);
					$(this).find('article').each(function() {
						$(this).hide(0, cookieDel($('ul#my_quest article').index($(this)))).prev().removeClass('expanded').addClass('collapsed');
					});
				});
			}
		return false; // Prohibit the browser to follow the link address
		});
	});
});
function cookieSet(index) {
	$.cookie('submenuMark-' + index, 'opened', {expires: null, path: '/'}); // Set mark to cookie (submenu is shown):
}
function cookieDel(index) {
	$.cookie('submenuMark-' + index, null, {expires: null, path: '/'}); // Delete mark from cookie (submenu is hidden):
}
