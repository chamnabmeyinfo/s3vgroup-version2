<!-- Parallax Section Template -->
<section class="parallax-section relative overflow-hidden" id="parallaxSection" 
         data-speed="<?= escape($parallaxSettings['parallax_speed'] ?? 0.5) ?>"
         style="height: <?= (int)($parallaxSettings['parallax_height'] ?? 600) ?>px;">
    <!-- Background Image with Parallax -->
    <div class="parallax-bg absolute inset-0" style="
        <?php if (!empty($parallaxSettings['parallax_bg_image'])): ?>
            background-image: url('<?= escape($parallaxSettings['parallax_bg_image']) ?>');
        <?php else: ?>
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        <?php endif; ?>
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    "></div>
    
    <!-- Overlay -->
    <div class="absolute inset-0" style="
        background: <?= escape($parallaxSettings['parallax_overlay_color'] ?? '#000000') ?>;
        opacity: <?= escape($parallaxSettings['parallax_overlay_opacity'] ?? 0.4) ?>;
    "></div>
    
    <!-- Particles Effect -->
    <?php if (($parallaxSettings['parallax_particles_enabled'] ?? 1) == 1): ?>
    <canvas id="parallaxParticles<?= uniqid() ?>" class="parallax-particles absolute inset-0 pointer-events-none" style="color: <?= escape($parallaxSettings['parallax_particles_color'] ?? '#ffffff') ?>;"></canvas>
    <?php endif; ?>
    
    <!-- Content -->
    <div class="relative z-10 py-16 md:py-24">
        <div class="container mx-auto px-4 text-center">
            <div class="parallax-content max-w-4xl mx-auto" data-animation="<?= escape($parallaxSettings['parallax_animation_type'] ?? 'fade-in') ?>">
                <?php if (!empty($parallaxSettings['parallax_subtitle'])): ?>
                <p class="text-lg md:text-xl mb-4 font-semibold" style="color: <?= escape($parallaxSettings['parallax_subtitle_color'] ?? '#ffffff') ?>;">
                    <?= escape($parallaxSettings['parallax_subtitle']) ?>
                </p>
                <?php endif; ?>
                
                <?php if (!empty($parallaxSettings['parallax_title'])): ?>
                <h2 class="text-4xl md:text-6xl lg:text-7xl font-black mb-6 leading-tight" style="color: <?= escape($parallaxSettings['parallax_title_color'] ?? '#ffffff') ?>;">
                    <?= escape($parallaxSettings['parallax_title']) ?>
                </h2>
                <?php endif; ?>
                
                <?php if (!empty($parallaxSettings['parallax_description'])): ?>
                <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto leading-relaxed" style="color: <?= escape($parallaxSettings['parallax_text_color'] ?? '#ffffff') ?>;">
                    <?= escape($parallaxSettings['parallax_description']) ?>
                </p>
                <?php endif; ?>
                
                <div class="flex flex-wrap justify-center gap-4">
                    <?php if (!empty($parallaxSettings['parallax_button_text'])): ?>
                    <a href="<?= url($parallaxSettings['parallax_button_url'] ?? 'products.php') ?>" class="px-8 py-4 rounded-xl font-bold text-lg transition-all duration-300 transform hover:scale-105 hover:shadow-2xl" style="
                        background: <?= escape($parallaxSettings['parallax_button_bg_color'] ?? '#3b82f6') ?>;
                        color: <?= escape($parallaxSettings['parallax_button_text_color'] ?? '#ffffff') ?>;
                    ">
                        <?= escape($parallaxSettings['parallax_button_text']) ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($parallaxSettings['parallax_button_2_text'])): ?>
                    <a href="<?= url($parallaxSettings['parallax_button_2_url'] ?? 'contact.php') ?>" class="px-8 py-4 rounded-xl font-bold text-lg border-2 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl" style="
                        background: <?= escape($parallaxSettings['parallax_button_2_bg_color'] ?? 'transparent') ?>;
                        color: <?= escape($parallaxSettings['parallax_button_2_text_color'] ?? '#ffffff') ?>;
                        border-color: <?= escape($parallaxSettings['parallax_button_2_text_color'] ?? '#ffffff') ?>;
                    ">
                        <?= escape($parallaxSettings['parallax_button_2_text']) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
