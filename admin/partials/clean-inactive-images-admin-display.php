<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form action="options.php" method="post">
		<?php
		settings_fields( 'cii_settings_group' );
		do_settings_sections( 'cii_settings_group' );
		?>
		<label for="cii_post_type">
			Post Type:
		</label>
		<input type="text" name="cii_post_type" id="cii_post_type"
		       value="<?php echo esc_attr( get_option( 'cii_post_type' ) ); ?>"/>
		<?php submit_button( 'Save' ); ?>
	</form>

	<p>BE PATIENT. THIS CAN TAKE A LOOOONG TIME TO FINISH!</p>

	<p>
		<button id="cii_start" class="button-primary">GO!</button>
		<span id="loading" style="display: none">Loading...</span>
	</p>
	<div id="results"></div>

</div>