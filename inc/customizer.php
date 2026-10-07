<?php
/**
 * Customizer: front page copy and contact details.
 * Appearance > Customize > PHASM front page / PHASM contact.
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values for every theme mod.
 */
function phasm_defaults() {
	return array(
		'hero_overline'   => 'Security · Learning · IT',
		'hero_title'      => 'Precise systems, quietly built.',
		'hero_text'       => 'One or two sentences on what PHASM does and who it is for.',
		'hero_btn1_label' => 'Explore services',
		'hero_btn1_url'   => '#services',
		'hero_btn2_label' => 'Get in touch',
		'hero_btn2_url'   => '#contact',
		'hero_image'      => 0,

		'services_title'  => 'What we do',
		'services_intro'  => 'Short intro to the services section.',
		'service_1_icon'  => 'shield',
		'service_2_icon'  => 'monitor',
		'service_3_icon'  => 'circuit',
		'service_1_title' => 'Service one',
		'service_1_text'  => 'Describe the service, the problem it solves and the result.',
		'service_1_url'   => '',
		'service_2_title' => 'Service two',
		'service_2_text'  => 'Describe the service, the problem it solves and the result.',
		'service_2_url'   => '',
		'service_3_title' => 'Service three',
		'service_3_text'  => 'Describe the service, the problem it solves and the result.',
		'service_3_url'   => '',

		'about_title'     => 'About PHASM',
		'about_text'      => "A paragraph about PHASM: background, approach and what makes the work different.\n\nA second paragraph with supporting detail.",

		'cta_title'       => 'Start a conversation',
		'cta_text'        => 'One sentence inviting contact, and what happens next.',
		'cta_label'       => 'Contact us',

		'contact_email'   => '',
		'contact_phone'   => '',
		'contact_address' => '',
		'footer_text'     => 'One-line description for the footer.',
	);
}

/**
 * Get a theme mod with its default.
 */
function phasm_mod( $key ) {
	$defaults = phasm_defaults();
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Register Customizer sections and controls.
 */
function phasm_customize_register( $wp_customize ) {
	$defaults = phasm_defaults();

	$wp_customize->add_section( 'phasm_front', array( 'title' => __( 'PHASM front page', 'phasm' ), 'priority' => 30 ) );
	$wp_customize->add_section( 'phasm_contact', array( 'title' => __( 'PHASM contact & footer', 'phasm' ), 'priority' => 31 ) );

	$fields = array(
		'hero_overline'   => array( 'Hero overline', 'text', 'phasm_front' ),
		'hero_title'      => array( 'Hero title', 'text', 'phasm_front' ),
		'hero_text'       => array( 'Hero text', 'textarea', 'phasm_front' ),
		'hero_btn1_label' => array( 'Primary button label', 'text', 'phasm_front' ),
		'hero_btn1_url'   => array( 'Primary button link', 'url', 'phasm_front' ),
		'hero_btn2_label' => array( 'Secondary button label', 'text', 'phasm_front' ),
		'hero_btn2_url'   => array( 'Secondary button link', 'url', 'phasm_front' ),
		'services_title'  => array( 'Services title', 'text', 'phasm_front' ),
		'services_intro'  => array( 'Services intro', 'textarea', 'phasm_front' ),
		'service_1_title' => array( 'Service 1 title', 'text', 'phasm_front' ),
		'service_1_text'  => array( 'Service 1 text', 'textarea', 'phasm_front' ),
		'service_1_url'   => array( 'Service 1 link', 'url', 'phasm_front' ),
		'service_2_title' => array( 'Service 2 title', 'text', 'phasm_front' ),
		'service_2_text'  => array( 'Service 2 text', 'textarea', 'phasm_front' ),
		'service_2_url'   => array( 'Service 2 link', 'url', 'phasm_front' ),
		'service_3_title' => array( 'Service 3 title', 'text', 'phasm_front' ),
		'service_3_text'  => array( 'Service 3 text', 'textarea', 'phasm_front' ),
		'service_3_url'   => array( 'Service 3 link', 'url', 'phasm_front' ),
		'about_title'     => array( 'About title', 'text', 'phasm_front' ),
		'about_text'      => array( 'About text (blank line = new paragraph)', 'textarea', 'phasm_front' ),
		'cta_title'       => array( 'CTA title', 'text', 'phasm_front' ),
		'cta_text'        => array( 'CTA text', 'textarea', 'phasm_front' ),
		'cta_label'       => array( 'CTA button label', 'text', 'phasm_front' ),
		'contact_email'   => array( 'Email', 'email', 'phasm_contact' ),
		'contact_phone'   => array( 'Phone', 'text', 'phasm_contact' ),
		'contact_address' => array( 'Address', 'textarea', 'phasm_contact' ),
		'footer_text'     => array( 'Footer description', 'textarea', 'phasm_contact' ),
	);

	foreach ( $fields as $key => $f ) {
		switch ( $f[1] ) {
			case 'url':
				$sanitize = 'esc_url_raw';
				break;
			case 'email':
				$sanitize = 'sanitize_email';
				break;
			case 'textarea':
				$sanitize = 'sanitize_textarea_field';
				break;
			default:
				$sanitize = 'sanitize_text_field';
		}
		$wp_customize->add_setting( $key, array( 'default' => $defaults[ $key ], 'sanitize_callback' => $sanitize ) );
		$wp_customize->add_control( $key, array( 'label' => $f[0], 'type' => $f[1], 'section' => $f[2] ) );
	}

	// Icon pickers for the three service boxes.
	$phasm_icon_choices = phasm_service_icons();
	for ( $i = 1; $i <= 3; $i++ ) {
		$wp_customize->add_setting(
			"service_{$i}_icon",
			array(
				'default'           => $defaults[ "service_{$i}_icon" ],
				'sanitize_callback' => 'phasm_sanitize_icon',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			"service_{$i}_icon",
			array(
				/* translators: %d: service number */
				'label'    => sprintf( __( 'Service %d icon', 'phasm' ), $i ),
				'type'     => 'select',
				'choices'  => $phasm_icon_choices,
				'section'  => 'phasm_front',
				'priority' => 20 + $i,
			)
		);
	}

	$wp_customize->add_setting( 'hero_image', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'hero_image',
			array(
				'label'     => __( 'Hero image (4:3)', 'phasm' ),
				'section'   => 'phasm_front',
				'mime_type' => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'phasm_customize_register' );

/**
 * Only allow icon slugs defined in phasm_service_icons().
 */
function phasm_sanitize_icon( $value, $setting ) {
	$icons = phasm_service_icons();
	return isset( $icons[ $value ] ) ? $value : $setting->default;
}

/**
 * Live preview of icon changes without a full reload.
 */
function phasm_customize_preview_icons( $wp_customize ) {
	for ( $i = 1; $i <= 3; $i++ ) {
		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				"service_{$i}_icon",
				array(
					'selector'        => '.service-card--' . $i . ' .service-card__icon',
					'render_callback' => function () use ( $i ) {
						return phasm_icon( phasm_mod( "service_{$i}_icon" ) );
					},
				)
			);
		}
	}
}
add_action( 'customize_register', 'phasm_customize_preview_icons', 20 );
