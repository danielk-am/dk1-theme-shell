/**
 * App chrome the CSS cannot express: the mobile drawer's ways out, and the
 * profile menu.
 *
 * Both are progressive enhancements over markup that already works. The drawer
 * is opened and closed by the sidebar block; this only adds Escape and
 * tap-outside. The profile row is a real link to the app's settings page; this
 * turns it into a menu trigger and takes the settings destination from the menu
 * item that carries the same href. With this file absent, the row still links
 * to settings and the menu renders as plain visible rows -- sign-out stays
 * reachable either way.
 */
( function () {
	'use strict';

	/* ------------------------------------------------------- mobile drawer */

	var toggle = document.querySelector( '.wpds-app-sidebar__collapse' );

	// Open means: below the drawer breakpoint AND not collapsed. Above it the
	// sidebar is a column, where Escape and a click on the canvas mean nothing.
	function isDrawerOpen() {
		return (
			!! toggle &&
			! document.documentElement.classList.contains( 'wpds-sidebar-collapsed' ) &&
			window.matchMedia( '( max-width: 782px )' ).matches
		);
	}

	if ( toggle ) {
		var main = document.querySelector( '.wpds-app-shell__main' );

		if ( main ) {
			// Capture phase: the scrim is a pseudo-element of this column, so
			// the click lands on whatever is underneath it. Taking it on the
			// way down closes the drawer before the page acts on a link the
			// veil is covering.
			main.addEventListener(
				'click',
				function ( event ) {
					if ( isDrawerOpen() ) {
						event.preventDefault();
						event.stopPropagation();
						toggle.click();
					}
				},
				true
			);
		}
	}

	/* ------------------------------------------------------- profile menu */

	var group = document.querySelector( '[data-wpds-profile-menu]' );
	var trigger = group && group.querySelector( '.wpds-profile-control' );
	var panel = group && group.querySelector( '.wpds-profile-menu__panel' );

	function menuIsOpen() {
		return !! group && group.classList.contains( 'is-open' );
	}

	function closeMenu( restoreFocus ) {
		if ( ! menuIsOpen() ) {
			return;
		}
		group.classList.remove( 'is-open' );
		trigger.setAttribute( 'aria-expanded', 'false' );
		if ( restoreFocus ) {
			trigger.focus();
		}
	}

	function openMenu() {
		group.classList.add( 'is-open' );
		trigger.setAttribute( 'aria-expanded', 'true' );
		var first = panel.querySelector( '.wpds-profile-menu__item' );
		if ( first ) {
			first.focus();
		}
	}

	if ( group && trigger && panel ) {
		// Announce the row as a menu button only now that a script is here to
		// make it behave like one. Without this file it stays a plain link,
		// and saying otherwise in the markup would be a lie to a screen reader.
		trigger.setAttribute( 'aria-haspopup', 'menu' );
		trigger.setAttribute( 'aria-expanded', 'false' );

		trigger.addEventListener( 'click', function ( event ) {
			// The row keeps its href for the no-JS case, so the navigation it
			// would otherwise do has to be stopped here. Settings lives in the
			// menu instead, taken from this same href.
			event.preventDefault();
			if ( menuIsOpen() ) {
				closeMenu( false );
			} else {
				openMenu();
			}
		} );

		// Choosing anything closes the menu. Not preventDefault: the item is
		// either a real link or the assistant toggle, and both must still act.
		panel.addEventListener( 'click', function () {
			closeMenu( false );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( menuIsOpen() && ! group.contains( event.target ) ) {
				closeMenu( false );
			}
		} );
	}

	/* --------------------------------------------- assistant starts closed */

	/*
	 * The assistant opens itself. The plugin's own `initiallyOpen` config -- set
	 * from functions.php -- is the right lever and is already in place, but the
	 * builds these apps actually ship predate it: the vendored ai-agent-block
	 * has no config-driven open state at all, so the filter is correct and
	 * inert. Verified by reading the served bundle, which never mentions the
	 * key. When those builds catch up the filter takes over and this does
	 * nothing, because it only ever acts on an assistant it finds already open.
	 *
	 * What the shipped build DOES have is a delegated click listener on
	 * `[data-wpds-toggles="assistant"], .wpds-open-assistant a, a.wpds-open-assistant`
	 * that toggles the panel. So one synthetic click on a throwaway element
	 * closes it, using the block's own public affordance rather than reaching
	 * into its internals or reproducing its state.
	 *
	 * Every load starts closed, deliberately: Daniel asked for it open only
	 * when clicked. The profile menu carries "Ask the assistant" on any site
	 * whose sidebar has no trigger of its own, so there is always a way back.
	 */
	var assistantSettled = false;

	/*
	 * The chat bubble (Daniel, 2026-09-01): ChatHost renders nothing while
	 * closed, so the page needs a way back in that survives a folded
	 * sidebar. A fixed bubble wearing the block's own public trigger
	 * attribute -- the chat script keeps ownership of open state; the
	 * theme's stylesheet shows the bubble only while the panel is off
	 * screen. Injected only once the chat script is actually present
	 * (window.wpdsAiChat, or its ready event), so it can never be a dead
	 * button.
	 */
	var fabSettled = false;

	function injectAssistantFab() {
		if ( fabSettled ) {
			return;
		}
		if ( ! document.querySelector( '.wpds-app-shell__assistant' ) ) {
			// No assistant column on this page; nothing to summon.
			fabSettled = true;
			return;
		}
		if ( ! window.wpdsAiChat ) {
			return;
		}
		fabSettled = true;

		var fab = document.createElement( 'button' );
		fab.type = 'button';
		fab.className = 'wpds-assistant-fab';
		fab.setAttribute( 'data-wpds-toggles', 'assistant' );
		fab.setAttribute( 'aria-haspopup', 'dialog' );
		fab.setAttribute( 'aria-label', 'Open the assistant' );

		var icon = document.createElement( 'span' );
		icon.className = 'dashicons dashicons-format-chat';
		icon.setAttribute( 'aria-hidden', 'true' );
		fab.appendChild( icon );

		// A summon from the bubble is the reader's own decision, so the
		// close-on-load below must stand down for good the moment it is
		// used: the chat fires its ready event when the panel first MOUNTS,
		// which on a closed-at-boot page is the moment the reader opens it,
		// and an armed closeAssistantOnce would shut it in their face.
		fab.addEventListener( 'click', function () {
			assistantSettled = true;
		} );

		document.body.appendChild( fab );
	}

	window.addEventListener( 'wpds-ai-chat-ready', injectAssistantFab );

	function closeAssistantOnce() {
		if ( assistantSettled ) {
			return;
		}
		if ( ! document.documentElement.classList.contains( 'wpds-assistant-open' ) ) {
			return;
		}
		assistantSettled = true;

		var proxy = document.createElement( 'button' );
		proxy.type = 'button';
		proxy.setAttribute( 'data-wpds-toggles', 'assistant' );
		// Out of the layout and out of the tree the moment it has done its job.
		proxy.setAttribute( 'aria-hidden', 'true' );
		proxy.tabIndex = -1;
		proxy.style.cssText = 'position:fixed;inset-block-start:-9999px;inline-size:1px;block-size:1px;opacity:0;pointer-events:none';
		document.body.appendChild( proxy );
		proxy.click();
		proxy.remove();
	}

	window.addEventListener( 'wpds-ai-chat-ready', closeAssistantOnce );

	// The ready event may already have fired before this file ran, so also
	// check on the next few frames rather than relying on the event alone.
	// The bubble rides the same poll: both settle independently.
	( function pollForAssistant( attempt ) {
		if ( ( assistantSettled && fabSettled ) || attempt > 40 ) {
			/*
			 * The load window is over: whatever state the assistant is in
			 * now is the settled load state, and close-on-load must never
			 * fire again. Without this, a page whose assistant boots CLOSED
			 * (initiallyOpen honoured, or a phone) leaves closeAssistantOnce
			 * armed forever, and the chat's ready event -- which fires when
			 * the panel first mounts, i.e. the moment the reader opens it --
			 * would close it in their face. Measured on the mobile bubble:
			 * open, ready, closed, all inside one click.
			 */
			assistantSettled = true;
			return;
		}
		closeAssistantOnce();
		injectAssistantFab();
		window.setTimeout( function () {
			pollForAssistant( attempt + 1 );
		}, 50 );
	}( 0 ) );

	/* --------------------------------------------- one Escape for both */

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}
		// The menu is the innermost thing open, so it goes first; only if it
		// was not open does Escape reach the drawer behind it.
		if ( menuIsOpen() ) {
			closeMenu( true );
			return;
		}
		if ( isDrawerOpen() ) {
			toggle.click();
			// Focus follows the drawer out, or it is left on a hidden element.
			toggle.focus();
		}
	} );
}() );
