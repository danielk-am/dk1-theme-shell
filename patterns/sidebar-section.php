<?php
/**
 * Title: Sidebar section
 * Slug: wpds-canvas/sidebar-section
 * Categories: page
 * Description: A labelled group of icon navigation rows for the sidebar body: wpds/sidebar-section holding wpds/sidebar-nav-item rows. The active row carries aria-current and takes background and weight, never an inline-start stripe.
 *
 * Real blocks, no Custom HTML: every part of a row is a control in the
 * inspector, and the icon is a Dashicons name the block turns into a glyph.
 *
 * @package wpds-canvas
 */

?>
<!-- wp:wpds/sidebar-section {"label":"Tools"} -->
<section class="wp-block-wpds-sidebar-section wpds-sidebar-section wpds-sidebar-section--ruled"><h2 class="wpds-sidebar-section__label">Tools</h2><div class="wpds-sidebar-section__items"><!-- wp:wpds/sidebar-nav-item {"icon":"image-rotate","isActive":true} -->
<a class="wp-block-wpds-sidebar-nav-item wpds-sidebar-nav-item" data-icon="image-rotate" href="#" aria-current="page"><span class="wpds-sidebar-nav-item__icon dashicons dashicons-image-rotate" aria-hidden="true"></span><div class="wpds-sidebar-nav-item__text"><!-- wp:paragraph {"className":"wpds-sidebar-nav-item__label"} -->
<p class="wpds-sidebar-nav-item__label">Review</p>
<!-- /wp:paragraph --></div></a>
<!-- /wp:wpds/sidebar-nav-item -->

<!-- wp:wpds/sidebar-nav-item {"icon":"microphone"} -->
<a class="wp-block-wpds-sidebar-nav-item wpds-sidebar-nav-item" data-icon="microphone" href="#"><span class="wpds-sidebar-nav-item__icon dashicons dashicons-microphone" aria-hidden="true"></span><div class="wpds-sidebar-nav-item__text"><!-- wp:paragraph {"className":"wpds-sidebar-nav-item__label"} -->
<p class="wpds-sidebar-nav-item__label">Practice</p>
<!-- /wp:paragraph --></div></a>
<!-- /wp:wpds/sidebar-nav-item -->

<!-- wp:wpds/sidebar-nav-item {"icon":"translation"} -->
<a class="wp-block-wpds-sidebar-nav-item wpds-sidebar-nav-item" data-icon="translation" href="#"><span class="wpds-sidebar-nav-item__icon dashicons dashicons-translation" aria-hidden="true"></span><div class="wpds-sidebar-nav-item__text"><!-- wp:paragraph {"className":"wpds-sidebar-nav-item__label"} -->
<p class="wpds-sidebar-nav-item__label">Translate</p>
<!-- /wp:paragraph --></div></a>
<!-- /wp:wpds/sidebar-nav-item --></div></section>
<!-- /wp:wpds/sidebar-section -->
