<?php
/**
 * Custom Customizer controls:
 * - Phasm_Modules_Control: drag-and-drop module order with on/off checkboxes.
 * - Phasm_Quotes_Control:  repeater for Daily wisdom quotes (+ Add quote).
 *
 * Both write a JSON string into a hidden input linked to their setting.
 * Behaviour lives in assets/js/customizer-controls.js.
 *
 * @package phasm
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

/**
 * Sortable module list.
 */
class Phasm_Modules_Control extends WP_Customize_Control {
	public $type = 'phasm_modules';

	public function render_content() {
		$labels  = phasm_module_list();
		$schemes = phasm_module_schemes();
		$modules = json_decode( phasm_sanitize_modules( $this->value() ), true );
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<ul class="phasm-modules">
			<?php foreach ( $modules as $m ) : ?>
				<li class="phasm-modules__item<?php echo $m['on'] ? '' : ' is-off'; ?>" data-id="<?php echo esc_attr( $m['id'] ); ?>">
					<span class="phasm-modules__handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to move', 'phasm' ); ?>" aria-hidden="true"></span>
					<label>
						<input type="checkbox" <?php checked( $m['on'] ); ?>>
						<?php echo esc_html( $labels[ $m['id'] ] ); ?>
					</label>
					<select class="phasm-modules__scheme" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: module */ __( 'Colours for %s', 'phasm' ), $labels[ $m['id'] ] ) ); ?>">
						<?php foreach ( $schemes as $sid => $slabel ) : ?>
							<option value="<?php echo esc_attr( $sid ); ?>" <?php selected( $m['scheme'], $sid ); ?>><?php echo esc_html( $slabel ); ?></option>
						<?php endforeach; ?>
					</select>
					<span class="phasm-modules__arrows">
						<button type="button" class="button-link phasm-up" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: module */ __( 'Move %s up', 'phasm' ), $labels[ $m['id'] ] ) ); ?>"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
						<button type="button" class="button-link phasm-down" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: module */ __( 'Move %s down', 'phasm' ), $labels[ $m['id'] ] ) ); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<input type="hidden" class="phasm-json" <?php $this->link(); ?> value="<?php echo esc_attr( $this->value() ); ?>">
		<?php
	}
}

/**
 * Quote repeater.
 */
class Phasm_Quotes_Control extends WP_Customize_Control {
	public $type = 'phasm_quotes';

	public function render_content() {
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<div class="phasm-quotes"
			data-label-quote="<?php esc_attr_e( 'Quote', 'phasm' ); ?>"
			data-label-author="<?php esc_attr_e( 'Author / person', 'phasm' ); ?>"
			data-label-remove="<?php esc_attr_e( 'Remove quote', 'phasm' ); ?>"></div>
		<button type="button" class="button phasm-quotes__add"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> <?php esc_html_e( 'Add quote', 'phasm' ); ?></button>
		<input type="hidden" class="phasm-json" <?php $this->link(); ?> value="<?php echo esc_attr( $this->value() ); ?>">
		<?php
	}
}

/**
 * Load the controls' script and styles in the Customizer.
 */
function phasm_customizer_controls_assets() {
	wp_enqueue_script(
		'phasm-customizer-controls',
		get_template_directory_uri() . '/assets/js/customizer-controls.js',
		array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
		PHASM_VERSION,
		true
	);
	wp_add_inline_style(
		'customize-controls',
		'.phasm-modules{margin:8px 0 0}
		.phasm-modules__item{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #dcdcde;padding:8px 10px;margin:0 0 6px;cursor:move}
		.phasm-modules__item.is-off label{color:#8c8f94;text-decoration:line-through}
		.phasm-modules__item{flex-wrap:wrap}
		.phasm-modules__item label{flex:1;cursor:pointer;min-width:110px}
		.phasm-modules__scheme{order:3;flex-basis:100%;max-width:none!important;min-height:30px!important;font-size:12px!important}
		.phasm-modules__arrows{order:2;display:flex;gap:2px}
		.phasm-modules__handle{color:#8c8f94}
		.phasm-modules__arrows button{color:#50575e;padding:0}
		.phasm-modules .ui-sortable-placeholder{border:1px dashed #007A4D;background:#f0faf5;visibility:visible!important;height:36px}
		.phasm-quote{background:#fff;border:1px solid #dcdcde;padding:10px;margin:0 0 8px}
		.phasm-quote__head{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-weight:600}
		.phasm-quote label{display:block;margin:6px 0 2px;font-size:12px}
		.phasm-quote textarea,.phasm-quote input{width:100%}
		.phasm-quote__remove{color:#b32d2e!important}
		.phasm-quotes__add{display:inline-flex!important;align-items:center;gap:4px}'
	);
}
add_action( 'customize_controls_enqueue_scripts', 'phasm_customizer_controls_assets' );
