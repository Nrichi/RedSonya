/*
* Exposure (http://http://exposure.blogocracy.org/)
* Copyright (c) 2010 Kristoffer Jelbring
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/
(function($) {	
/**
* @name Exposure
* @author Kristoffer Jelbring (kris@blogocracy.org)
* @version 0.6
*
* @type jQuery
* @cat plugins/Media
*
* @desc Turn a simple HTML list into a rich and smart photo viewer that handles very large amounts of photos.
*
* @example $('#images').exposure({options});
*
* @options
*	target:	(selector string) Where to insert the image being displayed. Defaults to '#exposure'. If no target is found, one will be created.
*	showThumbs:	(boolean) Display thumbnails or not. Defaults to true, but will be set to false if not thumbnails are found. 
*	showControls: (boolean) Display paging controls or not. Defaults to true, but will be set to false if missing controlsTarget.
*	controls: (array) Display only certain paging controls. All controls default to true. Usage example: controls : { prevNext : true, pageNumbers : true, firstLast : false }
*	enableSlideshow: (boolean) Enable slideshow. Defaults to true.
*	slideshowControlsTarget: (selector string) Where to insert the slideshow controls. Defaults to null.
*	autostartSlideshow: (boolean) Automatically start the slideshow when the gallery is loaded. Defaults to false.
*	slideshowDelay: (number) Delay for each slide in the slideshow (in milliseconds). Defauts to 3000.
*	showCaptions: (boolean) Display captions or not. Captions are added by setting a title attribute on the items in the list.
*	showExtraData: (boolean) Display extra image data or not. This data is added by inserting inner HTML to the items in the list.
*	dataTarget: (selector string) Where to insert captions and extra image data. Defaults to null, in which case the data container will appended to the main Exposure target.
*	controlsTarget: (selector string) Where to insert the paging controls. Defaults to null.
*	onThumb: (function) Callback function that is called when a thumbnail is displayed.
*	onImage: (function) Callback function that is called when an image is displayed. Defaults to removing the previous image.
*	onNext: (function) Callback function that is called when nextImage is called.
*	onPrev: (function) Callback function that is called when prevImage is called.
*	onPageChanged: (function) Callback function that is called when goToPage is called.
*	loop: (boolean) Start over when last image is reached.
*	onEndOfLoop: (function) Callback function that is called when the last image is reached and loop option is set to false.
*	pageSize: (number) Maximum number of images (thumbnails) per page. Defaults to 5.
*	visiblePages: (number) Maxium number of pages visible in paging.
*	preloadBuffer: (number) Maximum number of images to keep in load queue at any given time. Defaults to 3.
*	keyboardNavigation: (boolean) Enable keyboard navigation. Defaults to true.
*	clickingNavigation: (boolean) Enable browsing by clicking the image being shown. Defaults to true.
*	fixedTargetSize: (boolean) Enable a fixed size target element (set the size using CSS) instead of one that adapts to the size of the current image. Defaults to false.
*	showThumbToolTip: (boolean) Display captions as thumbnail tooltips or not. Defaults to true.
*	onEmpty: (function) Called when the gallery is empty. Defaults to removing controls and targets and to hiding the list element that the plugin is called on.
*	allowDuplicates: (boolean) Allow the same image to be added more than once. Defaults to true.
*/
var $$ = $.fn.exposure = function($args) {

	var $defaults = {
		target : '#exposure',
		showThumbs : true,
		showControls : true,
		controls : {
				prevNext : true,
				firstLast : true,
				pageNumbers : true				
		},
		enableSlideshow : true,
		slideshowControlsTarget : null,
		autostartSlideshow : false,
		slideshowDelay : 3000,
		showCaptions : true,
		showExtraData : true,
		dataTarget : null,
		controlsTarget : null,
		onThumb : function(thumb) {},
		onImage : function(image, imageData, thumb) {
			$('.exposureWrapper > .exposureLastImage').remove();
		},
		onNext : function() {},
		onPrev : function() {},
		onPageChanged : function() {},
		loop : true,
		onEndOfLoop : function() {},
		pageSize : 5,
		visiblePages : 5,
		preloadBuffer : 3,
		keyboardNavigation : true,
		clickingNavigation : true,
		fixedContainerSize : false,
		showThumbToolTip : true,
		onEmpty : function() {
			$('.exposureThumbs').hide();
			$($.exposure.target).remove();
			if ($.exposure.showControls) {
				$($.exposure.controlsTarget).remove();				
			}
			if ($.exposure.slideshowControlsTarget) {
				$($.exposure.slideshowControlsTarget).remove();
			}	
		},
		allowDuplicates : true
	};

	var opts = $.extend($defaults, $args);
	for (var i in opts) {
		// Only allow overriding of functions and properties defined in the defaults.
		if ($$.defined($defaults[i])) {
			$.exposure[i] = opts[i];
		}
	}
	
	if (!$($.exposure.target).length) {
		// The target element is missing so it needs to be created.
		$('<div id="exposure"></div>').insertBefore($(this));	
	}
	
	var wrapper = $('<div class="exposureWrapper"></div>');
	var target = $($.exposure.target).addClass('exposureTarget').append(wrapper);
	
	// Append image data (caption and additional data) container.
	var dataElements = $('<div class="caption"></div><div class="extra"></div>');
	if ($.exposure.dataTarget && $($.exposure.dataTarget).length) {
		$($.exposure.dataTarget).addClass('exposureData').append(dataElements);
	} else {
		$.exposure.dataTarget = null;
		target.append($('<div class="exposureData"></div>').append(dataElements));
	}
	
	// Don't show controls if there is no controls target or if all individual controls have been turned off.
	if (!$.exposure.controlsTarget || (!$.exposure.controls.prevNext && !$.exposure.controls.firstLast && !$.exposure.controls.pageNumbers)) {
		$.exposure.showControls = false;
	}
	
	// Render controls.
	if ($.exposure.showControls) {
		$($.exposure.controlsTarget).addClass('exposureControls').each(function() {
			if ($.exposure.controls.firstLast) { $(this).append($('<a class="exposureFirstPage" href="javascript:void(0);">' + $.exposure.texts.first + '</a>').click($.exposure.firstPage)); }
			if ($.exposure.controls.prevNext) { $(this).append($('<a class="exposurePrevPage" href="javascript:void(0);">' + $.exposure.texts.previous + '</a>').click($.exposure.prevPage)); }
			if ($.exposure.controls.pageNumbers) { $(this).append($('<div class="exposurePaging"></div>')); }
			if ($.exposure.controls.prevNext) { $(this).append($('<a class="exposureNextPage" href="javascript:void(0);">' + $.exposure.texts.next + '</a>').click($.exposure.nextPage)); }
			if ($.exposure.controls.firstLast) { $(this).append($('<a class="exposureLastPage" href="javascript:void(0);">' + $.exposure.texts.last + '</a>').click($.exposure.lastPage)); }
		});
	}
	
	// Only render slideshow controls if there is a slideshow controls target.
	if ($.exposure.slideshowControlsTarget) {
		$($.exposure.slideshowControlsTarget).addClass('exposureSlideshowControls').each(function() {
			$(this).append($('<a class="exposurePlaySlideshow" href="javascript:void(0);">' + $.exposure.texts.play + '</a>').click($.exposure.playSlideshow));
			$(this).append($('<a class="exposurePauseSlideshow" href="javascript:void(0);">' + $.exposure.texts.pause + '</a>').hide().click($.exposure.pauseSlideshow));
		});
	}
	
	// Bind keys for navigation (using Hotkeys Plugin).
	if ($.exposure.keyboardNavigation) {
		$(document).bind('keydown', 'left', $.exposure.prevImage);
		$(document).bind('keydown', 'right', $.exposure.nextImage);
		$(document).bind('keydown', 'ctrl+left', $.exposure.prevPage);
		$(document).bind('keydown', 'ctrl+right', $.exposure.nextPage);
		if ($.exposure.enableSlideshow) {
			$(document).bind('keydown', 'space', $.exposure.toggleSlideshow);
		}
	}
	
	// Return "this" to maintain chainability.
	return this.addClass('exposureThumbs').each(function() {
		
		var foundImage = false;
		var foundThumb = false;
		
		if ($(this).children('li').length) {	
			var selectedIndex = null;
				
			$(this).show().children('li').each(function() {
				foundImage = true;
				
				// The a tag contains all the needed information about the image.
				var a = $(this).find('a');
				if (a.length) {
					// Use only the first matching link.
					a = $(a[0]);
							
					var src = a.attr('href');
					var img = a.find('img');
					
					// Get caption and thumbnail source from either nested img tag or from rel attribute.
					var thumbSrc = img.length ? img.attr('src') : a.attr('rel');		
					var caption = img.length ? img.attr('title') : a.attr('title');
					
					var isSelected = a.hasClass('selected') && !selectedIndex;
										
					// Remove link and extract additional image data.
					a.remove();		
					var thumbData = $(this).html();
					
					if (thumbSrc) {
						foundThumb = true;
					}
					
					// All information extracted, remove original list entry.
					$(this).remove();
					
					// Add image to list of images.
					var imageIndex = $$.newImage(src, thumbSrc, caption, thumbData);
					
					if (imageIndex > -1) {
						if (isSelected) {
							selectedIndex = imageIndex;
						}
						
						if ($$.loadQueue.length < $.exposure.preloadBuffer) {
							// Preload buffer hasn't been filled yet, add image to load queue.				
							$$.addToLoadQueue(imageIndex);
						}
					}
				} else {
					// Just remove this empty entry.
					$(this).remove();
				}
			});
			
			if (!foundThumb) {
				// No thumbnails found, turn off thumbnails view.
				$.exposure.showThumbs = false;
			}
			
			if (!$.exposure.showThumbs) {
				// Thumbnails are turned off, change page size to 1.
				$.exposure.pageSize = 1;
				
				// Hide thumbnails container.
				$('.exposureThumbs').hide();
			}
			
			if (foundImage) {
				// Start preloading the first image.
				$$.preloadNextInQueue();
				
				$$.createPaging();
				
				if (selectedIndex) {
					$.exposure.goToPage($.exposure.pageNumberForImage(selectedIndex));
					$.exposure.viewImage(selectedIndex);
				} else {				
					// View the first page (and the first image).
					$.exposure.goToPage(1);
				}
				
				if ($.exposure.enableSlideshow && $.exposure.autostartSlideshow) {
					$.exposure.playSlideshow();
				}
			} else {
				$.exposure.onEmpty();	
			}
		} else {
			$.exposure.onEmpty();
		}
	});
};

