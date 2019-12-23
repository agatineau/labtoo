;(function($, window, document, undefined) {
	var $win = $(window);
	var $doc = $(document);

	$doc.ready(function() {
		$('.dropdown-default > a').on('click', function(event) {
			event.preventDefault();

			var $this = $(this);

			$this.addClass('active')
				.siblings('a').removeClass('active');

			$this
				.parent()
					.find('ul').toggleClass('current');

			setTimeout(function () {
				$this.closest('.dropdown').addClass('open');
			}, 5)
		});

		$('.slider').flipster({
			itemContainer: 	'.slides',
			itemSelector: 	'.slide',
			style: 			'carousel',
			spacing: 		-0.6,
			nav: 			true,
			buttons: 		true,
			scrollwheel: 	false
		});

        // Tabs Nav Expand Nav For Phone
        $('.nav-tabs a').on('click', function () {
            var text = $(this).text();
            $(this)
                .closest('.nav-tabs')
                .prev()
                .find('span')
                .text(text);

            $(this)
                .closest('.tabset-holder')
                .removeClass('active');

            setTimeout(function () {
                jcf.refreshAll()
            }, 10);
        });

		/*$('.nav-tabs a').on('click', function(event) {
		 event.preventDefault();

		 var text = $(this).text();

		 $(this).closest('.tabset-holder').removeClass('active').find('> .opener > span').text(text);
		 });*/

        $('.waypoint').on('click', function(event) {
            event.preventDefault();

            var element = $(this).attr('href');

            if($(element).length) {
                $('html, body').animate({
                    scrollTop: $(element).offset().top
                }, 1000);
            }
        });

		$win
			.on('load', function () {
				$('.boxes-holder .box-inner').equalizeHeight();
				$('.profile .eq-height').equalizeHeight();
				$('.twocolumns .eq-height').equalizeHeight();
			})
			.on('resize', function () {
				$('.boxes-holder .box-inner').equalizeHeight();
				$('.profile .eq-height').equalizeHeight();

				setTimeout(function () {
					$('.twocolumns .eq-height').equalizeHeight();
				}, 50);
			});

		$win.on('scroll load', function () {
			if($('.maparea-large').length) {
				var mapAreaOffsetTop = $('.maparea-large').offset().top;
			}

			if($('.listing-area').length) {
				var listingHolderOffsetTop = $('.listing-area').offset().top;
				var listingHolderH = $('.listing-area').height();
			}

			var scroll = $(this).scrollTop();

			if (scroll > mapAreaOffsetTop) {
				$('.maparea-large').addClass('fixed');
			} else {
				$('.maparea-large').removeClass('fixed');
			}

			if($win.scrollTop() + $win.height() >= listingHolderOffsetTop + listingHolderH) {
				$('.maparea-large').addClass('at-bottom');
				setTimeout(function (){
					mapEvent.trigger(map, 'resize');
				}, 50);
			} else {
				$('.maparea-large').removeClass('at-bottom');
			}
		});
	});

	$.fn.hasAttr = function(name) {
	   return this.attr(name) !== undefined;
	};

	$.fn.equalizeHeight = function() {
		var maxHeight = 0, itemHeight;

		for (var i = 0; i < this.length; i++) {
			itemHeight = $(this[i]).height();
			//console.log("itemHeight : " + itemHeight);
			if (maxHeight < itemHeight) {
				maxHeight = itemHeight;
			}
		}

		return this.height(maxHeight);
	}

})(jQuery, window, document);
