<?php
/**
 * Title: Token preview
 * Slug: wpds-canvas/token-preview
 * Categories: text
 * Description: Every type role, control and surface the theme states, on one page. Insert it to see what the tokens resolve to, or to check the canvas against the front end after a WordPress update.
 *
 * @package wpds-canvas
 */
?>
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Heading one, 32/40</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Body copy at 13px on a 20px line, the WPDS body-md pairing. A <a href="#">link</a> takes the brand token, and <code>inline code</code> takes the mono stack.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Heading two, 20/24</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Heading three, 15/20</h3>
<!-- /wp:heading -->

<!-- wp:heading {"level":6} -->
<h6 class="wp-block-heading">Heading six, uppercase 11/16</h6>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Primary</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">Secondary</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>A quote sits behind a four pixel stroke.</p>
<!-- /wp:paragraph --></blockquote>
<!-- /wp:quote -->

<!-- wp:code -->
<pre class="wp-block-code"><code>npm run check</code></pre>
<!-- /wp:code -->

<!-- wp:table -->
<figure class="wp-block-table"><table><thead><tr><th>Token</th><th>Value</th></tr></thead><tbody><tr><td>font-size-md</td><td>13px</td></tr><tr><td>border-radius-sm</td><td>2px</td></tr><tr><td>dimension-size-lg</td><td>40px</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:search {"label":"Search","showLabel":false,"buttonText":"Search"} /-->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->
