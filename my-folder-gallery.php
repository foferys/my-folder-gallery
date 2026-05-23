<?php
/**
 * Plugin Name: my-folder-gallery
 * Description: Gestisce progetti portfolio con upload in cartelle dedicate e shortcode frontend.
 * Version: 0.2.8
 * Author: Sportime
 * Text Domain: my-folder-gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PWG_VERSION', '0.2.8' );
define( 'PWG_PLUGIN_FILE', __FILE__ );
define( 'PWG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PWG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PWG_POST_TYPE', 'pwg_work' );
define( 'PWG_OPTION_BASE_SUBDIR', 'pwg_base_subdir' );
define( 'PWG_OPTION_GALLERY_TITLE', 'pwg_gallery_title' );
define( 'PWG_OPTION_ACCENT_COLOR', 'pwg_accent_color' );
define( 'PWG_OPTION_CARD_CORNERS', 'pwg_card_corners' );
define( 'PWG_SHORTCODE', 'my_folder-gallery' );
define( 'PWG_SHORTCODE_PRETTY', 'my_folder–gallery' );

register_activation_hook( __FILE__, 'pwg_activate' );

function pwg_activate(): void {
	pwg_ensure_base_dir();
	pwg_register_post_type();
	flush_rewrite_rules();
}

add_action( 'init', 'pwg_register_post_type' );
function pwg_register_post_type(): void {
	register_post_type(
		PWG_POST_TYPE,
		array(
			'labels'       => array(
				'name'               => __( 'Folder Gallery', 'my-folder-gallery' ),
				'singular_name'      => __( 'Lavoro', 'my-folder-gallery' ),
				'add_new_item'       => __( 'Aggiungi nuovo progetto', 'my-folder-gallery' ),
				'edit_item'          => __( 'Modifica progetto', 'my-folder-gallery' ),
				'new_item'           => __( 'Nuovo progetto', 'my-folder-gallery' ),
				'view_item'          => __( 'Vedi progetto', 'my-folder-gallery' ),
				'search_items'       => __( 'Cerca lavori', 'my-folder-gallery' ),
				'not_found'          => __( 'Nessun lavoro trovato', 'my-folder-gallery' ),
				'menu_name'          => __( 'Folder Gallery', 'my-folder-gallery' ),
				'featured_image'     => __( 'Cover', 'my-folder-gallery' ),
				'set_featured_image' => __( 'Imposta cover', 'my-folder-gallery' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-format-gallery',
			'supports'     => array( 'title' ),
			'capability_type' => 'post',
		)
	);
}

add_action( 'admin_menu', 'pwg_register_settings_page' );
function pwg_register_settings_page(): void {
	add_submenu_page(
		'edit.php?post_type=' . PWG_POST_TYPE,
		__( 'Impostazioni Folder Gallery', 'my-folder-gallery' ),
		__( 'Impostazioni', 'my-folder-gallery' ),
		'manage_options',
		'pwg-settings',
		'pwg_render_settings_page'
	);
}

add_action( 'admin_init', 'pwg_register_settings' );
function pwg_register_settings(): void {
	register_setting(
		'pwg_settings',
		PWG_OPTION_BASE_SUBDIR,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'pwg_sanitize_base_subdir',
			'default'           => 'sportime-lavori',
		)
	);

	register_setting(
		'pwg_settings',
		PWG_OPTION_GALLERY_TITLE,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'pwg_sanitize_gallery_title',
			'default'           => 'SELECTED WORKS',
		)
	);

	register_setting(
		'pwg_settings',
		PWG_OPTION_ACCENT_COLOR,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'pwg_sanitize_accent_color',
			'default'           => '#fbed51',
		)
	);

	register_setting(
		'pwg_settings',
		PWG_OPTION_CARD_CORNERS,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'pwg_sanitize_card_corners',
			'default'           => 'square',
		)
	);
}


function pwg_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Non hai i permessi per accedere a questa pagina.', 'my-folder-gallery' ) );
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Impostazioni Folder Gallery', 'my-folder-gallery' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'pwg_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( PWG_OPTION_BASE_SUBDIR ); ?>"><?php echo esc_html__( 'Cartella base in uploads', 'my-folder-gallery' ); ?></label>
					</th>
					<td>
						<input
							name="<?php echo esc_attr( PWG_OPTION_BASE_SUBDIR ); ?>"
							id="<?php echo esc_attr( PWG_OPTION_BASE_SUBDIR ); ?>"
							type="text"
							class="regular-text"
							value="<?php echo esc_attr( pwg_get_base_subdir() ); ?>"
						>
						<p class="description">
							<?php echo esc_html__( 'Esempio: sportime-lavori. Ogni progetto avra una sottocartella dedicata.', 'my-folder-gallery' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( PWG_OPTION_GALLERY_TITLE ); ?>"><?php echo esc_html__( 'Titolo gallery', 'my-folder-gallery' ); ?></label>
					</th>
					<td>
						<input
							name="<?php echo esc_attr( PWG_OPTION_GALLERY_TITLE ); ?>"
							id="<?php echo esc_attr( PWG_OPTION_GALLERY_TITLE ); ?>"
							type="text"
							class="regular-text"
							value="<?php echo esc_attr( pwg_get_gallery_title() ); ?>"
							placeholder="<?php echo esc_attr__( 'Lascia vuoto per non mostrare nessun titolo', 'my-folder-gallery' ); ?>"
						>
						<p class="description">
							<?php echo esc_html__( 'Questo testo appare sopra la gallery. Lascia il campo vuoto se vuoi mostrare solo le immagini.', 'my-folder-gallery' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( PWG_OPTION_ACCENT_COLOR ); ?>"><?php echo esc_html__( 'Colore pulsanti e accenti', 'my-folder-gallery' ); ?></label>
					</th>
					<td>
						<input
							name="<?php echo esc_attr( PWG_OPTION_ACCENT_COLOR ); ?>"
							id="<?php echo esc_attr( PWG_OPTION_ACCENT_COLOR ); ?>"
							type="color"
							value="<?php echo esc_attr( pwg_get_accent_color() ); ?>"
						>
						<p class="description">
							<?php echo esc_html__( 'Questo colore viene usato per frecce, pulsanti, pallini e accenti della modale gallery.', 'my-folder-gallery' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php echo esc_html__( 'Bordi progetti', 'my-folder-gallery' ); ?>
					</th>
					<td>
						<fieldset>
							<label>
								<input
									name="<?php echo esc_attr( PWG_OPTION_CARD_CORNERS ); ?>"
									type="radio"
									value="square"
									<?php checked( pwg_get_card_corners(), 'square' ); ?>
								>
								<?php echo esc_html__( 'Quadrati', 'my-folder-gallery' ); ?>
							</label>
							<br>
							<label>
								<input
									name="<?php echo esc_attr( PWG_OPTION_CARD_CORNERS ); ?>"
									type="radio"
									value="rounded"
									<?php checked( pwg_get_card_corners(), 'rounded' ); ?>
								>
								<?php echo esc_html__( 'Tondi', 'my-folder-gallery' ); ?>
							</label>
						</fieldset>
						<p class="description">
							<?php echo esc_html__( 'Scegli la forma dei singoli progetti nella vista con tutti i lavori.', 'my-folder-gallery' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
		<hr>
		<h2><?php echo esc_html__( 'Shortcode', 'my-folder-gallery' ); ?></h2>
		<p><?php echo esc_html__( 'Inserisci questo shortcode nella pagina dove vuoi mostrare la gallery:', 'my-folder-gallery' ); ?></p>
		<p class="pwg-shortcode-copy">
			<code id="pwg-shortcode-value">[<?php echo esc_html( PWG_SHORTCODE ); ?>]</code>
			<button type="button" class="button button-secondary pwg-copy-shortcode" data-copy-shortcode="[<?php echo esc_attr( PWG_SHORTCODE ); ?>]">
				<?php echo esc_html__( 'Copia', 'my-folder-gallery' ); ?>
			</button>
			<span class="pwg-copy-feedback" aria-live="polite"></span>
		</p>
		<p class="description">
			<?php
			printf(
				/* translators: %s: shortcode */
				esc_html__( 'Funziona anche se lo scrivi con il trattino lungo: %s.', 'my-folder-gallery' ),
				'[' . esc_html( PWG_SHORTCODE_PRETTY ) . ']'
			);
			?>
		</p>
		<div class="pwg-shortcode-help">
			<p><?php echo esc_html__( 'Il titolo viene preso dalle impostazioni qui sopra.', 'my-folder-gallery' ); ?></p>
			<p>
				<?php echo esc_html__( 'Per cambiare titolo solo in una pagina puoi usare:', 'my-folder-gallery' ); ?>
				<code>[<?php echo esc_html( PWG_SHORTCODE ); ?> title="Titolo personalizzato"]</code>
			</p>
			<p>
				<?php echo esc_html__( 'Per nascondere il titolo solo in una pagina puoi usare:', 'my-folder-gallery' ); ?>
				<code>[<?php echo esc_html( PWG_SHORTCODE ); ?> title=""]</code>
			</p>
		</div>
	</div>
	<?php
}

