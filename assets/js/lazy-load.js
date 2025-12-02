/**
 * Lazy Loading for Images
 * Uses Intersection Observer API for efficient image loading
 */

// Function to load a lazy image with fade-in effect
function loadLazyImage(img) {
    // Check if image has data-src and isn't already loaded
    if (!img.dataset.src) {
        return false;
    }
    
    // If already loaded, skip
    if (img.classList.contains('loaded')) {
        return false;
    }
    
    // Set up fade-in when image loads
    const handleImageLoad = function() {
        // Use requestAnimationFrame for smooth animation
        requestAnimationFrame(() => {
            this.classList.add('loaded');
        });
    };
    
    // Handle image load event
    img.addEventListener('load', handleImageLoad, { once: true });
    
    // Handle error case
    img.addEventListener('error', function() {
        this.style.opacity = '0.3';
        // Still mark as loaded to prevent retries
        this.classList.add('loaded');
    }, { once: true });
    
    // Check if image is already cached/loaded
    if (img.complete && img.naturalHeight !== 0) {
        // Image already loaded (cached), fade in immediately
        requestAnimationFrame(() => {
            img.classList.add('loaded');
        });
    } else {
        // Start loading the image by setting src from data-src
        img.src = img.dataset.src;
    }
    
    return true;
}

// Function to check if image is in viewport or near viewport
function isInViewport(element, margin = 300) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top < (window.innerHeight || document.documentElement.clientHeight) + margin &&
        rect.bottom > -margin &&
        rect.left < (window.innerWidth || document.documentElement.clientWidth) + margin &&
        rect.right > -margin
    );
}

