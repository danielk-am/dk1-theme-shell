<?php
/**
 * Title: App sidebar
 * Slug: wpds-canvas/app-sidebar
 * Categories: page, featured
 * Block Types: core/group
 * Description: The wpds-app shell sidebar: fixed identity header, a scrolling body with numbered journeys and the assistant and support stack, and a fixed footer with the editable profile row, documentation links and version. Ships inside a full-height shell row with a content column, so it stands up as a mockup on its own.
 *
 * The sidebar itself is the app-sidebar TEMPLATE PART, which page.html also
 * renders, so there is one copy of it and editing it in the Site Editor
 * changes every surface that shows it. This pattern exists for mockups that
 * want the shell on a Blank canvas page rather than the theme's default.
 *
 * Built to the wpds-app-grammar shell contract. The parts that are contract
 * rather than taste are noted where they appear, because an editor moving
 * things around is exactly who needs to know which ones are load-bearing:
 * region order, the assistant stack sitting below the navigation and never
 * merged into it, the documentation trio travelling together, and the active
 * item carrying aria-current rather than a colour alone.
 *
 * The three actions are wpds/button from the WPDS Blocks plugin, the same
 * control admin-ui composes its own actions from. Without that plugin they
 * degrade to plain buttons, styled but unthemed, rather than breaking.
 *
 * @package wpds-canvas
 */

?>
<!-- wp:group {"className":"wpds-app-shell","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"stretch"}} -->
<div class="wp-block-group wpds-app-shell"><!-- wp:template-part {"slug":"app-sidebar"} /-->

<!-- wp:group {"tagName":"main","className":"wpds-app-shell__main","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
<main class="wp-block-group wpds-app-shell__main" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"level":1,"fontSize":"large","style":{"typography":{"fontWeight":"600","lineHeight":"1.333333"}}} -->
<h1 class="wp-block-heading has-large-font-size" style="font-weight:600;line-height:1.333333">Overview</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"contrast-weak"} -->
<p class="has-contrast-weak-color has-text-color">The main area owns the page scroll. Replace this column with the mockup.</p>
<!-- /wp:paragraph --></main>
<!-- /wp:group --></div>
<!-- /wp:group -->