// Private functions and properties. These are only for internal use.

/**
* Check if a variable is defined.
*
* @param v Variable to check.
*/
$$.defined = function(v) {
	return typeof v != 'undefined';
};

/**
* Value object representing an image in the viewer.
*
* @param src Source to the full size image.
* @param thumb Source to thumbnail version of the image.
* @param caption Image caption.
* @param data Extra image data.
*/
$$.Image = function(src, thumb, caption, data) {
	this.src = src;
	this.thumb = thumb;
	this.caption = caption;
	this.data = data;
	this.loaded = false;
};

/**
* All the images in the viewer. Holds an array of Image objects that are filled up when the plugin is loaded.
*/
$$.images = [];

/**
* All the image sources that's been previously added to the viewer.
*/
$$.sources = {};

/**
* Create a new Image object and add it to images array.
*
* @param src Source to the full size image.
* @param thumb Source to thumbnail version of the image.
* @param caption Image caption.
* @param data Extra image data.
* @returns Index of the new image.
*/
$$.newImage = function(src, thumb, caption, data) {
	var alreadyAdded = $$.defined($$.sources[src]);
	if (alreadyAdded && !$.exposure.allowDuplicates) {
		return -1;
	}
	var image = new $$.Image(src, thumb, caption, data);
	var imageIndex = $$.images.push(image) - 1;
	if (!alreadyAdded) {
		$$.sources[src] = imageIndex;
	}
	return imageIndex;
};
		
