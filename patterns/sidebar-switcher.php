<?php
/**
 * Title: Sidebar workspace switcher
 * Slug: wpds-canvas/sidebar-switcher
 * Categories: page
 * Description: The context chooser at the top of the sidebar body: a labelled section holding wpds/context-switcher, the select-shaped block (swapped 2026-08-27 from the button-styled-as-a-chooser it used to carry; the block draws its own caret in CSS, so there is still no markup kses could strip). Markup is the block's executed save.
 *
 * @package wpds-canvas
 */

?>
<!-- wp:wpds/sidebar-section {"label":"Workspace","ruled":false} -->
<section class="wp-block-wpds-sidebar-section wpds-sidebar-section wpds-sidebar-section--flush"><h2 class="wpds-sidebar-section__label">Workspace</h2><div class="wpds-sidebar-section__items"><!-- wp:wpds/context-switcher {"label":"Personal workspace"} -->
<button class="wp-block-wpds-context-switcher wpds-context-switcher wpds-context-switcher--one-line" type="button" aria-haspopup="listbox" aria-expanded="false"><span class="wpds-context-switcher__text"><span class="wpds-context-switcher__label">Personal workspace</span></span><span class="wpds-context-switcher__caret" aria-hidden="true"></span></button>
<!-- /wp:wpds/context-switcher --></div></section>
<!-- /wp:wpds/sidebar-section -->