function pwg_sanitize_base_subdir( string $value ): string {
	$value = trim( wp_unslash( $value ) );
	$value = trim( $value, "/\\ \t\n\r\0\x0B" );
	$parts = array_filter( array_map( 'sanitize_title', preg_split( '#[\/\\\\]+#', $value ) ) );

	return $parts ? implode( '/', $parts ) : 'sportime-lavori';
}

function pwg_get_base_subdir(): string {
	return pwg_sanitize_base_subdir( (string) get_option( PWG_OPTION_BASE_SUBDIR, 'sportime-lavori' ) );
}

function pwg_sanitize_gallery_title( string $value ): string {
	return sanitize_text_field( wp_unslash( $value ) );
}

function pwg_get_gallery_title(): string {
	return pwg_sanitize_gallery_title( (string) get_option( PWG_OPTION_GALLERY_TITLE, 'SELECTED WORKS' ) );
}

function pwg_get_project_title( int $post_id ): string {
	$title = (string) get_post_field( 'post_title', $post_id, 'raw' );
	$title = html_entity_decode( wp_specialchars_decode( $title, ENT_QUOTES ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ?: 'UTF-8' );

	return sanitize_text_field( $title );
}

function pwg_sanitize_accent_color( string $value ): string {
	$value = trim( wp_unslash( $value ) );

	if ( preg_match( '/^#[0-9a-fA-F]{6}$/', $value ) ) {
		return strtolower( $value );
	}

	return '#fbed51';
}

function pwg_get_accent_color(): string {
	return pwg_sanitize_accent_color( (string) get_option( PWG_OPTION_ACCENT_COLOR, '#fbed51' ) );
}

function pwg_sanitize_card_corners( string $value ): string {
	$value = sanitize_key( wp_unslash( $value ) );

	return in_array( $value, array( 'square', 'rounded' ), true ) ? $value : 'square';
}

function pwg_get_card_corners(): string {
	return pwg_sanitize_card_corners( (string) get_option( PWG_OPTION_CARD_CORNERS, 'square' ) );
}

function pwg_get_card_border_radius( string $corners ): string {
	return 'rounded' === pwg_sanitize_card_corners( $corners ) ? '28px' : '0';
}

function pwg_hex_to_rgb( string $hex ): array {
	$hex = ltrim( pwg_sanitize_accent_color( $hex ), '#' );

	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

function pwg_render_accent_style( string $instance_id, string $accent_color, string $card_corners ): string {
	list( $red, $green, $blue ) = pwg_hex_to_rgb( $accent_color );
	$selector = '#' . sanitize_html_class( $instance_id );
	$soft     = sprintf( 'rgba(%d, %d, %d, 0.24)', $red, $green, $blue );
	$nav      = sprintf( 'rgba(%d, %d, %d, 0.9)', $red, $green, $blue );
	$contrast = ( ( $red * 299 + $green * 587 + $blue * 114 ) / 1000 ) > 150 ? '#050505' : '#ffffff';
	$radius   = pwg_get_card_border_radius( $card_corners );

	return sprintf(
		'<style>%1$s .pwg-modal,%1$s .works-modal{--works-pink:%2$s;}%1$s .pwg-card,%1$s .works-card{border-radius:%6$s;}%1$s .works-modal .modal-content{background:radial-gradient(circle at top left,%3$s,transparent 30%%),linear-gradient(135deg,rgba(5,5,5,.98),rgba(25,25,25,.95));}%1$s .works-modal-nav{background:%4$s;color:%5$s;}%1$s .works-modal-close:hover,%1$s .works-modal-close:focus{background:%2$s;border-color:%2$s;color:%5$s;}%1$s .works-modal-dots button.is-active{background:%2$s;}</style>',
		esc_attr( $selector ),
		esc_html( $accent_color ),
		esc_html( $soft ),
		esc_html( $nav ),
		esc_html( $contrast ),
		esc_html( $radius )
	);
}

function pwg_get_base_paths(): array {
	$uploads = wp_upload_dir();
	$subdir  = pwg_get_base_subdir();

	return array(
		'dir' => trailingslashit( $uploads['basedir'] ) . $subdir,
		'url' => trailingslashit( $uploads['baseurl'] ) . $subdir,
	);
}

function pwg_ensure_base_dir(): bool {
	$paths = pwg_get_base_paths();

	return wp_mkdir_p( $paths['dir'] );
}

function pwg_get_allowed_extensions(): array {
	return array( 'jpg', 'jpeg', 'png', 'gif', 'mp4' );
}

function pwg_get_allowed_mimes(): array {
	return array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'gif'          => 'image/gif',
		'mp4|m4v'      => 'video/mp4',
	);
}

function pwg_sanitize_project_folder( string $folder, int $post_id = 0 ): string {
	$folder = trim( wp_unslash( $folder ) );
	$folder = sanitize_title( $folder );

	if ( '' !== $folder ) {
		return $folder;
	}

	$title = $post_id ? get_the_title( $post_id ) : '';
	$slug  = sanitize_title( $title );

	return $slug ? $slug : 'progetto-' . ( $post_id ? absint( $post_id ) : wp_generate_uuid4() );
}

function pwg_get_project_paths( int $post_id ): array {
	$base   = pwg_get_base_paths();
	$folder = pwg_sanitize_project_folder( (string) get_post_meta( $post_id, '_pwg_folder', true ), $post_id );

	return array(
		'folder' => $folder,
		'dir'    => trailingslashit( $base['dir'] ) . $folder,
		'url'    => trailingslashit( $base['url'] ) . rawurlencode( $folder ),
	);
}

function pwg_get_folder_paths( string $folder ): array {
	$base   = pwg_get_base_paths();
	$folder = pwg_sanitize_project_folder( $folder );

	return array(
		'folder' => $folder,
		'dir'    => trailingslashit( $base['dir'] ) . $folder,
		'url'    => trailingslashit( $base['url'] ) . rawurlencode( $folder ),
	);
}

function pwg_is_allowed_file( string $filename ): bool {
	$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	return in_array( $extension, pwg_get_allowed_extensions(), true );
}

function pwg_get_project_files( int $post_id ): array {
	$paths = pwg_get_project_paths( $post_id );

	if ( ! is_dir( $paths['dir'] ) ) {
		return array();
	}

	$files = array();
	foreach ( glob( trailingslashit( $paths['dir'] ) . '*', GLOB_NOSORT ) as $file_path ) {
		if ( ! is_file( $file_path ) ) {
			continue;
		}

		$filename = basename( $file_path );
		if ( ! pwg_is_allowed_file( $filename ) ) {
			continue;
		}

		$files[] = $filename;
	}

	$order = get_post_meta( $post_id, '_pwg_order', true );
	$order = is_array( $order ) ? array_values( array_map( 'basename', $order ) ) : array();
	$rank  = array_flip( $order );

	usort(
		$files,
		static function ( string $a, string $b ) use ( $rank ): int {
			$a_rank = $rank[ $a ] ?? PHP_INT_MAX;
			$b_rank = $rank[ $b ] ?? PHP_INT_MAX;

			if ( $a_rank === $b_rank ) {
				return strnatcasecmp( $a, $b );
			}

			return $a_rank <=> $b_rank;
		}
	);

	return $files;
}

add_action( 'add_meta_boxes', 'pwg_register_meta_boxes' );
function pwg_register_meta_boxes(): void {
	add_meta_box(
		'pwg_project_media',
		__( 'Media progetto', 'my-folder-gallery' ),
		'pwg_render_project_meta_box',
		PWG_POST_TYPE,
		'normal',
		'high'
	);
}

add_action( 'post_edit_form_tag', 'pwg_add_post_form_enctype' );
function pwg_add_post_form_enctype( WP_Post $post ): void {
	if ( PWG_POST_TYPE === $post->post_type ) {
		echo ' enctype="multipart/form-data"';
	}
}

function pwg_render_project_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pwg_save_project_media', 'pwg_project_media_nonce' );

	$paths       = pwg_get_project_paths( $post->ID );
	$folder      = $paths['folder'];
	$files       = pwg_get_project_files( $post->ID );
	$cover       = (string) get_post_meta( $post->ID, '_pwg_cover', true );
	$folder_path = $paths['dir'];
	?>
	<div class="pwg-admin-box">
		<p>
			<label for="pwg_folder"><strong><?php echo esc_html__( 'Cartella progetto', 'my-folder-gallery' ); ?></strong></label>
		</p>
		<input type="text" id="pwg_folder" name="pwg_folder" class="regular-text" value="<?php echo esc_attr( $folder ); ?>">
		<p class="description">
			<?php
			printf(
				/* translators: %s: folder path */
				esc_html__( 'I file saranno salvati in: %s', 'my-folder-gallery' ),
				esc_html( $folder_path )
			);
			?>
		</p>

		<hr>

		<p>
			<label for="pwg_uploads"><strong><?php echo esc_html__( 'Carica foto/video', 'my-folder-gallery' ); ?></strong></label>
		</p>
		<input type="file" id="pwg_uploads" name="pwg_uploads[]" multiple accept=".jpg,.jpeg,.png,.gif,.mp4,image/jpeg,image/png,image/gif,video/mp4">
		<p class="description"><?php echo esc_html__( 'Formati ammessi: JPG, PNG, GIF, MP4.', 'my-folder-gallery' ); ?></p>

		<?php if ( $files ) : ?>
			<hr>
			<div class="pwg-media-toolbar">
				<p><strong><?php echo esc_html__( 'File del progetto', 'my-folder-gallery' ); ?></strong></p>
				<label class="pwg-select-all-delete">
					<input type="checkbox" id="pwg_select_all_delete">
					<?php echo esc_html__( 'Seleziona tutti per eliminarli', 'my-folder-gallery' ); ?>
				</label>
			</div>
			<input type="hidden" name="pwg_order" id="pwg_order" value="<?php echo esc_attr( implode( ',', $files ) ); ?>">
			<ul class="pwg-media-list" id="pwg-media-list">
				<?php foreach ( $files as $index => $filename ) : ?>
					<?php
					$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
					$url       = trailingslashit( $paths['url'] ) . rawurlencode( $filename );
					$is_cover  = $cover ? $cover === $filename : 0 === $index;
					?>
					<li class="pwg-media-item" data-filename="<?php echo esc_attr( $filename ); ?>">
						<span class="dashicons dashicons-menu pwg-media-handle" aria-hidden="true"></span>
						<span class="pwg-media-preview">
							<?php if ( 'mp4' === $extension ) : ?>
								<span class="dashicons dashicons-format-video"></span>
							<?php else : ?>
								<img src="<?php echo esc_url( $url ); ?>" alt="">
							<?php endif; ?>
						</span>
						<span class="pwg-media-name"><?php echo esc_html( $filename ); ?></span>
						<label class="pwg-media-cover">
							<input type="radio" name="pwg_cover" value="<?php echo esc_attr( $filename ); ?>" <?php checked( $is_cover ); ?>>
							<?php echo esc_html__( 'Cover', 'my-folder-gallery' ); ?>
						</label>
						<label class="pwg-media-delete">
							<input type="checkbox" name="pwg_delete_files[]" value="<?php echo esc_attr( $filename ); ?>">
							<?php echo esc_html__( 'Elimina', 'my-folder-gallery' ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="description"><?php echo esc_html__( 'Trascina i file per cambiare ordine. I file selezionati su Elimina saranno rimossi quando salvi/aggiorni il progetto.', 'my-folder-gallery' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'admin_enqueue_scripts', 'pwg_enqueue_admin_assets' );
function pwg_enqueue_admin_assets( string $hook_suffix ): void {
	$screen = get_current_screen();
	if ( ! $screen || PWG_POST_TYPE !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style(
		'pwg-admin',
		PWG_PLUGIN_URL . 'assets/admin.css',
		array(),
		PWG_VERSION
	);

	wp_enqueue_script(
		'pwg-admin',
		PWG_PLUGIN_URL . 'assets/admin.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		PWG_VERSION,
		true
	);
}

add_action( 'save_post_' . PWG_POST_TYPE, 'pwg_save_project_media', 10, 2 );
function pwg_save_project_media( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['pwg_project_media_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pwg_project_media_nonce'] ) ), 'pwg_save_project_media' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$old_folder = pwg_sanitize_project_folder( (string) get_post_meta( $post_id, '_pwg_folder', true ), $post_id );
	$folder     = isset( $_POST['pwg_folder'] ) ? pwg_sanitize_project_folder( sanitize_text_field( wp_unslash( $_POST['pwg_folder'] ) ), $post_id ) : pwg_sanitize_project_folder( '', $post_id );

	pwg_maybe_rename_project_folder( $old_folder, $folder );
	update_post_meta( $post_id, '_pwg_folder', $folder );

	pwg_ensure_base_dir();
	$paths = pwg_get_project_paths( $post_id );
	wp_mkdir_p( $paths['dir'] );

	pwg_delete_selected_files( $post_id );
	pwg_handle_project_uploads( $post_id );

	$current_files = pwg_get_project_files( $post_id );
	$order         = isset( $_POST['pwg_order'] ) ? sanitize_text_field( wp_unslash( $_POST['pwg_order'] ) ) : '';
	$order_files   = array_filter( array_map( 'basename', array_map( 'trim', explode( ',', $order ) ) ) );
	$order_files   = array_values( array_intersect( $order_files, $current_files ) );
	$new_files     = array_values( array_diff( $current_files, $order_files ) );
	$final_order   = array_merge( $order_files, $new_files );

	update_post_meta( $post_id, '_pwg_order', $final_order );

	$cover = isset( $_POST['pwg_cover'] ) ? basename( sanitize_text_field( wp_unslash( $_POST['pwg_cover'] ) ) ) : '';
	if ( ! $cover || ! in_array( $cover, $final_order, true ) ) {
		$cover = $final_order[0] ?? '';
	}

	if ( $cover ) {
		update_post_meta( $post_id, '_pwg_cover', $cover );
	} else {
		delete_post_meta( $post_id, '_pwg_cover' );
	}
}

function pwg_maybe_rename_project_folder( string $old_folder, string $new_folder ): void {
	if ( $old_folder === $new_folder ) {
		return;
	}

	$old_paths = pwg_get_folder_paths( $old_folder );
	$new_paths = pwg_get_folder_paths( $new_folder );

	if ( ! is_dir( $old_paths['dir'] ) || is_dir( $new_paths['dir'] ) ) {
		return;
	}

	wp_mkdir_p( dirname( $new_paths['dir'] ) );
	rename( $old_paths['dir'], $new_paths['dir'] );
}

function pwg_delete_selected_files( int $post_id ): void {
	if ( empty( $_POST['pwg_delete_files'] ) || ! is_array( $_POST['pwg_delete_files'] ) ) {
		return;
	}

	$paths = pwg_get_project_paths( $post_id );

	foreach ( $_POST['pwg_delete_files'] as $raw_filename ) {
		$filename = basename( sanitize_file_name( wp_unslash( $raw_filename ) ) );
		if ( ! pwg_is_allowed_file( $filename ) ) {
			continue;
		}

		$file_path = trailingslashit( $paths['dir'] ) . $filename;
		if ( is_file( $file_path ) ) {
			$attachment_id = pwg_find_attachment_by_project_file( $post_id, $filename );
			if ( $attachment_id ) {
				wp_delete_attachment( $attachment_id, true );
			} else {
				wp_delete_file( $file_path );
			}
		}
	}
}

add_action( 'trashed_post', 'pwg_delete_project_files_on_trash' );
function pwg_delete_project_files_on_trash( int $post_id ): void {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return;
	}

	pwg_delete_project_files_on_delete( $post_id, $post );
}

