<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$sliderId = $_GET['id'] ?? null;

if (!$sliderId) {
    header('Location: ' . url('admin/hero-sliders.php'));
    exit;
}

$slider = db()->fetchOne(
    "SELECT * FROM hero_sliders WHERE id = :id",
    ['id' => $sliderId]
);

if (!$slider) {
    header('Location: ' . url('admin/hero-sliders.php'));
    exit;
}

// Create duplicate
$newData = [
    'title' => $slider['title'] . ' (Copy)',
    'subtitle' => $slider['subtitle'] ?? null,
    'description' => $slider['description'] ?? null,
    'image' => $slider['image'] ?? null,
    'image_mobile' => $slider['image_mobile'] ?? null,
    'button_text_1' => $slider['button_text_1'] ?? null,
    'button_link_1' => $slider['button_link_1'] ?? null,
    'button_text_2' => $slider['button_text_2'] ?? null,
    'button_link_2' => $slider['button_link_2'] ?? null,
    'overlay_color' => $slider['overlay_color'] ?? null,
    'overlay_gradient' => $slider['overlay_gradient'] ?? null,
    'text_alignment' => $slider['text_alignment'] ?? 'center',
    'text_color' => $slider['text_color'] ?? 'white',
    'background_size' => $slider['background_size'] ?? 'cover',
    'background_position' => $slider['background_position'] ?? 'center',
    'parallax_effect' => $slider['parallax_effect'] ?? 0,
    'slide_height' => $slider['slide_height'] ?? 'auto',
    'custom_height' => $slider['custom_height'] ?? null,
    'content_animation' => $slider['content_animation'] ?? 'fade-in-up',
    'animation_speed' => $slider['animation_speed'] ?? 'normal',
    'sort_order' => ($slider['sort_order'] ?? 0) + 1,
    'is_active' => 0, // Make inactive by default
];

// Insert new slider
$newId = db()->insert('hero_sliders', $newData);

header('Location: ' . url('admin/hero-slider-edit.php?id=' . $newId . '&message=Slider duplicated successfully'));
exit;

