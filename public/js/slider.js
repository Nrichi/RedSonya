// JavaScript Document
$(function(){
				$('#images').exposure({controlsTarget : '#controls',
					controls : { prevNext : true, pageNumbers : true, firstLast : false },
					visiblePages : 2,
					pageSize : 6,
					slideshowControlsTarget : '#slideshow',
					onThumb : function(thumb) {
						var li = thumb.parents('li');				
						var fadeTo = li.hasClass('active') ? 1 : 1;
						
						thumb.css({display : 'none', opacity : fadeTo}).stop().fadeIn(200);
						
						thumb.hover(function() { 
							thumb.fadeTo('fast',1); 
						}, function() { 
							li.not('.active').children('img').fadeTo('fast', 1); 
						});
					},
					onImage : function(image, imageData, thumb) {
						// Check if wrapped is hovered.
						var hovered = $('.exposureWrapper').hasClass('exposureHover');
						
						// Fade out the previous image.
						$('.exposureWrapper > .exposureLastImage').stop().fadeOut(500, function() {
							$(this).remove();
						});
						
						// Fade in the current image.
						image.hide().stop().fadeIn(1000);
						
						var hasCaption = function() {
							return imageData.find('.caption').html().length > 0 || imageData.find('.extra').html().length > 0;
						}
						
						var showImageData = function() {
							imageData.stop().show().animate({bottom:0+'px'},{queue:false,duration:160});
						}
						var hoverOver = function() {
							$('.exposureWrapper').addClass('exposureHover');
							// Show image data as an overlay when image is hovered.
							var hasCpt = hasCaption();
							
							if (hasCpt) {
								showImageData.call();
							}
						};
						
						var hideImageData = function() {
							var imageDataBottom = -imageData.outerHeight();
							imageData.stop().show().animate({bottom:imageDataBottom+'px'},{queue:false,duration:160});
						}
						var hoverOut = function() { 
							$('.exposureWrapper').removeClass('exposureHover');
							// Hide image data on hover exit.
							if (hasCaption()) {
								hideImageData.call();
							}
						};
						
						$('.exposureWrapper').hover(hoverOver,hoverOut);
						imageData.hover(hoverOver,hoverOut);
												
						if (hovered) {
							if (hasCaption()) {
								showImageData.call();
							} else {
								hideImageData.call();	
							}	
						}
		
						if ($.exposure.showThumbs && thumb && thumb.length) {
							thumb.parents('li').siblings().children('img.selected').stop().fadeTo(200, 1);			
							thumb.fadeTo('fast', 1).addClass('selected');
						}
					}
				});
			});