/**
* Index of the image currently being viewed.
*/
$$.current = 0;
		
/**
* The load queue, holds an array of indices of images to load.
*/
$$.loadQueue = [];
		
/**
* Add an image to the load queue.
*
* @param index Index of image to add.
*/
$$.addToLoadQueue = function(index) {
	if (!$$.loaded(index) && !$$.queued(index)) {
		$$.loadQueue.push(index);
	}
};
		
/**
* Check if a specific image exists in the load queue.
*
* @param index Index of image to check.
*/
$$.queued = function(index) {
	return $.inArray(index, $$.loadQueue) > -1;	
};
		
/**
* Check if a specific image has been loaded.
*
* @param index Index of image to check.
*/
$$.loaded = function(index) {
	var image = $.exposure.getImage(index);
	if (image !== null) {
		return image.loaded;
	}
	return false;
};
		
/**
* Find the next, not already loaded image, in the load queue. This function is recursive and will continue until
* an image is found, or until the queue is empty.
*/
$$.nextInLoadQueue = function() {
	if ($$.loadQueue.length > 0) {
		var next = $$.loadQueue.shift();
		if ($$.loaded(next)) {				
			// Image already loaded, remove from load queue.
			var i = $.inArray(index, $.exposure.loadQueue);
			$.exposure.loadQueue.splice(i, 1);
			
			// Find next in queue.
			return $$.nextInLoadQueue();
		}
		return next;
	}
	return null;
};
		
/**
* Preload the next image in the load queue.
*/	
$$.preloadNextInQueue = function() {
	if ($$.loadQueue.length > 0) {				
		var nextIndex = $$.nextInLoadQueue();
		if (nextIndex !== null) {
			$$.loadImage(nextIndex, $$.preloadNextInQueue);
		}
	}
};

