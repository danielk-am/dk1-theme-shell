<?php
/**
 * WPDS Canvas theme setup.
 *
 * The theme's whole job is to make the two documents a mockup lives in, the
 * editor canvas and the public page, resolve the SAME design tokens that
 * @wordpress/ui and @wordpress/components resolve. There is no vendored copy
 * of those tokens here: WordPress 7.1 registers them itself as the `wp-theme`
 * style handle (wp-includes/css/dist/theme/design-tokens.css), which is
 * already a dependency of `wp-edit-blocks` and therefore already inside the
 * editor iframe. The only gap is the front end, and that is what this file
 * closes.
 *
 * @package wpds-canvas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core handle that carries the official WPDS custom properties.
 */
const WPDS_CANVAS_TOKENS_HANDLE = 'wp-theme';

add_action(
	'after_setup_theme',
	function () {
		// Required for add_editor_style() to reach the canvas iframe.
		add_theme_support( 'editor-styles' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'style', 'script', 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

		/*
		 * Deliberately NOT add_theme_support( 'wp-block-styles' ).
		 * That opt-in loads wp-block-library-theme, core's opinionated layer,
		 * which restyles quotes, separators, buttons and captions with values
		 * that are not WPDS tokens. Everything it would provide is stated here
		 * in theme.json instead, from the token file, so there is one voice.
		 */

		/*
		 * The canvas iframe already has the tokens (wp-edit-blocks depends on
		 * wp-theme), so this only has to add the layer theme.json cannot
		 * express: the box model, form controls and the focus ring.
		 */
		// The canvas needs the icon font too, or a row edits as a blank square.
		add_action( 'enqueue_block_assets', function () {
			if ( is_admin() ) {
				wp_enqueue_style( 'dashicons' );
			}
		} );

		add_editor_style(
			array(
				'assets/css/wpds-canvas.css',
				'assets/css/wpds-admin-page.css',
				'assets/css/wpds-app-sidebar.css',
			)
		);
	}
);


add_action(
	'wp_enqueue_scripts',
	function () {
		$deps = array();

		/*
		 * Dashicons: a core icon FONT, which is the one icon system a static
		 * mockup can keep on screen. @wordpress/icons is React SVG a theme
		 * cannot render, and inline SVG is stripped by kses for any user
		 * without unfiltered_html. A row carries a class and nothing else.
		 */
		wp_enqueue_style( 'dashicons' );

		/*
		 * WP 7.1+ registers the token file for every context, front end
		 * included; it is simply never enqueued out here. Enqueueing the core
		 * handle rather than shipping a copy means the theme cannot drift from
		 * the tokens the components in the editor are resolving.
		 */
		if ( wp_style_is( WPDS_CANVAS_TOKENS_HANDLE, 'registered' ) ) {
			wp_enqueue_style( WPDS_CANVAS_TOKENS_HANDLE );
			$deps[] = WPDS_CANVAS_TOKENS_HANDLE;
		}

		// Ordered: the base layer first, the admin page shell on top of it.
		$sheets = array(
			'wpds-canvas'     => 'assets/css/wpds-canvas.css',
			'wpds-admin-page' => 'assets/css/wpds-admin-page.css',
			'wpds-app-sidebar' => 'assets/css/wpds-app-sidebar.css',
		);

		/*
		 * The sidebar's collapse and resize behaviour ships with the
		 * wpds/app-sidebar block (WPDS Blocks plugin), so it survives a theme
		 * switch. The shell's flush/framed toggle is GONE (Daniel, 2026-08-27:
		 * "Remove the Framed layout badge"); the .wpds-shell-framed layout
		 * rules remain as the design hook, no longer visitor chrome.
		 */
		foreach ( $sheets as $handle => $relative ) {
			$path = get_theme_file_path( $relative );

			wp_enqueue_style(
				$handle,
				get_theme_file_uri( $relative ),
				$deps,
				file_exists( $path ) ? (string) filemtime( $path ) : false
			);

			// Each sheet depends on the one before it, so the cascade is stated.
			$deps[] = $handle;
		}
	}
);


/*
 * ---------------------------------------------------------------- app chrome
 *
 * These four apps are applications, not blogs. Everything below exists because
 * the WordPress front end still dresses them as a site.
 */

/*
 * The admin bar is for administrators only (Daniel, 2026-09-03).
 *
 * Members and every other role see the app as an app: no toolbar, so nothing
 * of WordPress's dressing sits above the shell. An administrator keeps the
 * bar as break-glass access to wp-admin, the same exemption
 * app-admin-access.php grants them on the back end, and the profile-level
 * "Show Toolbar" preference is still honoured ($show carries it).
 *
 * is_admin_bar_showing() returns true for is_admin() BEFORE this filter is
 * consulted, so wp-admin keeps its toolbar regardless and only the app is
 * affected. When the filter returns false _wp_admin_bar_init() bails, so
 * admin-bar.css/js are never enqueued, there is no handle for core to hang
 * its 32px/46px html bump on, and body_class() drops `admin-bar` with it.
 *
 * When it returns true, the shell is NOT hidden behind the bar:
 * wpds-app-sidebar.css maps `.admin-bar` onto --canvas-shell-offset, read
 * from core's own --wp-admin--admin-bar--height, so the shell's height, the
 * sticky sidebar and assistant columns, and the mobile drawer all start
 * below the bar. With the class gone the offset falls back to its declared
 * 0px everywhere, and the drawer and its opener sit at the top of the screen.
 */
add_filter(
	'show_admin_bar',
	function ( $show ) {
		return current_user_can( 'manage_options' ) ? (bool) $show : false;
	}
);

/*
 * Hiding the bar takes the only sign-out with it.
 *
 * The sidebar's profile control is a link to the app's own settings page, not
 * a logout, so before this the only way out of a signed-in app was to type
 * /wp-login.php?action=logout by hand. Since the household id.danielk.am gate
 * came off these sites on 2026-08-30, WordPress's own session is the ONLY
 * authentication, which makes a reachable sign-out non-optional.
 *
 * It goes in a menu hung off the profile row, which is where an app puts it.
 * The row STAYS an anchor to the settings page and keeps its href: with no
 * JavaScript it still behaves exactly as it does today, and the menu below it
 * renders as plain visible rows, so sign-out is reachable either way. app-shell.js
 * is what turns it into a popover -- it adds the trigger semantics and hides
 * the panel, and the CSS only hides the panel under .wpds-js. Nothing here
 * depends on a script having run.
 *
 * Injected at render rather than authored into parts/app-sidebar.html because
 * that file is inert on every one of these sites: each carries a saved
 * wp_template_part row named app-sidebar, and a stored part beats the theme
 * file unconditionally. Verified on all four productions 2026-08-30.
 *
 * Spliced before the LAST </footer> so it lands inside footer.wpds-sidebar-footer
 * as a direct child, which is what the collapsed-rail rules act on. lingo
 * renders its footer dynamically from lingo/sidebar-footer rather than saving
 * the tag in post_content, but it emits the same footer.wpds-sidebar-footer at
 * render time, so splicing on the RENDERED output is footer-block agnostic
 * where a render_block_wpds/sidebar-footer filter would have silently no-opped
 * on lingo.
 */
add_filter(
	'render_block_wpds/app-sidebar',
	function ( $content ) {
		/*
		 * Front end, signed in, never inside a REST payload: the menu carries a
		 * per-user logout nonce and has no business being serialized into an
		 * API response.
		 */
		if ( is_admin() || ! is_user_logged_in() ) {
			return $content;
		}
		if ( function_exists( 'wp_is_serving_rest_request' ) && wp_is_serving_rest_request() ) {
			return $content;
		}

		$items = '';

		/*
		 * Only where the sidebar has no Ask control of its own. The assistant
		 * renders nothing at all while closed -- no launcher, no floating
		 * button -- so on a site without a trigger a closed panel cannot be
		 * reopened without a reload. travel and lingo carry
		 * data-wpds-toggles="assistant" in their saved sidebar; prosper and
		 * spark do not (verified against all four production databases,
		 * 2026-08-30 -- and note the Studio twins disagree with production
		 * here, so this is tested on the live markup, not from a list).
		 */
		if ( false === strpos( $content, 'data-wpds-toggles="assistant"' ) ) {
			$items .= '<button type="button" class="wpds-profile-menu__item" role="menuitem" data-wpds-toggles="assistant">'
				. '<span class="wpds-profile-menu__icon dashicons dashicons-admin-comments" aria-hidden="true"></span>'
				. esc_html__( 'Ask the assistant', 'app-shell' )
				. '</button>';
		}

		$items .= sprintf(
			'<a class="wpds-profile-menu__item" role="menuitem" href="%s">'
			. '<span class="wpds-profile-menu__icon dashicons dashicons-exit" aria-hidden="true"></span>%s</a>',
			esc_url( wp_logout_url( home_url( '/' ) ) ),
			esc_html__( 'Sign out', 'app-shell' )
		);

		$panel = '<div class="wpds-profile-menu__panel" role="menu">' . $items . '</div>';

		/*
		 * Wrap the profile control and its new panel in one positioned parent,
		 * so the panel can be anchored to the row rather than to the footer.
		 * The anchor itself is left completely untouched -- its classes, its
		 * href and its inner markup are the block's, and profile-control's box
		 * is element-qualified as a.wpds-profile-control, so turning it into a
		 * button here would have cost it every bit of its styling.
		 */
		$anchor = '<a class="wp-block-wpds-profile-control wpds-profile-control"';
		$start  = strpos( $content, $anchor );

		if ( false !== $start ) {
			$end = strpos( $content, '</a>', $start );

			if ( false !== $end ) {
				$end += 4;
				$row  = substr( $content, $start, $end - $start );

				/*
				 * Once the row opens a menu, its own destination has to move
				 * INTO that menu or it becomes unreachable for anyone with
				 * JavaScript. Taken from the row's own href rather than
				 * hardcoded, because every app points it somewhere different
				 * and lingo builds it dynamically.
				 */
				$settings = '';
				if ( preg_match( '/\shref="([^"]*)"/', $row, $m ) ) {
					$destination = html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' );

					if ( '' !== $destination && '#' !== $destination ) {
						$settings = sprintf(
							'<a class="wpds-profile-menu__item" role="menuitem" href="%s">'
							. '<span class="wpds-profile-menu__icon dashicons dashicons-admin-generic" aria-hidden="true"></span>%s</a>',
							esc_url( $destination ),
							esc_html__( 'Settings', 'app-shell' )
						);
					}
				}

				$group = '<div class="wpds-profile-menu" data-wpds-profile-menu>'
					. $row
					. str_replace( '<div class="wpds-profile-menu__panel" role="menu">', '<div class="wpds-profile-menu__panel" role="menu">' . $settings, $panel )
					. '</div>';

				return substr( $content, 0, $start ) . $group . substr( $content, $end );
			}
		}

		/*
		 * No profile row to hang it off (a port that dropped it, or a footer
		 * block that renders one some other way). The menu still has to exist,
		 * so fall back to dropping it into the footer on its own.
		 */
		$pos = strrpos( $content, '</footer>' );

		if ( false === $pos ) {
			$pos = strrpos( $content, '</aside>' );
		}

		return false === $pos
			? $content
			: substr( $content, 0, $pos ) . '<div class="wpds-profile-menu wpds-profile-menu--orphan" data-wpds-profile-menu>' . $panel . '</div>' . substr( $content, $pos );
	}
);

/*
 * Escape and scrim-click close the mobile drawer. A pseudo-element scrim
 * cannot take a listener and Escape had no owner, so this is the one piece
 * that cannot be CSS. It drives the block's OWN button rather than toggling
 * the class, so the block stays the single owner of the state and keeps
 * aria-expanded in sync.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$relative = 'assets/js/app-shell.js';
		$path     = get_theme_file_path( $relative );

		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_enqueue_script(
			'app-shell-chrome',
			get_theme_file_uri( $relative ),
			array(),
			(string) filemtime( $path ),
			true
		);
	}
);

/*
 * The assistant starts CLOSED.
 *
 * ChatHost's default is open: `initiallyOpen` is true unless the localized
 * config carries the key with one of false/''/0/'0'. That meant every app
 * opened with a chat panel already occupying a third of the screen -- and on a
 * phone it was worse than that, since the panel is a fixed-width resizable box
 * and simply overflowed the viewport.
 *
 * Set through the plugin's own `wpds_ai_agent_client_config` filter, which runs
 * immediately before wp_localize_script. The alternative -- an inline script
 * hoping to land after the localized data block -- depends on print order and
 * fails silently when it loses.
 *
 * THE PRECONDITION, and it is not optional: ChatHost returns null while closed
 * -- no launcher, no floating button, nothing. A site with no way to reopen the
 * panel would lose the assistant entirely until a reload. Every one of these
 * apps has a trigger: travel and lingo carry data-wpds-toggles="assistant" in
 * their saved sidebar, and the profile menu above adds one on any site that
 * does not. Do not set this on a site without checking that.
 */
add_filter(
	'wpds_ai_agent_client_config',
	function ( $config ) {
		if ( is_array( $config ) ) {
			// '0' rather than false: wp_localize_script casts values to
			// strings, and '0' survives that intact while reading as intent.
			$config['initiallyOpen'] = '0';
		}

		return $config;
	}
);