add_action( 'before_delete_post', 'pwg_delete_project_files_on_delete', 10, 2 );
function pwg_delete_project_files_on_delete( int $post_id, WP_Post $post ): void {
	if ( PWG_POST_TYPE !== $post->post_type ) {
		return;
	}

	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_parent'    => $post_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_pwg_project_file',
		)
	);

	foreach ( $attachments as $attachment_id ) {
		wp_delete_attachment( (int) $attachment_id, true );
	}

	$paths = pwg_get_project_paths( $post_id );
	pwg_delete_project_dir( $paths['dir'] );
}

function pwg_delete_project_dir( string $dir ): void {
	$base = wp_normalize_path( trailingslashit( realpath( pwg_get_base_paths()['dir'] ) ?: pwg_get_base_paths()['dir'] ) );
	$dir  = wp_normalize_path( trailingslashit( realpath( $dir ) ?: $dir ) );

	if ( $base === $dir || 0 !== strpos( $dir, $base ) || ! is_dir( $dir ) ) {
		return;
	}

	$items = scandir( $dir );
	if ( false === $items ) {
		return;
	}

	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}

		$path = trailingslashit( $dir ) . $item;
		if ( is_dir( $path ) ) {
			pwg_delete_project_dir( $path );
		} elseif ( is_file( $path ) ) {
			wp_delete_file( $path );
		}
	}

	rmdir( $dir );
}