/**
* Load a specific page.
*
* @param page Number of the page to load.
* @param backwards Set to true if browsing backwards.
*/
$$.loadPage = function(page, backwards) {
	if ($$.validPage(page)) {
		// Calculate first and last images on this page.
		var last = page * $.exposure.pageSize;
		var first = last - $.exposure.pageSize;
		
		if (last > $$.images.length) {
			last = $$.images.length;
		}

		// Go through images on page.
		for (var i = first; i < last; i++) {
			if ($.exposure.showThumbs) {
				var image = $$.images[i];
				// Find thumbnail container.
				var container = $.exposure.getThumb(i).parent();
				if (!container.length) {
					// Create a thumbnail if one doesn't already exist.
					container = $$.createThumbForImage(image, i);
					
					// Add page number as rel attribute.
					container.attr('rel', page);
					
					if (i == first) {
						// Decorate thumbnail container for first image on page.
						container.addClass('first');
					}
					if (i == last-1) {
						// Decorate thumbnail container for last image on page.
						container.addClass('last');
					}
				}
				if (container.length) {
					// Show thumbnail container.
					container.show();
				}
			}
		}			
		
		if (backwards) {
			// Moving backwards, set the last image on the page as active.
			$.exposure.viewImage(last-1);
		} else {
			// Set the first image on this page as active.			
			$.exposure.viewImage(first);
		}
	}
};
		
/**
* Load a specific image.
*
* @param index Index of image to load.
* @param onload Image onload callback function.
*/
$$.loadImage = function(index, onload) {
	var image = $.exposure.getImage(index);		
	var img = $('<img />').addClass('exposureImage');
	if (image !== null) {
		image.loaded = true;
		if ($$.queued(index)) {
			// Since image already has been loaded, remove it from the load queue.
			var i = $.inArray(index, $$.loadQueue);
			$$.loadQueue.splice(i, 1);
		}
		if (typeof onload == 'function') {
			img.load(onload);
		}
		img.attr('src', image.src);
	}
	return img;		
};

/**
* Create a thumbnail for a specific image.
*
* @param image Image object for the image.
* @param image Index of the image.
*/
$$.createThumbForImage = function(image, index) {
	if ($.exposure.showThumbs && image.thumb) {
		
		var thumb = $.exposure.getThumb(index);
		if (thumb === null || !thumb.length) {						
			// Create thumbnail container.
			var container = $('<li></li>');
			$('.exposureThumbs').append(container);
			
			// Create thumbnail img element.
			thumb = $('<img />').attr('src', image.thumb);
			container.append(thumb.css('display', 'block'));					
			
			// Add image index and caption as attributes.
			thumb.attr('rel', index);
			if (image.caption && $.exposure.showThumbToolTip) {
				thumb.attr('title', image.caption);
			}
			
			// Save extra image data in thumbnail data.
			thumb.data('data', image.data);
			
			thumb.click(function() {
				// When a thumbnail is clicked, view full version of that image.
				$.exposure.viewImage(index);
			});
			
			thumb.load(function() {
				// Set the height of the thumbnail container to the height of the thumbnail.
				var imageHeight = $(this).height();
				if (imageHeight > 0) {
					$(this).parent().height(imageHeight);
				}						
			});
			
			$.exposure.onThumb(thumb);
			
			return container;
		}
	}
	return null;
};

/**
* Number of the page currently being viewed.
*/
$$.currentPage = 1;
		
/**
* Check if a specific page number is a valid page number.
*
* @param page Page number to check.
*/
$$.validPage = function(page) {
	return page > 0 && page <= $.exposure.numberOfPages();
};

/**
* Create paging links.
*/
$$.createPaging = function() {	
	if ($.exposure.showControls && $.exposure.controls.pageNumbers) {	
		// Create paging links.
		for (var i = 1; i <= $.exposure.numberOfPages(); i++) {
			$('.exposurePaging').each(function() {
				$(this).append($$.newPagingLink(i));
			});
		}
	}	
};

/**
* Create a new paging link for a specific page.
*
* @param page Number of the page to create the link for.
*/
$$.newPagingLink = function(page) {
	return $('<a href="javascript:void(0);" rel="' + page + '">' + page + '</a>').click(function() { 
		// View the page defined in the rel attribute of the link.
		$.exposure.goToPage(Number($(this).attr('rel')));
	});
};
				
/**
* Slideshow playing state.
*/
$$.playingSlideshow = false;
		
/**
* Holds the timer for the slideshow.
*/
$$.slideshowTimer = null;
		