// Initialize lazy loading - can be called multiple times
function initLazyLoading() {
    // Lazy load images
    const lazyImages = document.querySelectorAll('img.lazy-load[data-src]:not(.loaded), img[data-src]:not(.loaded)');
    
    if (lazyImages.length === 0) return;
    
    // First, load images that are already in viewport or near viewport immediately
    lazyImages.forEach(img => {
        const rect = img.getBoundingClientRect();
        // Load immediately if visible on screen or within 200px
        const isVisible = rect.top < window.innerHeight + 200 && rect.bottom > -200;
        if (isVisible) {
            loadLazyImage(img);
        }
    });
    
    // Then set up Intersection Observer for remaining images
    const remainingImages = document.querySelectorAll('img.lazy-load[data-src]:not(.loaded), img[data-src]:not(.loaded)');
    
    if ('IntersectionObserver' in window && remainingImages.length > 0) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    // Load immediately when entering viewport
                    if (loadLazyImage(img)) {
                        observer.unobserve(img);
                    }
                }
            });
        }, {
            rootMargin: '100px' // Start loading 100px before image enters viewport
        });
        
        remainingImages.forEach(img => {
            // Only observe if not already loaded
            if (!img.classList.contains('loaded')) {
                imageObserver.observe(img);
            }
        });
    } else if (remainingImages.length > 0) {
        // Fallback for browsers without IntersectionObserver - load all immediately
        remainingImages.forEach(img => {
            loadLazyImage(img);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize lazy loading immediately
    initLazyLoading();
    
    // Also load images on window load - load all visible images immediately
    window.addEventListener('load', function() {
        const remainingLazyImages = document.querySelectorAll('img.lazy-load[data-src]:not(.loaded)');
        remainingLazyImages.forEach(img => {
            const rect = img.getBoundingClientRect();
            // Load immediately if in viewport or near viewport
            if (rect.top < window.innerHeight + 300) {
                loadLazyImage(img);
            }
        });
    });
    
    // Immediate check: Load all images that are currently visible
    setTimeout(function() {
        const visibleImages = document.querySelectorAll('img.lazy-load[data-src]:not(.loaded)');
        visibleImages.forEach(img => {
            const rect = img.getBoundingClientRect();
            // Load if visible on screen
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                loadLazyImage(img);
            }
        });
    }, 100);
    
    // Load More Products Functionality
    const loadMoreBtn = document.getElementById('load-more-btn');
    let isLoading = false;
    let scrollTimeout = null;
    let isAtBottom = false;
    let popupShown = false; // Track if popup is currently shown
    
    // Function to load more products
    function loadMoreProducts() {
        if (!loadMoreBtn || isLoading) return;
        
        const btn = loadMoreBtn;
        const spinner = document.getElementById('load-more-spinner');
        const text = document.getElementById('load-more-text');
        const currentPage = parseInt(btn.dataset.currentPage) || 1;
        const totalPages = parseInt(btn.dataset.totalPages) || 1;
        const nextPage = currentPage + 1;
        
        // Check if there are more pages
        if (nextPage > totalPages) {
            return;
        }
        
        isLoading = true;
        
        // Show loading state
        if (spinner) spinner.classList.remove('hidden');
        if (text) text.textContent = 'Loading...';
        if (btn) btn.disabled = true;
        
        // Build query params
        const params = new URLSearchParams();
        params.append('page', nextPage);
        
        if (btn.dataset.category) {
            params.append('category', btn.dataset.category);
        }
        if (btn.dataset.search) {
            params.append('search', btn.dataset.search);
        }
        if (btn.dataset.featured) {
            params.append('featured', btn.dataset.featured);
        }
        
        // Fetch more products
        const apiUrl = window.APP_CONFIG?.urls?.loadMore || 'api/load-more-products.php';
        fetch(`${apiUrl}?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Append new products
                    const productsGrid = document.getElementById('products-grid');
                    if (productsGrid) {
                        // Create temporary container to parse HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = data.html;
                        
                        // Collect images BEFORE appending (while they're still in temp)
                        const newImages = [];
                        Array.from(temp.children).forEach(product => {
                            const images = product.querySelectorAll('img[data-src]');
                            images.forEach(img => {
                                newImages.push(img);
                            });
                        });
                        
                        // Append each product with fade-in animation
                        Array.from(temp.children).forEach((product, index) => {
                            product.style.opacity = '0';
                            product.style.transform = 'translateY(20px)';
                            productsGrid.appendChild(product);
                            
                            // Animate in
                            setTimeout(() => {
                                product.style.transition = 'all 0.5s ease';
                                product.style.opacity = '1';
                                product.style.transform = 'translateY(0)';
                            }, index * 50);
                        });
                        
                        // Reapply layout to newly loaded products
                        if (typeof setLayout === 'function') {
                            const savedLayout = localStorage.getItem('productLayout') || 'grid';
                            setTimeout(() => {
                                setLayout(savedLayout);
                            }, 100);
                        }
                        
                        // Load all new images immediately after DOM update
                        // Use multiple animation frames to ensure DOM is ready
                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                // Load all collected images
                                newImages.forEach((img, index) => {
                                    // Small delay to stagger loading slightly
                                    setTimeout(() => {
                                        // Ensure lazy-load class is present
                                        if (!img.classList.contains('lazy-load')) {
                                            img.classList.add('lazy-load');
                                        }
                                        
                                        // Skip if already loaded
                                        if (img.classList.contains('loaded')) {
                                            return;
                                        }
                                        
                                        // Directly set src from data-src to force immediate loading
                                        if (img.dataset.src) {
                                            // Always load - don't check placeholder, just load
                                            // Set up load handler
                                            const loadHandler = function() {
                                                requestAnimationFrame(() => {
                                                    this.classList.add('loaded');
                                                });
                                            };
                                            
                                            // Set up error handler
                                            const errorHandler = function() {
                                                this.style.opacity = '0.3';
                                                this.classList.add('loaded');
                                            };
                                            
                                            // Remove any existing handlers to avoid duplicates
                                            img.removeEventListener('load', loadHandler);
                                            img.removeEventListener('error', errorHandler);
                                            
                                            // Add handlers
                                            img.addEventListener('load', loadHandler, { once: true });
                                            img.addEventListener('error', errorHandler, { once: true });
                                            
                                            // Set src to trigger loading immediately
                                            img.src = img.dataset.src;
                                        }
                                    }, index * 10); // Stagger by 10ms per image
                                });
                                
                                // Double-check: Find any remaining unloaded images in the grid
                                setTimeout(() => {
                                    const remainingImages = productsGrid.querySelectorAll('img[data-src]:not(.loaded)');
                                    remainingImages.forEach(img => {
                                        if (!img.classList.contains('lazy-load')) {
                                            img.classList.add('lazy-load');
                                        }
                                        
                                        // Force load if still has placeholder or empty src
                                        if (img.dataset.src) {
                                            const needsLoad = img.src.includes('data:image/svg+xml') || 
                                                             img.src === '' || 
                                                             !img.src;
                                            
                                            if (needsLoad) {
                                                img.addEventListener('load', function() {
                                                    this.classList.add('loaded');
                                                }, { once: true });
                                                img.addEventListener('error', function() {
                                                    this.style.opacity = '0.3';
                                                    this.classList.add('loaded');
                                                }, { once: true });
                                                img.src = img.dataset.src;
                                            } else {
                                                loadLazyImage(img);
                                            }
                                        }
                                    });
                                    
                                // Initialize lazy loading for any future images
                                initLazyLoading();
                            }, 300);
                        });
                        
                        // Reapply layout to newly loaded products after a short delay
                        setTimeout(() => {
                            // Check if setLayout function exists (from products.php)
                            if (typeof window.setLayout === 'function') {
                                const savedLayout = localStorage.getItem('productLayout') || 'grid';
                                window.setLayout(savedLayout);
                            } else {
                                // Fallback: manually apply layout classes
                                const savedLayout = localStorage.getItem('productLayout') || 'grid';
                                const container = document.getElementById('products-grid');
                                const allItems = container.querySelectorAll('.product-item');
                                
                                // Remove all layout classes
                                container.classList.remove('grid', 'list-view', 'compact-view', 'sm:grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-3', 'lg:grid-cols-4', 'gap-6', 'gap-4', 'space-y-4');
                                allItems.forEach(item => {
                                    item.classList.remove('grid-item', 'list-item', 'compact-item', 'flex');
                                });
                                
                                // Apply layout
                                if (savedLayout === 'grid') {
                                    container.classList.add('grid', 'sm:grid-cols-2', 'lg:grid-cols-3', 'gap-6');
                                    allItems.forEach(item => item.classList.add('grid-item'));
                                } else if (savedLayout === 'list') {
                                    container.classList.add('list-view', 'space-y-4');
                                    allItems.forEach(item => {
                                        item.classList.add('list-item', 'flex', 'gap-4');
                                    });
                                } else if (savedLayout === 'compact') {
                                    container.classList.add('grid', 'sm:grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-4', 'gap-4', 'compact-view');
                                    allItems.forEach(item => item.classList.add('compact-item'));
                                }
                            }
                        }, 350);
                    });
                    }
                    
                    // Update button state
                    btn.dataset.currentPage = nextPage;
                    
                    if (data.hasMore) {
                        // Update remaining count
                        const remaining = data.totalProducts - (nextPage * 12);
                        const countEl = document.getElementById('load-more-count');
                        if (countEl) {
                            countEl.textContent = `(${remaining > 0 ? remaining : 0} remaining)`;
                        }
                        
                        // Re-enable button
                        if (spinner) spinner.classList.add('hidden');
                        if (text) text.textContent = 'Load More Products';
                        isLoading = false;
                    } else {
                        // Hide button if no more products
                        const container = document.getElementById('load-more-container');
                        if (container) {
                            container.style.opacity = '0';
                            setTimeout(() => {
                                container.style.display = 'none';
                            }, 300);
                        }
                        isLoading = false;
                    }
                    // Reset popup flag so it can show again if more products are available
                    popupShown = false;
                } else {
                    // Error handling
                    if (spinner) spinner.classList.add('hidden');
                    if (text) text.textContent = 'Load More Products';
                    isLoading = false;
                    alert('Failed to load more products. Please try again.');
                    // Reset popup flag
                    popupShown = false;
                }
            })
            .catch(error => {
                console.error('Error loading more products:', error);
                if (spinner) spinner.classList.add('hidden');
                if (text) text.textContent = 'Load More Products';
                isLoading = false;
                alert('Error loading products. Please try again.');
                // Reset popup flag
                popupShown = false;
            });
    }
    
    // Auto-detect footer and show popup to load more
    if (loadMoreBtn) {
        let popupShown = false;
        let footerCheckTimeout = null;
        
        // Create Load More Popup
        function createLoadMorePopup() {
            // Check if popup already exists
            let popup = document.getElementById('load-more-popup');
            if (popup) return popup;
            
            popup = document.createElement('div');
            popup.id = 'load-more-popup';
            popup.className = 'fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 hidden';
            popup.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full border-2 border-blue-500 animate-slide-up">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-box text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg">Load More Products?</h3>
                                <p class="text-gray-600 text-sm">We found more products for you!</p>
                            </div>
                        </div>
                        <button onclick="closeLoadMorePopup()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex gap-3">
                            <button onclick="confirmLoadMore()" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all transform hover:scale-105">
                                <i class="fas fa-arrow-down mr-2"></i>Load More
                            </button>
                            <button onclick="closeLoadMorePopup()" class="px-6 py-3 rounded-lg font-semibold border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all">
                                Not Now
                            </button>
                        </div>
                        <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" id="remember-load-more" checked class="mr-2">
                            <span>Remember my choice (auto-load next time)</span>
                        </label>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
            return popup;
        }
        
        // Show popup
        function showLoadMorePopup() {
            if (popupShown || isLoading) return;
            
            const btn = loadMoreBtn;
            const currentPage = parseInt(btn.dataset.currentPage) || 1;
            const totalPages = parseInt(btn.dataset.totalPages) || 1;
            
            // Check if there are more pages
            if (currentPage >= totalPages) {
                return; // No more products to load
            }
            
            const popup = createLoadMorePopup();
            popup.classList.remove('hidden');
            popupShown = true;
            
            // Auto-hide after 8 seconds if user doesn't interact
            setTimeout(() => {
                if (popupShown) {
                    closeLoadMorePopup();
                }
            }, 8000);
        }
        
        // Close popup
        window.closeLoadMorePopup = function() {
            const popup = document.getElementById('load-more-popup');
            if (popup) {
                popup.classList.add('hidden');
                popupShown = false;
            }
            
            // Check if user wants to remember "Not Now" preference
            const rememberCheckbox = document.getElementById('remember-load-more');
            if (rememberCheckbox && rememberCheckbox.checked) {
                // User wants to remember - but "Not Now" means don't auto-load
                // So we'll set it to false or remove it
                localStorage.removeItem('autoLoadMore');
            }
        };
        
        // Confirm load more
        window.confirmLoadMore = function() {
            // Check if user wants to remember preference
            const rememberCheckbox = document.getElementById('remember-load-more');
            if (rememberCheckbox && rememberCheckbox.checked) {
                // Save preference to auto-load in future
                localStorage.setItem('autoLoadMore', 'true');
            }
            closeLoadMorePopup();
            if (!isLoading) {
                loadMoreProducts();
            }
        };
        
        // Check if user previously chose to auto-load
        const shouldAutoLoad = localStorage.getItem('autoLoadMore') === 'true';
        
        // Detect when footer is reached
        window.addEventListener('scroll', function() {
            if (isLoading || popupShown) return;
            
            // Get footer element
            const footer = document.querySelector('footer');
            if (!footer) return;
            
            const footerRect = footer.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            // Check if footer is visible or near viewport (within 200px)
            if (footerRect.top <= windowHeight + 200 && footerRect.bottom > -200) {
                // Footer is in view or near view
                if (!footerCheckTimeout) {
                    footerCheckTimeout = setTimeout(() => {
                        if (!popupShown && !isLoading) {
                            // Check if user has auto-load enabled
                            const autoLoadEnabled = localStorage.getItem('autoLoadMore') === 'true';
                            
                            if (autoLoadEnabled) {
                                // Auto-load without showing popup
                                loadMoreProducts();
                            } else {
                                // Show popup to ask user
                                showLoadMorePopup();
                            }
                        }
                        footerCheckTimeout = null;
                    }, 500); // Wait 0.5 seconds after reaching footer
                }
            } else {
                // Footer not in view, clear timeout
                if (footerCheckTimeout) {
                    clearTimeout(footerCheckTimeout);
                    footerCheckTimeout = null;
                }
            }
        });
        
        // Manual click handler
        loadMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isLoading) {
                loadMoreProducts();
            }
        });
        
        // Note: popupShown flag is reset inside loadMoreProducts function after loading completes
    }
});

