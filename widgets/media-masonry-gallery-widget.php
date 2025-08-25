<?php

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Media_Masonry_Gallery_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'media-masonry-gallery';
    }

    public function get_title()
    {
        return esc_html__('Media Masonry Gallery', 'wp-media-masonry-elementor');
    }

    public function get_icon()
    {
        return 'eicon-gallery-masonry';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    public function get_script_depends()
    {
        return ['wp-media-masonry-elementor-frontend'];
    }

    public function get_style_depends()
    {
        return ['wp-media-masonry-elementor-frontend'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_gallery',
            [
                'label' => esc_html__('Galerie Masonry', 'wp-media-masonry-elementor'),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'media_type',
            [
                'label' => esc_html__('Media Type', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'image' => esc_html__('Image', 'wp-media-masonry-elementor'),
                    'video' => esc_html__('Video', 'wp-media-masonry-elementor'),
                ],
            ]
        );

        $repeater->add_control(
            'image',
            [
                'label' => esc_html__('Image', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_types' => ['image'],
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'media_type' => 'image',
                ],
            ]
        );

        $repeater->add_control(
            'video',
            [
                'label' => esc_html__('Video File', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_types' => ['video'],
                'condition' => [
                    'media_type' => 'video',
                ],
            ]
        );

        $this->add_control(
            'gallery_items',
            [
                'label' => esc_html__('Items', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'media_type' => 'image',
                    ],
                    [
                        'media_type' => 'image',
                    ],
                    [
                        'media_type' => 'video',
                    ],
                ],
                'title_field' => '{{{ media_type.charAt(0).toUpperCase() + media_type.slice(1) }}}',
            ]
        );

        $this->add_control(
            'obfuscate_links',
            [
                'label' => esc_html__('Obfuscation des liens', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'wp-media-masonry-elementor'),
                'label_off' => esc_html__('No', 'wp-media-masonry-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__('Convertit tous les liens <a> en <span> et accepte les .avif dans la visionneuse.', 'wp-media-masonry-elementor'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_layout',
            [
                'label' => esc_html__('Éléments', 'wp-media-masonry-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'item_width',
            [
                'label' => esc_html__('Largeur', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'tablet_default' => [
                    'unit' => 'px',
                    'size' => 250,
                ],
                'mobile_default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item' => 'width: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label' => esc_html__('Espace', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 15,
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .media-masonry-gallery' => 'margin: -{{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .gallery-item' => 'padding: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_items',
            [
                'label' => esc_html__('Design', 'wp-media-masonry-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'item_border',
                'selector' => '{{WRAPPER}} .gallery-item-inner',
            ]
        );

        $this->add_responsive_control(
            'item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'wp-media-masonry-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .gallery-item img, {{WRAPPER}} .gallery-item video' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_box_shadow',
                'selector' => '{{WRAPPER}} .gallery-item-inner',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if (empty($settings['gallery_items'])) {
            return;
        }

        $this->add_render_attribute('gallery', 'class', 'media-masonry-gallery');

        $this->add_render_attribute('gallery', 'data-item-width', $settings['item_width']['size'] ?? 300);
        $this->add_render_attribute('gallery', 'data-item-width-tablet', $settings['item_width_tablet']['size'] ?? 250);
        $this->add_render_attribute('gallery', 'data-item-width-mobile', $settings['item_width_mobile']['size'] ?? 100);
?>
        <div <?php echo $this->get_render_attribute_string('gallery'); ?>>
            <?php
            foreach ($settings['gallery_items'] as $item) {
            ?>
                <div class="gallery-item">
                    <div class="gallery-item-inner">
                        <?php
                        if ($item['media_type'] === 'image' && !empty($item['image']['url'])) {
                            $image_html = wp_get_attachment_image(
                                $item['image']['id'],
                                'large',
                                false,
                                [
                                    'class' => 'gallery-image',
                                    'loading' => 'lazy',
                                    'onload' => 'this.classList.add("loaded")'
                                ]
                            );

                            $tag_name = 'a';
                            $href_attr = 'href';
                            $obfuscated_class = '';
                            if ('yes' === $settings['obfuscate_links']) {
                                $tag_name = 'span';
                                $href_attr = 'data-href';
                                $obfuscated_class = ' wpmme-obfuscated-link';
                            }

                            if ($image_html) {
                                echo '<' . $tag_name . ' ' . $href_attr . '="' . esc_url($item['image']['url']) . '" class="wpmme-lightbox' . $obfuscated_class . '" data-media-type="image">';
                                echo $image_html;
                                echo '</' . $tag_name . '>';
                            } else {
                                echo '<' . $tag_name . ' ' . $href_attr . '="' . esc_url($item['image']['url']) . '" class="wpmme-lightbox' . $obfuscated_class . '" data-media-type="image">';
                                echo '<img class="gallery-image" src="' . esc_url($item['image']['url']) . '" alt="" loading="lazy" onload="this.classList.add(\'loaded\')">';
                                echo '</' . $tag_name . '>';
                            }
                        } elseif ($item['media_type'] === 'video' && !empty($item['video']['url'])) {
                            $tag_name = 'a';
                            $href_attr = 'href';
                            $obfuscated_class = '';
                            if ('yes' === $settings['obfuscate_links']) {
                                $tag_name = 'span';
                                $href_attr = 'data-href';
                                $obfuscated_class = ' wpmme-obfuscated-link';
                            }
                            echo '<' . $tag_name . ' ' . $href_attr . '="' . esc_url($item['video']['url']) . '" class="wpmme-lightbox' . $obfuscated_class . '" data-media-type="video">';
                            echo '<video class="gallery-video" controls preload="metadata" onloadeddata="this.parentNode.classList.add(\'loaded\')">
';
                            echo '<source src="' . esc_url($item['video']['url']) . '">';
                            echo esc_html__('Your browser does not support the video tag.', 'wp-media-masonry-elementor');
                            echo '</video>';
                            echo '</' . $tag_name . '>';
                        }
                        ?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
<?php
    }

    protected function content_template()
    {
?>
        <div class="media-masonry-gallery" data-item-width="{{{ settings.item_width.size || 300 }}}" data-item-width-tablet="{{{ settings.item_width_tablet.size || 250 }}}" data-item-width-mobile="{{{ settings.item_width_mobile.size || 100 }}}">
            <#
            if ( settings.gallery_items ) {
                _.each( settings.gallery_items, function( item ) {
            #>
                    <div class="gallery-item">
                        <div class="gallery-item-inner">
                        <# if ( item.media_type === 'image' && item.image.url ) { #>
                            <img class="gallery-image" src="{{{ item.image.url }}}" loading="lazy" onload="this.classList.add('loaded')" />
                        <# } else if ( item.media_type === 'video' && item.video.url ) { #>
                            <video class="gallery-video" controls preload="metadata" onloadeddata="this.parentNode.classList.add('loaded')">
                                <source src="{{{ item.video.url }}}" />
                            </video>
                        <# } #>
                        </div>
                    </div>
            <#
                } );
            }
            #>
        </div>
<?php
    }
}