/**
* Slideshow transition state.
*/
$$.slideshowTransition = false;
		
/**
* Recursive function that runs nextImage() after given delay. Don't use this directly, use playSlideshow() instead.
*/		
$$.slideshow = function() {
	$$.slideshowTimer = setTimeout(function() { 
		$$.slideshowTransition = true;
		$.exposure.nextImage(); 
		$$.slideshowTransition = false;
		$$.slideshow(); 
	}, $.exposure.slideshowDelay);
};

// Extend with public functions. These can be called from your gallery using $.exposure.nameOfFunction().
$.extend({exposure : {
		/**
		* Calculate the page number of a specific image.
		*
		* @param index Index of image to get page number for.
		*/
		pageNumberForImage : function(index) {
			return Math.ceil((index + 1) / $.exposure.pageSize);
		},
		
		/**
		* Calculate the total number of pages using the set page size.
		*/
		numberOfPages : function() {
			// Calculate the page number for the last image.
			return $.exposure.pageNumberForImage($$.images.length-1);
		},
		
		/**
		* Check if the the page currently being viewed is the first page.
		*/
		atFirstPage : function() {
			return $$.currentPage == 1;
		},
		
		/**
		* Check if the the page currently being viewed is the last page.
		*/
		atLastPage : function() {
			return $$.currentPage == $.exposure.numberOfPages();
		},
		
		/**
		* Check if the image currently being viewed is the first image on its page.
		*/
		atFirstImageOnPage : function() {
			return $.exposure.pageSize == 1 || ($$.current % $.exposure.pageSize === 0);
		},
		
		/**
		* Check if the image currently being viewed is the last image on its page.
		*/
		atLastImageOnPage : function() {
			if ($.exposure.pageSize == 1) {
				return true;	
			}
			if ($$.current > 0 || $$.current.length == 1) {
				var currentPageSize = $.exposure.pageSize;
				if ($.exposure.atLastPage()) {
					// Calculate the size of the last page as it may differ from the set page size.
					var newPageSize = $$.images.length % $.exposure.pageSize;
					if (newPageSize > 0) {
						currentPageSize = newPageSize;
					}
				}
				
				var imageIndex = $$.current;
				if ($$.currentPage > 1) {
					imageIndex -= ($$.currentPage-1) * $.exposure.pageSize;
				}
				
				// Check if the current image is the last image of the current page.				
				return (imageIndex+1) % currentPageSize === 0;
			}
			return false;
		},
		
		/**
		* Get a spefic image object from the images array.
		*
		* @param index Index of image to get.
		*/
		getImage : function(index) {
			if (index !== null && index > -1 && index < $$.images.length) {
				return $$.images[index];
			}
			return null;
		},
		
		/**
		* Get the index of the image with the specified image source.
		*
		* @param src Source of the image to get index for.
		*/
		indexOfImage : function(src) {
			if (src && $$.defined($$.sources[src])) {
				return $$.sources[src];
			}
			return -1;
		},

		/**
		* Dynamically add an image to the gallery. 
		*
		* @param src Source to the full size image.
		* @param thumb Source to thumbnail version of the image.
		* @param caption Image caption.
		* @param data Extra image data.
		*/		
		addImage : function(src, thumb, caption, data) {
			var pageCount = $.exposure.numberOfPages();				
			var index = $$.newImage(src, thumb, caption, data);
			if (index > -1) {
				var pageNumber = $.exposure.pageNumberForImage(index);
				var containers = $('.exposureThumbs li[rel="'+ pageNumber + '"]');
				if (containers.length) {
					containers.removeClass('last');
				}
				
				// Recreate paging if a new page needs to be added.
				var newPageAdded = pageNumber > pageCount;
				if (newPageAdded) {
					// Make sure paging container is empty.
					$('.exposurePaging').empty();
					
					$$.createPaging();
				}
				
				if (newPageAdded || pageNumber == $$.currentPage) {
					// Reload the current page.
					$.exposure.goToPage($$.currentPage);	
				}
			}
		},
		
		/**
		* Removes all images from the gallery. Usable when dynamically rebuilding the gallery from scratch.
		*/
		removeAllImages : function() {
			$$.images = [];
			$$.sources = {};
			$$.loadQueue = [];
			if ($.exposure.enableSlideshow) {
				$.exposure.pauseSlideshow();	
			}
			$('.exposureThumbs').empty();
			$('.exposurePaging').empty();
			$.exposure.viewImage(0);
		},
		
		/**
		* Get the thumbnail img element for a specific image.
		*
		* @param index Index of image to find thumbnail for.
		*/
		getThumb : function(index) {
			return $('.exposureThumbs img[rel="'+index+'"]');
		},
		
		/**
		* Get the index of the next image.
		*/
		getNextImage : function() {
			if ($$.current == $$.images.length-1) {
				// Is at last image, return first image.
				if ($.exposure.loop) {
					return 0;
				} else {
					// Loop ended callback.
					$.exposure.onEndOfLoop();	
				}					
			} else {
				// Return next image.
				return $$.current+1;
			}
			return null;
		},
		
		/**
		* Get the index of the previous image.
		*/
		getPrevImage : function() {
			if ($$.current === 0) {
				// Is at first image, return last image.
				if ($.exposure.loop) {
					return $$.images.length-1;
				}
			} else {					
				// Return previous image. 
				return $$.current-1;
			}
			return null;
		},
	
		/**
		* View a specific page.
		*
		* @param page Number of the page to view.
		* @param backwards Set to true if browsing backwards.
		*/
		goToPage : function(page, backwards) {
			if ($$.validPage(page)) {
				// Hide all thumbnail containers.
				$('.exposureThumbs li').hide();
				
				$$.loadPage(page, backwards);
				
				if ($.exposure.showControls && $.exposure.controls.pageNumbers) {
					$('.exposurePaging span.active').each(function() { 
						$(this).replaceWith($$.newPagingLink($$.currentPage)); 
					});
					$('.exposurePaging a[rel="' + page + '"]').each(function() { 
						$(this).replaceWith($('<span>' + page + '</span>').addClass('active')); 
					});
					if ($.exposure.visiblePages > 0 && $.exposure.numberOfPages() > $.exposure.visiblePages) {
						var firstVisiblePage = page;						
						var lastVisiblePage = $.exposure.visiblePages;
						var flooredVisiblePages = Math.floor($.exposure.visiblePages/2);
						if (page <= flooredVisiblePages) {
							firstVisiblePage = 1;							
						} else if (page > ($.exposure.numberOfPages() - flooredVisiblePages)) {
							lastVisiblePage = $.exposure.numberOfPages();
							firstVisiblePage = lastVisiblePage - $.exposure.visiblePages + 1;
						} else { 
							firstVisiblePage -= flooredVisiblePages;
							lastVisiblePage = firstVisiblePage + $.exposure.visiblePages - 1;
						}
						$('.exposurePaging').each(function() {	
							$(this).children().each(function(i) {
								var currentPage = i+1;
								if (currentPage >= firstVisiblePage && currentPage <= lastVisiblePage) {
									$(this).show();
								} else {
									$(this).hide();
								}
							});
						});
					}
				}
				
				$$.currentPage = page;
				
				if ($.exposure.showControls) {			
					if ($.exposure.atFirstPage()) {
						// Disable first page button.
						if ($.exposure.controls.firstLast) {
							$('.exposureFirstPage').addClass('disabled');
						}
						
						// Hide previous page button.
						if (!$.exposure.loop && $.exposure.controls.prevNext) {
							$('.exposurePrevPage').hide();
						}
					} else {
						// Enable first page button.
						if ($.exposure.controls.firstLast) {
							$('.exposureFirstPage').removeClass('disabled');
						}
						
						// Show previous page button.
						if (!$.exposure.loop && $.exposure.controls.prevNext) {
							$('.exposurePrevPage').show();
						}
					}
					if ($.exposure.atLastPage()) {
						// Disable last page button.
						if ($.exposure.controls.firstLast) {
							$('.exposureLastPage').addClass('disabled');
						}
						
						// Hide next page button.
						if (!$.exposure.loop && $.exposure.controls.prevNext) {
							$('.exposureNextPage').hide();
						}
					} else {
						// Enable last page button.
						if ($.exposure.controls.firstLast) {
							$('.exposureLastPage').removeClass('disabled');
						}
						
						// Show next page button.
						if (!$.exposure.loop && $.exposure.controls.prevNext) {			
							$('.exposureNextPage').show();
						}
					}
				}
				
				// Page changed callback.
				$.exposure.onPageChanged();
			}
		},
		
		/**
		* View the first page.
		*/
		firstPage : function() {
			if (!$.exposure.atFirstPage()) {
				$.exposure.goToPage(1);
			}	
		},
		
		/**
		* View the last page.
		*/
		lastPage : function() {
			if (!$.exposure.atLastPage()) {
				$.exposure.goToPage($.exposure.numberOfPages());
			}	
		},
		
		/**
		* View the previous page.
		*/
		prevPage : function() {
			if (!$.exposure.atFirstPage()) {
				// Go to previous page.
				$.exposure.goToPage($$.currentPage-1);
			} else if ($.exposure.loop) {
				// At first page, go to last page.
				$.exposure.goToPage($.exposure.numberOfPages());
			}	
		},
		
		/**
		* View the next page.
		*/
		nextPage : function() {
			if (!$.exposure.atLastPage()) {
				// Go to next page.
				$.exposure.goToPage($$.currentPage+1);
			} else if ($.exposure.loop) {
				// At last page, go back to first page.
				$.exposure.goToPage(1);
			}	
		},
		
		/**
		* View a specific image.
		*
		* @param Index of image to view.
		*/
		viewImage : function(index) {
			if ($.exposure.enableSlideshow && !$$.slideshowTransition) {
				$.exposure.pauseSlideshow();
			}
			var wrapper = $('.exposureWrapper');
			var validImage = true;	
			var image = $$.images[index];
			if (image) {
				var src = image.src;
				var caption = image.caption;
				var extraImageData = image.data;
								
				if (src) {
					var hasThumb = $.exposure.showThumbs;
					var thumb = null;
					if ($.exposure.showThumbs) {
						thumb = $('.exposureThumbs img[rel="' + index + '"]');
						hasThumb = thumb && thumb.length;
						
						// Light up active thumbnail.
						if (hasThumb) {
							thumb.parent().siblings().removeClass('active');
							thumb.parent().addClass('active');
						} else {
							$('.exposureThumbs li.active').removeClass('active');
						}
					}
					
					// Show loading animation.
					wrapper.parent().removeClass('exposureLoaded');
					if ($$.loaded(index)) {
						// Hide loading animation if image already loaded.				
						wrapper.parent().addClass('exposureLoaded');
					}
				
					var img = $$.loadImage(index, function() {
						var lastImage = wrapper.find('.exposureImage');
						if (lastImage.length) {
							lastImage.addClass('exposureLastImage');
						}
						
						wrapper.append($(this));
						
						// Enable browsing by clicking on the image.
						if ($.exposure.clickingNavigation) {
							$(this).click($.exposure.nextImage);
						}
						
						// Resize target element to fit image.
						if (!$.exposure.fixedContainerSize) {
							$('.exposureTarget').width($(this).width()).height($(this).height());
						}
						
						// Add caption and additional image data.							
						var imageDataContainer = $.exposure.dataTarget ? $($.exposure.dataTarget) : wrapper.siblings('.exposureData');
						if (imageDataContainer.length) {
							if ($.exposure.showCaptions) {
								// Add caption to image data container.
								var captionContainer = imageDataContainer.find('.caption');
								if (captionContainer.length) {
									// Remove current caption from container.
									captionContainer.empty();
									if (!caption && hasThumb) {
										// Extract caption from thumbnail.
										caption	= thumb.attr('title');
									}
								}
								captionContainer.html(caption);
							}
							
							if ($.exposure.showExtraData) {
								// Add extra image data to image data container.
								var extraImageDataContainer = imageDataContainer.find('.extra');
								if (extraImageDataContainer.length) {
									// Remove current data from container.
									extraImageDataContainer.empty();
									if (!extraImageData && hasThumb) {
										// Extract data from thumbnail.
										extraImageData = thumb.data('data');
									}
									extraImageDataContainer.html(extraImageData);
								}
							}
						}
						
						// Image loaded callback.
						$.exposure.onImage($(this), imageDataContainer, thumb);

						// Preload next image.					
						$$.preloadNextInQueue();
					});
				} else {
					validImage = false;	
				}
			} else {
				validImage = false;	
			}
			if (!validImage) {
				wrapper.siblings().andSelf().empty();
				$('.exposureThumbs li.active').removeClass('active');	
			}
			$$.current = index;
		},
		
		/**
		* View next image.
		*/
		nextImage : function() {
			if ($.exposure.atLastImageOnPage()) {
				if ($.exposure.atLastPage()) {
					// At the last page, go back to first page.
					$.exposure.goToPage(1);
				} else {
					// Go to the next page.
					$.exposure.goToPage($$.currentPage+1);
				}
				// Next image callback.
				$.exposure.onNext();
			} else {
				var next = $.exposure.getNextImage();
				if (next !== null) {
					// Select next image.
					$.exposure.viewImage(next);
					// Next image callback.
					$.exposure.onNext();	
				}
			}
			var nextNext = $.exposure.getNextImage();
			if (nextNext !== null) {
				// Add second next image to load queue.
				$$.addToLoadQueue(nextNext);
			}
		},
		
		/**
		* View previous image.
		*/
		prevImage : function() {
			if ($.exposure.atFirstImageOnPage()) {
				if ($.exposure.atFirstPage()) {
					// At the first page, go to the last page.
					$.exposure.goToPage($.exposure.numberOfPages(), true);
				} else {
					// Go to the previous page.	
					$.exposure.goToPage($$.currentPage-1, true);
				}
				// Previous image callback.
				$.exposure.onPrev();
			} else {
				var prev = $.exposure.getPrevImage();
				if (prev !== null) {
					// Select next image.
					$.exposure.viewImage(prev);
					// Previous image callback.
					$.exposure.onPrev();
				}
			}
			var prevPrev = $.exposure.getPrevImage();
			if (prevPrev !== null) {
				// Add second previous image to load queue.
				$$.addToLoadQueue(prevPrev);
			}
		},
		
		/**
		* Play the slideshow.
		*/		
		playSlideshow : function() {
			if (!$$.playingSlideshow) {
				if ($.exposure.slideshowControlsTarget) {
					$('.exposurePlaySlideshow').hide();
					$('.exposurePauseSlideshow').show();
				}
				$$.slideshow();
				$$.playingSlideshow = true;		
			}			
		},
		
		/**
		* Pause the slideshow.
		*/
		pauseSlideshow : function() {
			if ($$.playingSlideshow) {
				if ($.exposure.slideshowControlsTarget) {
					$('.exposurePlaySlideshow').show();
					$('.exposurePauseSlideshow').hide();
				}
				$$.playingSlideshow = false;
				if ($$.slideshowTimer) {
					clearTimeout($$.slideshowTimer);
				}
			}
		},
		
		/**
		* Toggle (play/pause)
		*/
		toggleSlideshow : function() {
			if ($$.playingSlideshow) {
				$.exposure.pauseSlideshow();
			} else {
				$.exposure.playSlideshow();
			}
		},
		
		/**
		* Default texts.
		*/
		texts : {
			first : "First",
			previous : "Prev",
			next : "Next",
			last : "Last",
			play : "Play slideshow",
			pause : "Pause slideshow"
		}
	}			
});
})(jQuery);

