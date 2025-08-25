(function($) {
    'use strict';

    var MediaMasonryHandler = function($scope, $) {
        var galleryElement = $scope.find('.media-masonry-gallery').get(0);
        
        if (!galleryElement) return;

        var isEditor = Boolean(window.elementor && window.elementor.isEditMode && window.elementor.isEditMode());
        var masonry = null;

        var breakpoints = {
            mobile: 767,
            tablet: 1024
        };

        function getCurrentBreakpoint() {
            var width = window.innerWidth;
            if (width <= breakpoints.mobile) return 'mobile';
            if (width <= breakpoints.tablet) return 'tablet';
            return 'desktop';
        }

        function updateItemWidths() {
            var currentBreakpoint = getCurrentBreakpoint();
            var items = galleryElement.querySelectorAll('.gallery-item');
            
            var widthDesktop = galleryElement.getAttribute('data-item-width') || '300';
            var widthTablet = galleryElement.getAttribute('data-item-width-tablet') || '250';
            var widthMobile = galleryElement.getAttribute('data-item-width-mobile') || '100';
            
            var currentWidth;
            switch(currentBreakpoint) {
                case 'mobile':
                    currentWidth = widthMobile;
                    break;
                case 'tablet':
                    currentWidth = widthTablet;
                    break;
                default:
                    currentWidth = widthDesktop;
            }
            
            items.forEach(function(item) {
                if (!item.style.width || isEditor) {
                    var unit = currentWidth.includes('%') ? '%' : 'px';
                    if (!currentWidth.includes('%') && !currentWidth.includes('px')) {
                        currentWidth += 'px';
                    }
                    item.style.width = currentWidth + ' !important';
                }
            });
        }

        function initMasonry() {
            try {
                if (masonry) {
                    masonry.destroy();
                }

                updateItemWidths();

                masonry = new Masonry(galleryElement, {
                    itemSelector: '.gallery-item',
                    columnWidth: '.gallery-item',
                    gutter: 0,
                    percentPosition: false, 
                    transitionDuration: isEditor ? 0 : '0.3s'
                });

                setTimeout(function() {
                    if (masonry) {
                        masonry.layout();
                        updateContainerHeight();
                    }
                }, 100);

            } catch (e) {
                console.error('Error initializing Masonry:', e);
            }
        }

        function updateContainerHeight() {
            if (!galleryElement || !masonry) return;

            var items = galleryElement.querySelectorAll('.gallery-item');
            var maxBottom = 0;

            items.forEach(function(item) {
                var rect = item.getBoundingClientRect();
                var containerRect = galleryElement.getBoundingClientRect();
                var bottomPosition = rect.bottom - containerRect.top;
                if (bottomPosition > maxBottom) {
                    maxBottom = bottomPosition;
                }
            });

            if (maxBottom > 0) {
                galleryElement.style.minHeight = Math.ceil(maxBottom) + 'px';
            }

            if (isEditor && window.elementor) {
                setTimeout(function() {
                    window.elementor.trigger('refresh:ui');
                }, 50);
            }
        }

        function handleImagesLoaded() {
            if (typeof imagesLoaded === 'function') {
                imagesLoaded(galleryElement).on('progress', function() {
                    if (masonry) {
                        masonry.layout();
                        setTimeout(updateContainerHeight, 50);
                    }
                }).on('done', function() {
                    if (masonry) {
                        masonry.layout();
                        setTimeout(updateContainerHeight, 100);
                    }
                });
            } else {
                console.warn('imagesLoaded not available. Layout may not be perfect.');
                setTimeout(function() {
                    if (masonry) {
                        masonry.layout();
                        updateContainerHeight();
                    }
                }, 300);
            }
        }

        initMasonry();
        handleImagesLoaded();

        var resizeTimeout;
        $(window).on('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                var previousBreakpoint = getCurrentBreakpoint();
                
                updateItemWidths();
                
                if (masonry) {
                    masonry.reloadItems(); 
                    masonry.layout();
                    setTimeout(updateContainerHeight, 50);
                }
            }, 250);
        });

        if (isEditor) {
            var observer = new MutationObserver(function(mutations) {
                var shouldUpdate = false;
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || 
                        (mutation.type === 'attributes' && 
                         ['src', 'style', 'class', 'data-item-width'].includes(mutation.attributeName))) {
                        shouldUpdate = true;
                    }
                });

                if (shouldUpdate && masonry) {
                    setTimeout(function() {
                        updateItemWidths();
                        masonry.reloadItems();
                        masonry.layout();
                        setTimeout(updateContainerHeight, 100);
                    }, 100);
                }
            });

            observer.observe(galleryElement, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['src', 'style', 'class', 'data-item-width', 'data-item-width-tablet', 'data-item-width-mobile']
            });

            setTimeout(function() {
                if (masonry) {
                    updateItemWidths();
                    masonry.layout();
                    updateContainerHeight();
                }
            }, 500);

            if (window.elementor && window.elementor.channels) {
                window.elementor.channels.editor.on('change', function() {
                    setTimeout(function() {
                        if (masonry) {
                            updateItemWidths();
                            masonry.layout();
                            updateContainerHeight();
                        }
                    }, 300);
                });
            }
        }

        $scope.on('click', '.wpmme-lightbox', function(e) {
            console.log('Lightbox click handler triggered.');
            e.preventDefault();
            var $this = $(this);
            var imageUrl;
            var mediaType = $this.data('media-type');

            if ($this.is('a')) { 
                imageUrl = $this.attr('href');
            } else if ($this.is('span')) { 
                imageUrl = $this.data('href');
            }

            console.log('imageUrl:', imageUrl, 'mediaType:', mediaType);
            if (!imageUrl) return;

            var $overlay = $('<div class="wpmme-lightbox-overlay"></div>');
            var $content = $('<div class="wpmme-lightbox-content"></div>');
            var $mediaElement; 

            if (mediaType === 'image') {
                $mediaElement = $('<img src="' + imageUrl + '" alt="Lightbox Image">');
            } else if (mediaType === 'video') {
                $mediaElement = $('<video controls preload="metadata"><source src="' + imageUrl + '"></video>');
            } else {
                console.warn('Unknown media type for lightbox:', mediaType);
                return;
            }

            var $closeBtn = $('<span class="wpmme-lightbox-close">&times;</span>');

            $content.append($mediaElement).append($closeBtn); 
            $overlay.append($content);
            $('body').append($overlay);

            $overlay.addClass('active');

            $overlay.on('click', function(event) {
                if ($(event.target).is($overlay) || $(event.target).is($closeBtn)) {
                    $overlay.removeClass('active');
                    setTimeout(function() {
                        $overlay.remove();
                    }, 300); 
                }
            });

            $(document).on('keydown.wpmmeLightbox', function(e) {
                if (e.keyCode === 27) { 
                    $overlay.removeClass('active');
                    setTimeout(function() {
                        $overlay.remove();
                    }, 300); 
                    $(document).off('keydown.wpmmeLightbox');
                }
            });
        });

        if (isEditor && window.console) {
            window.masonryDebug = {
                gallery: galleryElement,
                masonry: masonry,
                updateWidths: updateItemWidths,
                updateHeight: updateContainerHeight,
                getCurrentBreakpoint: getCurrentBreakpoint
            };
        }
    };

    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/media-masonry-gallery.default', MediaMasonryHandler);
    });

    $(window).on('elementor/frontend/init', function() {
        if (window.elementor && window.elementor.isEditMode && window.elementor.isEditMode()) {
            elementorFrontend.hooks.addAction('frontend/element_ready/media-masonry-gallery.default', MediaMasonryHandler);
        }
    });

})(jQuery);