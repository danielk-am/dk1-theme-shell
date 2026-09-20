<?php
/**
 * Title: App page, long
 * Slug: wpds-canvas/app-page-long
 * Categories: page, text
 * Description: A full app page with enough copy to scroll several viewports, for testing that the shell holds: the sidebar stays put, the page header stays put, and only the main column moves.
 *
 * Generic product copy rather than lorem, so the type scale is legible while
 * you scroll it: real sentence lengths break differently from Latin filler,
 * and a measure that reads well in lorem can read badly in English.
 *
 * @package wpds-canvas
 */

?>
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Everything in one place, and nothing you have to keep in your head. This page is the state of the work: what came in, what is moving, and what is waiting on somebody.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What this page is for</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Most tools answer the question you asked. This one tries to answer the question you were about to ask. The counts above the fold are the ones people check first thing; everything below is the detail you go looking for when a number surprises you.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Nothing here is computed on a schedule. Every figure is read at the moment you load the page, so a number that looks wrong is a number that is wrong, not a number that is stale. If something disagrees with a source, the source wins and this page is the bug.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How the work moves</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">It arrives</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Work enters from a connected source or from someone typing it in. Both paths end in the same place and neither is privileged: an item created by hand is not a second-class item, and an imported one carries no special authority just because a machine put it there.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">It gets shaped</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Shaping is the part people spend real time in, so it is the part that has to survive interruption. Every edit is saved as you make it. Leaving the page mid-sentence is a supported way to use this app, not an accident it recovers from.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Grouping is deliberately loose. An item can sit in several groups at once, and a group with one item in it is a perfectly reasonable group. The structure is there to help you find things later, not to be correct.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">It goes out</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Publishing is the only step that touches anything outside this app, so it is the only step that asks twice. Everything before it is reversible; everything after it is somebody else's copy.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Status, and what each one means</h2>
<!-- /wp:heading -->

<!-- wp:table -->
<figure class="wp-block-table"><table><thead><tr><th>Status</th><th>What it means</th><th>Who moves it</th></tr></thead><tbody><tr><td>Draft</td><td>Yours alone. Nothing has been sent anywhere.</td><td>You</td></tr><tr><td>In review</td><td>Someone has been asked to look. They have not yet.</td><td>The reviewer</td></tr><tr><td>Needs changes</td><td>Looked at, and something came back.</td><td>You</td></tr><tr><td>Scheduled</td><td>Accepted, waiting for its time.</td><td>Nobody, until then</td></tr><tr><td>Published</td><td>Out. Changes from here make a new version.</td><td>You, deliberately</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Conventions worth knowing</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>A number with no unit is a count. A number with a unit is a measurement, and measurements say when they were taken.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Anything you can undo says so before you do it. Anything you cannot says so twice.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Empty states describe what will fill them, not the fact that they are empty.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Errors name the thing that failed and the next action. An error that only apologises is a bug.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Nothing auto-refreshes under your cursor. Fresh data waits behind a control you press.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Questions that come up</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Why does the count differ from the source?</strong> Usually because the source counts things this app deliberately ignores: duplicates, items removed after import, and anything still in a draft that was never sent. The detail view lists exactly what was skipped and why.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Can two people work on the same item?</strong> Yes, and the app will not stop you. It shows you who else has it open and what they changed, and the last write wins. If that is not good enough for your team, split the item.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Where does my data live?</strong> On the machine you are reading this on, unless you connected a source that says otherwise. Nothing leaves without a step you took.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>The best state for a tool is the one where you forget it is there and get on with the work.</p>
<!-- /wp:paragraph --></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">If you are automating this</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every view on this page is a read of the same API the interface uses. There is no private endpoint and no shortcut that the interface takes and you cannot.</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>GET /api/items?status=in-review&amp;limit=50</code></pre>
<!-- /wp:code -->

<!-- wp:paragraph -->
<p>Responses are paginated and stable: an item that has not changed will come back byte for byte on the next call, so a diff between two reads is a real diff and not formatting noise.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What is coming</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Two things are being worked on and neither is finished, so neither is promised. The first is a proper history view, so you can see what an item looked like before the change you are about to undo. The second is the ability to work while disconnected and reconcile afterwards, which is easy to demonstrate and hard to get right.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Things that were considered and dropped are listed in the changelog with the reason, because a feature that was rejected for a good reason should not need rejecting twice.</p>
<!-- /wp:paragraph -->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"fontSize":"small","textColor":"contrast-weak"} -->
<p class="has-contrast-weak-color has-text-color has-small-font-size">Last read a moment ago. Nothing on this page is cached.</p>
<!-- /wp:paragraph -->
