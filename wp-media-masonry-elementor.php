<?php

/**
 * Plugin Name: WP Media Masonry Elementor
 * Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-media-masonry-elementor/
 * Description: WP Media Masonry Elementor est un widget qui ajoute une galerie de médias images et vidéos mélangés, avec une mise en page masonry. Il inclut des options de style, gère le format AVIF et peut obfusquer les liens.
 * Version: 1.0
 * Author: Kevin Benabdelhak
 * Author URI: https://kevin-benabdelhak.fr/
 * Contributors: kevinbenabdelhak
 * Elementor: 3.27.5
 */


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
	require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$monUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/kevinbenabdelhak/WP-Media-Masonry-Elementor/',
	__FILE__,
	'wp-media-masonry-elementor'
);

$monUpdateChecker->setBranch('main');

final class WP_Media_Masonry_Elementor_Plugin
{

	private static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()
	{
		add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
		add_filter('upload_mimes', [$this, 'allow_avif_uploads']);
	}

	public function allow_avif_uploads($mimes)
	{
		$mimes['avif'] = 'image/avif';
		return $mimes;
	}

	public function on_plugins_loaded()
	{
		if ($this->is_compatible()) {
			add_action('elementor/widgets/register', [$this, 'register_widgets']);
			add_action('wp_enqueue_scripts', [$this, 'register_assets'], 5);
			add_action('elementor/editor/after_enqueue_scripts', [$this, 'register_assets'], 5);
			add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
			add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_assets']);
		}
	}

	public function is_compatible()
	{
		if (!did_action('elementor/loaded')) {
			add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
			return false;
		}
		return true;
	}

	public function register_assets()
	{
		wp_register_script('imagesloaded', 'https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js', ['jquery'], '5.0.0', true);
		wp_register_script('masonry', 'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js', ['jquery', 'imagesloaded'], '4.2.2', true);
		wp_register_script(
			'wp-media-masonry-elementor-frontend',
			plugins_url('assets/js/frontend.js', __FILE__),
			['jquery', 'masonry'],
			'1.0.1',
			true
		);
		wp_register_style(
			'wp-media-masonry-elementor-frontend',
			plugins_url('assets/css/frontend.css', __FILE__),
			[],
			'1.0.0'
		);
	}

	public function enqueue_assets()
	{
		wp_enqueue_script('wp-media-masonry-elementor-frontend');
		wp_enqueue_style('wp-media-masonry-elementor-frontend');
	}

	public function admin_notice_missing_main_plugin()
	{
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}
		$message = sprintf(
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'wp-media-masonry-elementor'),
			'<strong>' . esc_html__('WP Media Masonry Elementor', 'wp-media-masonry-elementor') . '</strong>',
			'<strong>' . esc_html__('Elementor', 'wp-media-masonry-elementor') . '</strong>'
		);
		printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
	}

	public function register_widgets($widgets_manager)
	{
		require_once(__DIR__ . '/widgets/media-masonry-gallery-widget.php');
		$widgets_manager->register(new \Elementor_Media_Masonry_Gallery_Widget());
	}
}

WP_Media_Masonry_Elementor_Plugin::instance();