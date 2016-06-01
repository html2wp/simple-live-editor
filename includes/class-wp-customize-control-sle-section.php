<?php

function sle_init_wp_customize_control_sle_section() {

	if ( class_exists( 'WP_Customize_Control' ) ) {

		class WP_Customize_Control_Sle_Section extends WP_Customize_Control {

			public $type = 'section';

			public function render_content() {
				?>
					<label>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<ul class="sle-sections-list">
							<li>Who We Are</li>
						</ul>
					</label>
				<?php
			}
		}
	}
}