/*
* jQuery Hotkeys Plugin
* Copyright 2010, John Resig
* Dual licensed under the MIT or GPL Version 2 licenses.
*
* Based upon the plugin by Tzury Bar Yochay:
* http://github.com/tzuryby/hotkeys
*
* Original idea by:
* Binny V A, http://www.openjs.com/scripts/events/keyboard_shortcuts/
*/
(function(jQuery){jQuery.hotkeys={version:"0.8",specialKeys:{8:"backspace",9:"tab",13:"return",16:"shift",17:"ctrl",18:"alt",19:"pause",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"insert",46:"del",96:"0",97:"1",98:"2",99:"3",100:"4",101:"5",102:"6",103:"7",104:"8",105:"9",106:"*",107:"+",109:"-",110:".",111:"/",112:"f1",113:"f2",114:"f3",115:"f4",116:"f5",117:"f6",118:"f7",119:"f8",120:"f9",121:"f10",122:"f11",123:"f12",144:"numlock",145:"scroll",191:"/",224:"meta"},shiftNums:{"`":"~","1":"!","2":"@","3":"#","4":"$","5":"%","6":"^","7":"&","8":"*","9":"(","0":")","-":"_","=":"+",";":": ","'":"\"",",":"<",".":">","/":"?","\\":"|"}};function keyHandler(handleObj){if(typeof handleObj.data!=="string"){return}var origHandler=handleObj.handler,keys=handleObj.data.toLowerCase().split(" ");handleObj.handler=function(event){if(this!==event.target&&(/textarea|select/i.test(event.target.nodeName)||event.target.type==="text")){return}var special=event.type!=="keypress"&&jQuery.hotkeys.specialKeys[event.which],character=String.fromCharCode(event.which).toLowerCase(),key,modif="",possible={};if(event.altKey&&special!=="alt"){modif+="alt+"}if(event.ctrlKey&&special!=="ctrl"){modif+="ctrl+"}if(event.metaKey&&!event.ctrlKey&&special!=="meta"){modif+="meta+"}if(event.shiftKey&&special!=="shift"){modif+="shift+"}if(special){possible[modif+special]=true}else{possible[modif+character]=true;possible[modif+jQuery.hotkeys.shiftNums[character]]=true;if(modif==="shift+"){possible[jQuery.hotkeys.shiftNums[character]]=true}}for(var i=0,l=keys.length;i<l;i++){if(possible[keys[i]]){return origHandler.apply(this,arguments)}}}}jQuery.each(["keydown","keyup","keypress"],function(){jQuery.event.special[this]={add:keyHandler}})})(jQuery);
