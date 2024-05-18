<?php settings_errors(); ?>
<div class="wrap">
	<h1><?php esc_html_e( 'JegAI Assistant Settings', 'jeg-ai-assistant' ); ?></h1>
	<form action="options.php" method="post">
		<?php
		settings_fields( $args );
		do_settings_sections( $args );
		submit_button();
		?>
	</form>
</div>