function pwg_find_attachment_by_project_file( int $post_id, string $filename ): int {
	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_parent'    => $post_id,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_pwg_project_file',
			'meta_value'     => $filename,
		)
	);

	return $attachments ? (int) $attachments[0] : 0;
}

function pwg_handle_project_uploads( int $post_id ): void {
	if ( empty( $_FILES['pwg_uploads'] ) || empty( $_FILES['pwg_uploads']['name'] ) || ! is_array( $_FILES['pwg_uploads']['name'] ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$files = $_FILES['pwg_uploads'];
	$count = count( $files['name'] );

	for ( $index = 0; $index < $count; $index++ ) {
		if ( empty( $files['name'][ $index ] ) || UPLOAD_ERR_OK !== (int) $files['error'][ $index ] ) {
			continue;
		}

		if ( ! pwg_is_allowed_file( (string) $files['name'][ $index ] ) ) {
			continue;
		}

		$file = array(
			'name'     => sanitize_file_name( $files['name'][ $index ] ),
			'type'     => $files['type'][ $index ],
			'tmp_name' => $files['tmp_name'][ $index ],
			'error'    => $files['error'][ $index ],
			'size'     => $files['size'][ $index ],
		);

		add_filter( 'upload_dir', 'pwg_filter_upload_dir' );
		$upload = wp_handle_upload(
			$file,
			array(
				'test_form' => false,
				'mimes'     => pwg_get_allowed_mimes(),
			)
		);
		remove_filter( 'upload_dir', 'pwg_filter_upload_dir' );

		if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
			continue;
		}

		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => $upload['url'],
				'post_mime_type' => $upload['type'],
				'post_title'     => pathinfo( basename( $upload['file'] ), PATHINFO_FILENAME ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$post_id
		);

		if ( ! is_wp_error( $attachment_id ) ) {
			update_post_meta( $attachment_id, '_pwg_project_file', basename( $upload['file'] ) );
			pwg_update_attachment_metadata_without_image_copies( (int) $attachment_id, $upload['file'], $upload['type'] );
		}
	}
}

function pwg_update_attachment_metadata_without_image_copies( int $attachment_id, string $file, string $mime_type ): void {
	if ( 0 === strpos( $mime_type, 'image/' ) ) {
		update_post_meta( $attachment_id, '_pwg_original_only', '1' );
		return;
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $file );

	if ( $metadata ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}
}

function pwg_filter_upload_dir( array $dirs ): array {
	$post_id = isset( $_POST['post_ID'] ) ? absint( $_POST['post_ID'] ) : 0;

	if ( ! $post_id ) {
		return $dirs;
	}

	$folder = pwg_sanitize_project_folder( (string) get_post_meta( $post_id, '_pwg_folder', true ), $post_id );
	$subdir = '/' . trim( pwg_get_base_subdir(), '/' ) . '/' . $folder;

	$dirs['path']   = trailingslashit( $dirs['basedir'] ) . trim( $subdir, '/' );
	$dirs['url']    = trailingslashit( $dirs['baseurl'] ) . str_replace( '%2F', '/', rawurlencode( trim( $subdir, '/' ) ) );
	$dirs['subdir'] = $subdir;

	wp_mkdir_p( $dirs['path'] );

	return $dirs;
}

add_shortcode( PWG_SHORTCODE, 'pwg_render_shortcode' );
add_shortcode( PWG_SHORTCODE_PRETTY, 'pwg_render_shortcode' );
function pwg_render_shortcode( array $atts = array() ): string {
	$atts = shortcode_atts(
		array(
			'title' => null,
			'limit' => -1,
		),
		$atts,
		PWG_SHORTCODE
	);

	$gallery_title = null === $atts['title'] ? pwg_get_gallery_title() : sanitize_text_field( (string) $atts['title'] );

	$query = new WP_Query(
		array(
			'post_type'      => PWG_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['limit'],
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	$works = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$work = pwg_prepare_work_data( get_the_ID() );
		if ( $work ) {
			$works[] = $work;
		}
	}
	wp_reset_postdata();

	if ( ! $works ) {
		return '';
	}

	pwg_enqueue_frontend_assets();

	$instance_id  = 'pwg-gallery-' . wp_unique_id();
	$accent_color = pwg_get_accent_color();
	$card_corners = pwg_get_card_corners();

	ob_start();
	?>
	<div class="pwg-gallery mx-5 my-5 py-5" id="<?php echo esc_attr( $instance_id ); ?>">
		<?php echo pwg_render_accent_style( $instance_id, $accent_color, $card_corners ); ?>
		<?php if ( '' !== $gallery_title ) : ?>
			<div class="selWorks pwg-heading">
				<h1 class="text-center py-5"><?php echo esc_html( $gallery_title ); ?></h1>
			</div>
		<?php endif; ?>

		<div class="imgBoxCont pwg-grid">
			<?php foreach ( $works as $index => $work ) : ?>
				<?php
				$cover       = $work['cover'];
				$random_size = wp_rand( 240, 450 );
				$box_width   = false !== stripos( $cover['url'], 'Locatelli' ) ? '200px' : $random_size . 'px';
				?>
				<article class="imgbox works-card pwg-card" style="width: <?php echo esc_attr( $box_width ); ?>;" role="button" tabindex="0" data-work-index="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( 'Apri lavoro ' . $work['title'] ); ?>">
					<?php if ( 'video' === $cover['type'] ) : ?>
						<video class="works-card-media pwg-card-media" autoplay loop muted playsinline>
							<source src="<?php echo esc_url( $cover['url'] ); ?>" type="video/mp4">
						</video>
					<?php else : ?>
						<img class="works-card-media pwg-card-media" src="<?php echo esc_url( $cover['url'] ); ?>" alt="<?php echo esc_attr( $work['title'] ); ?>">
					<?php endif; ?>

					<div class="text-hover pwg-card-hover">
						<?php foreach ( $work['title_parts'] as $title_part ) : ?>
							<h6><?php echo esc_html( $title_part ); ?></h6>
						<?php endforeach; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="modal fade works-modal pwg-modal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-fullscreen works-modal-dialog pwg-modal-dialog">
				<div class="modal-content pwg-modal-content">
					<div class="works-modal-topbar pwg-modal-topbar">
						<div>
							<p class="works-modal-kicker pwg-modal-counter">Selected work</p>
							<h2 class="works-modal-title pwg-modal-title"></h2>
						</div>
						<button type="button" class="works-modal-close pwg-modal-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__( 'Chiudi', 'my-folder-gallery' ); ?>">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="works-modal-stage pwg-modal-stage">
						<button type="button" class="works-modal-nav works-modal-prev pwg-modal-nav pwg-modal-prev" aria-label="<?php echo esc_attr__( 'Media precedente', 'my-folder-gallery' ); ?>">
							<span aria-hidden="true">&lsaquo;</span>
						</button>

						<div class="works-modal-media-wrap pwg-modal-media-wrap"></div>

						<button type="button" class="works-modal-nav works-modal-next pwg-modal-nav pwg-modal-next" aria-label="<?php echo esc_attr__( 'Media successivo', 'my-folder-gallery' ); ?>">
							<span aria-hidden="true">&rsaquo;</span>
						</button>
					</div>

					<div class="works-modal-footer pwg-modal-footer">
						<p class="pwg-modal-caption"></p>
						<div class="works-modal-dots pwg-modal-dots" aria-label="<?php echo esc_attr__( 'Navigazione media', 'my-folder-gallery' ); ?>"></div>
					</div>
				</div>
			</div>
		</div>

		<script type="application/json" class="pwg-data"><?php echo wp_json_encode( $works ); ?></script>
	</div>
	<?php

	return ob_get_clean();
}

function pwg_prepare_work_data( int $post_id ): ?array {
	$files = pwg_get_project_files( $post_id );
	if ( ! $files ) {
		return null;
	}

	$paths = pwg_get_project_paths( $post_id );
	$media = array();
	$title = pwg_get_project_title( $post_id );

	foreach ( $files as $filename ) {
		$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		$media[] = array(
			'url'  => trailingslashit( $paths['url'] ) . rawurlencode( $filename ),
			'type' => 'mp4' === $extension ? 'video' : 'image',
			'alt'  => $title,
		);
	}

	$cover_filename = (string) get_post_meta( $post_id, '_pwg_cover', true );
	$cover_index    = array_search( $cover_filename, $files, true );
	$cover          = false === $cover_index ? $media[0] : $media[ $cover_index ];

	return array(
		'id'          => $post_id,
		'cover'       => $cover,
		'media'       => $media,
		'title'       => $title,
		'title_parts' => array_map( 'trim', preg_split( '/\s*(?:-|\x{2013}|\x{2014})\s*/u', $title ) ),
	);
}

add_action( 'wp_enqueue_scripts', 'pwg_enqueue_frontend_assets' );
function pwg_enqueue_frontend_assets(): void {
	wp_enqueue_style(
		'pwg-frontend',
		PWG_PLUGIN_URL . 'assets/frontend.css',
		array(),
		PWG_VERSION
	);

	wp_enqueue_script(
		'pwg-frontend',
		PWG_PLUGIN_URL . 'assets/frontend.js',
		array(),
		PWG_VERSION,
		true
	);
}

add_filter( 'manage_' . PWG_POST_TYPE . '_posts_columns', 'pwg_add_admin_columns' );
function pwg_add_admin_columns( array $columns ): array {
	$columns['pwg_folder'] = __( 'Cartella', 'my-folder-gallery' );
	$columns['pwg_files']  = __( 'File', 'my-folder-gallery' );

	return $columns;
}

add_action( 'manage_' . PWG_POST_TYPE . '_posts_custom_column', 'pwg_render_admin_columns', 10, 2 );
function pwg_render_admin_columns( string $column, int $post_id ): void {
	if ( 'pwg_folder' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_pwg_folder', true ) );
	}

	if ( 'pwg_files' === $column ) {
		echo esc_html( (string) count( pwg_get_project_files( $post_id ) ) );
	}
}
