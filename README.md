# DK1 Theme Shell

Republished **2026-09-20** as `dk1-theme-shell`.

Publication prepared with **ChatGPT** for Daniel Kam.

The shared app-shell block theme for app-to-WordPress ports, built on WPDS Canvas.

A block theme whose global styles **are** the WordPress Design System tokens.

The mockup blocks in `wpds-blocks` compose `@wordpress/ui` primitives in the
editor, and those primitives read their values from `--wpds-*` custom
properties. The theme around them was saying something else: a content theme's
type scale, a content theme's palette, a content theme's spacing. So a mockup
looked like the design system in the middle and like a blog everywhere else.

This theme closes that gap from the theme side. Every preset it publishes is a
WPDS token, so a heading, a button, a link, an input and a card agree whether
they come from a core block or from a primitive.

## The one idea

WordPress 7.1 ships the design tokens itself, at
`wp-includes/css/dist/theme/design-tokens.css`, registered as the **`wp-theme`**
style handle. That handle is already a dependency of `wp-edit-blocks`, so the
editor canvas iframe already has the tokens. The front end never enqueues it.

So the theme does not vendor a token file. It enqueues **core's own handle** on
the front end (`functions.php`) and states the same values as presets in
`theme.json`. Both documents then resolve one set of numbers, and a WordPress
update moves both at once.

```
core design-tokens.css ──┬── canvas iframe   (core already loads it)
                         └── front end       (functions.php enqueues wp-theme)
                                 │
                         theme.json presets  (the same values, as literals)
```

### Why literals in theme.json rather than `var(--wpds-…)`

A preset value of `var(--wpds-color-background-surface-neutral)` works on the
page and breaks the editing UI: the color picker, the palette swatches and the
size controls sit in the admin document, outside the canvas, where the tokens
are not defined, and they render the raw string rather than the color.

Literals keep the UI honest and put the maintenance burden on a script instead.
`tools/token-map.json` records the token every literal came from, and
`npm run check` compares the two against the file WordPress is serving. Run it
after any WordPress update.

```bash
npm run check
```

It was proven by breaking it: a color moved by one digit, an `h1` line height
set to 1.3, and an unmapped preset added, all three reported by name.

## What maps to what

**Type.** The pairs are `@wordpress/ui`'s `Text` variants, read off the built
component CSS, not guessed.

| Role | Variant | Size | Line height |
| --- | --- | --- | --- |
| body | `body-md` | 13px | 20px |
| `h1` | `heading-2xl` | 32px | 40px |
| `h2` | `heading-xl` | 20px | 24px |
| `h3` | `heading-lg` | 15px | 20px |
| `h4`, `h5` | `heading-md` | 13px | 20px |
| `h6` | `heading-sm` | 11px | 16px, uppercase |
| caption | `body-sm` | 12px | 16px |

Font size presets are the raw scale: `x-small` 11, `small` 12, `medium` 13,
`large` 15, `x-large` 20, `xx-large` 32. Line heights are stated as unitless
ratios so they survive a font size override; the gate checks each one against
its px pair.

**Spacing.** `--wpds-dimension-padding-*` and `--wpds-dimension-gap-*`:

| Preset | 10 | 20 | 30 | 40 | 50 | 60 | 70 | 80 |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| px | 4 | 8 | 12 | 16 | 24 | 32 | 40 | 48 |

Only `80` is not a token, and `tools/token-map.json` says so in `$unmapped`.

This scale also fixes something in the plugin. `wpds-blocks` maps its private
spacing onto the theme's preset scale (`--wpds-space-s: var(--wp--preset--spacing--20)`,
`-m: …--40`, `-l: …--50`), and its own comment records that it wanted
`s = m/2` but settled for the nearest rung the previous theme published, 7px.
Under this theme those three resolve to **8 / 16 / 24**, which is the rhythm the
plugin was aiming at.

**Color.** Surfaces, content, strokes and the four status tones, straight from
`--wpds-color-*`. `base` `#fcfcfc` / `contrast` `#1e1e1e` / `stroke` `#dbdbdb` /
`brand` `#3858e9`. Three presets serve sites rather than mockups: `brand-content`,
the block plugin's `--wpds-color-foreground-content-brand` (eyebrows, block titles,
marks); `accent`, a core-block preset with no token behind it, for a brand's
second colour as a rule or an eyebrow on ink; and `chrome`, also a core-block
preset, for a header or footer bar distinct from `neutral-strong`'s dark-ink
and dark-button duty. All three equal `brand` until a child theme binds them. Core's default palette, gradients and duotones are off: a
mockup should not be able to reach for a color the design system does not have.

**Layout.** `contentSize` 720px and `wideSize` 960px are
`--wpds-dimension-surface-width-xl` and `-2xl`.

**Shadows.** None. WPDS has no shadow token; it separates surfaces with strokes.
`defaultPresets` is off so core's set cannot stand in for one.

## Files

| Path | What it holds |
| --- | --- |
| `theme.json` | Presets, elements, block styles. Every literal is a token. |
| `assets/css/wpds-canvas.css` | Only what theme.json cannot say: box models, form controls, the focus ring, the `@wordpress/components` accent shim. Front end and canvas both. |
| `assets/css/wpds-admin-page.css` | The admin page shell, ported from `@wordpress/admin-ui`'s `Page`. |
| `assets/css/wpds-app-sidebar.css` | The app shell sidebar, built to the wpds-app-grammar reference. |
| `functions.php` | Enqueues core's `wp-theme` handle on the front end, and both stylesheets into the canvas. |
| `templates/`, `parts/` | Two shells: the admin page (`page.html` + `parts/admin-header.html`) and the content document (`index`, `single`, `content-page` + `parts/header.html`). |
| `patterns/token-preview.php` | Every type role, control and surface on one page. |
| `patterns/app-sidebar.php` | The wpds-app shell on a Blank canvas, composing the part below. |
| `patterns/sidebar-*.php` | The composable sections: switcher, section, tip, disclosure. |
| `patterns/app-page-long.php` | A page long enough to scroll several viewports, for testing the shell. |
| `parts/app-sidebar.html` | The sidebar itself, rendered by `page.html`, `index.html` and the pattern. |
| `parts/admin-header-index.html` | The index's header: site title instead of post title. |
| `assets/js/app-shell.js` | The theme's only script: collapsing the sidebar. |
| `tools/` | The drift gate. |

### The admin page shell, and why the title is 15px

`page.html` renders the design system's **admin page**, not a document. It is a
block-markup port of `@wordpress/admin-ui`'s `Page` component (v2.8.0), read off
`src/page/style.module.css` and `src/page/header.tsx`. That package exists
precisely to "guarantee consistency in the common page structure of an admin
page layout", and it is what new WordPress admin surfaces are built from. The
PHP-era `.wrap` / `.page-title-action` chrome is not the reference.

| Element | Spec | Token |
| --- | --- | --- |
| Page | `#fcfcfc` on `#1e1e1e` | `background-surface-neutral`, `foreground-content-neutral` |
| Header | 16px / 24px padding, sticky, `#fff` | `padding-lg`, `padding-2xl`, `surface-neutral-strong` |
| Header rule | 1px `#f0f0f0` | `stroke-surface-neutral-**weak**`, not the `#dbdbdb` a content border uses |
| Header row | min-height 32px, gap 8px, space-between | `size-md`, `gap-sm` |
| **Title** | **15px / 20px / 600**, truncates | `Text variant="heading-lg"` as `h1` |
| Subtitle | 13px / 20px `#707070`, 4px below | `body-md`, `foreground-content-neutral-weak` |
| Content | 16px / 24px padding, full width, no measure | `padding-lg`, `padding-2xl` |

The title is the surprising one. An admin page title is `heading-lg`, 15px at
weight 600 — a *quarter* the size of this theme's document `h1`. Admin pages are
dense; a 32px title is a content-site gesture. The header part sets it with the
`large` preset, whose class carries `!important`, so it overrides the global
`core/post-title` rule without a stylesheet override.

`Page` also carries a column `Stack` with **no** default gap: nothing separates
the title row from the subtitle except the subtitle's own 4px. The theme's 16px
`blockGap` would have invented a gap there, so the header group pins it to 0.

The header actions are `wpds/button`, the action control in the WPDS Blocks
plugin, not core buttons: `admin-ui` composes its header actions from
`@wordpress/ui`'s Button, and that block is the same control with the same
tokens and the real `variant` / `tone` / `size` props. The primary action is
solid brand and the secondary one is outline neutral, which is the pairing
`Page` shows in its own stories.

That is the theme's one plugin dependency, and it is scoped to this template
part. With WPDS Blocks deactivated the part still renders: the saved markup is
a plain `<button type="button">` and WordPress emits it verbatim, unstyled,
rather than breaking the page. Swap the two blocks for core buttons if you want
the theme standalone.

Use **Content page** when a page wants the document language instead: the
constrained 720px measure, the 32px `h1` and the site header and footer.

### The blank canvas template

Assign **Blank canvas** to a page and the shell disappears: no header, no
footer, no root padding, no constrained width. The post content is the whole
document, which is what a full-screen app mockup wants. Everything else keeps
the shell so the site still reads as a site.

## The mark

`assets/logo.svg` is the theme's own thesis at 64 units: a brand field, a
header band carrying a title and one primary action, and the paper surface
below it. Nothing in it is decorative and no colour was chosen — `#3858e9` is
`wpds-color-background-interactive-brand-strong`, `#ffffff` is
`wpds-color-background-surface-neutral-strong`, `#dbdbdb` is
`wpds-color-stroke-surface-neutral`. It was drawn to survive 16px: the content
rules and the title bar fade out at favicon size and the silhouette carries it,
which is a blue band over a white sheet, which is what the theme puts on
screen. Checked at 16 / 24 / 32 / 64 / 128 on both light and dark grounds.

It lands in two places. `assets/logo-512.png` is the rasterised mark, set as
the site icon (the browser-tab placeholder). And `screenshot.png` is the theme
tile: not a mocked-up brand card but a real 1200x900 render of the theme
serving a page built from the `wpds-blocks/dashboard-shell` and
`wpds-blocks/settings-form` patterns. Regenerate it by publishing a page from
those two patterns and photographing it at 1200x900; the page itself is
disposable.

One caution if you edit the SVG: the token names in its comment are written
without their leading double hyphen, because `--` is illegal inside an XML
comment and putting it back makes the file fail to parse as an image. It fails
silently — the browser reports `complete: true` with `naturalWidth: 0` — so it
looks like a broken path rather than a broken file.

## Two shells, and the root padding

theme.json's root padding lands on `.wp-site-blocks`, an ancestor no template
can reach from the inside. That is right for a document and wrong for both of
the app shells, and each one was measurably broken by it:

- the admin page floated 32px down, leaving its *sticky* header stranded below
  the top of the viewport the moment you scrolled
- the blank canvas, whose whole promise is that the content is the document,
  opened with a 32px band above and below it

Both are cancelled at the root with `:has()`, scoped to the shell that needs
it, and the content shell still keeps its 32px. `:has()` rather than a body
class because `page.html` is the DEFAULT page template and WordPress gives it
no class of its own, so the selector names the thing it actually depends on.
Both roots are listed, since the front end scrolls `.wp-site-blocks` and the
editor canvas `.is-root-container`.

## The app sidebar

**Every template gets it.** `page`, `index`, `single`, `archive`, `search` and
`404` are all the whole shell: the `app-sidebar` template part, then a main
column carrying an admin page header and the content. They differ in exactly
one line, the title block, because `core/post-title` has no queried post
outside a loop: `admin-header` renders the post title, `admin-header-index` the
site title with the tagline as its subtitle, and `admin-header-archive` and
`admin-header-search` a `core/query-title` of the right type. The three
variants are generated from `admin-header-index` rather than written out, so
the chrome cannot drift between them.

**Content page** and **Blank canvas** remain the two ways out: the document
shell with its 720px measure, and no shell at all.

**It renders in the Site Editor too**, because template parts and the theme's
editor styles both reach the canvas (verified: the canvas receives the sidebar,
shell and toggle rules). One difference, and it is the right one: the collapse
script is front-end only, so the canvas never gets `wpds-js`, the toggle stays
hidden and the sidebar always edits expanded. You cannot accidentally leave a
template collapsed for the next editor. The sidebar is a template part rather than markup in the template, so
`patterns/app-sidebar.php` composes the same part and editing it in the Site
Editor changes every surface at once. Use **Content page** for a page that
wants no shell at all.

It is built to the **wpds-app-grammar** reference rather than to the older
`wpds-sidebar` theme.
Where the two disagree the reference wins: it specifies grid rows and a
numbered journey navigation, the theme predates it and uses flex with a search
box.

The parts that are contract rather than taste, all verified in the browser:

| Rule | How it is met |
| --- | --- |
| White surfaces; grey is not a family shell background | `surface-neutral-strong`, separated by a stroke rather than a tint |
| Grid rows `auto minmax(0, 1fr) auto` | measured `53px 438px 151px` |
| Only the body scrolls; the sidebar never does | body scrolled to 238 while header top stayed `0` and footer bottom stayed `420` |
| The main area owns page scrolling | shell pinned to `100vh` with `overflow: hidden`, main column `overflow-y: auto` |
| Active item never uses an inline-start border or an inset stripe | background + weight + `aria-current`; measured `border-left: 0px`, `box-shadow: none` |
| Scrollbar hidden until hover or focus, visible on touch | `scrollbar-width: none`, restored on `:hover`/`:focus-within` and under `@media (hover: none)` |
| Identity is one control carrying mark and title | single `<a>` with an accessible name |
| 36px rounded-square app mark, 28px circular avatar | both drawn in CSS, see below |
| Assistant stack below the nav, never merged into it | Ask, then Help & Feedback, then App settings, then the tip |
| Documentation trio travels together | Changelog, README, API Reference in one footer row |

### What the default sidebar carries

It ships populated rather than sparse, at roughly Lingo's density, so a mockup
does not start from a blank column: a workspace switcher, a labelled journey
navigation, a labelled tools section with icons, the assistant and support
stack, a tip card, two disclosures, and the profile footer with documentation
links and a version. Hairlines separate the groups, because once the body
scrolls, spacing alone stops reading as a boundary.

The same pieces remain available as individual patterns for building a
different sidebar, and both come from the same CSS.

### Composable sections

The sidebar in a real app is assembled, not authored once, so the pieces ship
as their own patterns. Modelled on Lingo, which is the family app this theme
mocks up:

| Pattern | What it is |
| --- | --- |
| **Sidebar context switcher** | The reference's `context_switcher`: a labelled workspace or entity trigger. |
| **Sidebar section** | A labelled group of icon rows, one carrying `aria-current`. |
| **Sidebar tip card** | The `ai-tip-card`: kind, guidance, and a pair of actions. |
| **Sidebar disclosure** | A real `<details>`, so the trigger has native expanded state and no script. |

**The icons are Dashicons, and that closes a gap I previously wrote down as
open.** The reference wants `@wordpress/icons` on utilities, which is React SVG
a static theme cannot render, and inline SVG is stripped by kses. Dashicons is
a core-registered icon FONT, so a row carries `class="dashicons dashicons-tag"`
and nothing else: no markup to strip, no build step, no missing glyph. It is
not the family icon set, and it is the one a static mockup can keep on screen.
The theme enqueues the core handle on the front end and in the canvas, because
a row that edits as a blank square is worse than one that ships as a blank
square.

**The switcher is a button, not a `<select>`.** kses removes `<select>` and
`<option>` too, and the first version rendered as its own option text in a row:
"French · Français Spanish · Español Japanese · 日本語", with no control. That
is the same failure as the SVG marks, and it is worth stating as a rule for
this theme rather than a fact about one pattern: **a pattern may only use
markup kses keeps.** A button with `aria-haspopup` is also closer to the
reference, whose control here is `custom-popover-select`.

### Resizing it

The sidebar has the reference's `left-panel-resize-handle`: a transparent 16px
full-height rail straddling the divider, carrying a 4x32 grip that stays inside
the panel and is invisible until its own border is hovered, its own separator
focused, or its own drag active. Range 240 to 400, default 304, step 16.

It is a real `role="separator"` with `aria-orientation`, a label, and live
`aria-valuemin` / `max` / `now`; Arrow keys step, Home and End jump to the
bounds.

**Where is it?** Invisible until you want it, twice over, and the first of
those is deliberate in both this theme and the app it copies.

The grip sits at `opacity: 0` and fades in over 120ms when its own rail is
hovered, its separator focused, or its drag active. That is the reference's
rule, and Lingo implements it identically: a transparent 16px rail at
`right: -8px`, a `4x32` pill at `border-radius: 999px` and `opacity: 0`, going
to `1` on `:hover`, `:focus-visible` and `.is-dragging`. So you find it by
hovering the divider, in both.

If you would rather it were discoverable at rest, that is a one-line change and
a deliberate deviation from both the reference and the app: give the grip a
low resting opacity instead of zero. It has not been made, because the point of
this theme is to look like the apps rather than to improve on them quietly.

The rail is also hidden until `app-shell.js` adds `wpds-js`, so it does not
appear in the Site Editor at all: the script is front-end only, a handle there
could never drag, and a drag handle that does not drag is worse than none. Measured: 304 to 320 to 336 by arrow, End to 400 and clamped, Home to
240 and clamped, and a pointer drag to 352 surviving a reload as a rendered
352.

Four details are contract rather than taste.

**The rail is an overlay, not a child.** The sidebar is a three-row grid, and a
fourth child in flow would take an implicit row and push the footer out. It is
absolutely positioned, so it claims no column, no row and no flow space.

**Interpolation is off while resizing.** The transition that makes collapsing
feel deliberate would deliver the border, the grip and the panel edge on
different frames under the pointer. Measured during a drag: `transition-duration
0s`.

**The width is state, not a measurement.** Reading it back off the element
looks equivalent and is not, because `inline-size` is transitioned and
`getBoundingClientRect` returns an intermediate value mid-animation. Stepping
from that makes each arrow press start wherever the last one had got to. This
was caught exactly the way the reference warns: `aria-valuenow` climbed
correctly while the property resolved to 360px on the element and its computed
`inline-size` still read 304px, one frame in. **Test the rendered width, not
the attribute.**

**Resize and collapse are separate preferences**, stored separately. Collapsing
hides the handle, because a 72px rail has nothing to resize, and it leaves the
width preference untouched for when you expand again.

### Three ways this got the width wrong

Worth keeping, because each one looked right and measured wrong.

**A reserved gutter of nothing.** Hiding the scrollbar until hover reflowed the
sidebar under the pointer, and `scrollbar-gutter: stable` did not fix it: a
gutter is reserved at the width of the current scrollbar, and a scrollbar of
`none` is zero wide. The measurement said so and was read as a pass, because
`offsetWidth 303 - clientWidth 303 = 0` is not "reserved", it is "reserved
nothing". The fix holds `scrollbar-width: thin` at all times and colours the
THUMB transparent, so the appearance is the same and there is no layout in it.
Now 11px, reserved at rest.

**A rail where a drawer belonged.** The desktop rail's `inline-size: 72px` and
the mobile drawer's `inline-size: 0` have identical specificity, so whichever
came last won, and the rail was later in the file. A narrow window got 72px of
icons that, being `position: fixed`, reserved no space, and the page content ran
underneath them clipped along its left edge. Now behind a `min-width: 783px`
query, so the state is declared rather than inherited from source order.

**A floor that did not come down.** Zeroing the drawer's `inline-size` left it
at 240px, because the base rule keeps `min-inline-size: 240px` so a resized
sidebar can never collapse below its range. The old collapse rule had zeroed
both; when it became a rail behind a breakpoint, mobile lost the half it
needed.

### Collapsing it

The toggle sits first in the admin page header, where `admin-ui` puts its own
`SidebarToggleSlot`. It is a real button with `aria-expanded` and
`aria-controls`, and `assets/js/app-shell.js` is the theme's only script.

Four behaviours, each measured rather than assumed:

- **No flash.** The script loads in the head and is deliberately not deferred,
  because the stored state has to reach `<html>` before first paint. Deferred,
  a reader who left it collapsed watches the sidebar render open and snap shut.
- **No dead control.** The toggle is hidden by CSS until the script adds
  `wpds-js`. Without JavaScript you get an expanded sidebar and no button that
  does nothing.
- **Nothing invisible in the tab order.** A closed mobile drawer takes
  `visibility: hidden` on its children, so its links leave the tab order
  instead of becoming invisible focus stops. Verified: a link in a closed
  drawer refuses `.focus()`. The opener is the one exception, set back to
  visible and fixed to the viewport, because a drawer whose only restore
  control is inside itself cannot be reopened.
- **The preference does not leak across breakpoints.** Below 783px the sidebar
  is a fixed drawer that opens closed, and the stored desktop value is never
  read there, which the reference requires by name. The converse leak is closed
  too: toggling the drawer does not write the desktop preference, because
  opening a drawer is an interaction and collapsing a sidebar is a choice.

**Collapsed is a 72px rail, matching Lingo**, not a closed drawer. It closed to
nothing in an earlier pass for a stated reason, that a rail is icons and this
theme had none; the section patterns brought Dashicons and removed that
obstacle, so the rail is now what it should have been. It keeps the app mark,
the journey ordinals, the three utility glyphs and the avatar, and drops the
labels, the switcher, the tip and the disclosures.

Two details in there are contract rather than taste. Labels are **clipped**,
never `display: none`, because the reference requires every control in a rail
to keep an accessible name and `display: none` takes the name out of the
accessibility tree with the pixels; measured, a collapsed journey row still
reads "01 Overview Where everything stands today" and still takes focus. And
the three actions collapse with `font-size: 0` rather than a clip, because the
label is the button's own text node with no element to clip: the text stays in
the tree, the pseudo element sets its own size, and the component's
`min-width: calc(4ch + ...)` collapses to nothing, which is what lets a 40px
control fit a 72px rail.

**The control lives in the sidebar header**, where Lingo puts it, not in the
page header. That also deleted a duplicate: it previously had to be copied into
both page-header parts, and there is now one copy, inside the thing it
controls.

On mobile the sidebar was originally a stacked column, which pushed the page
header and content about 1300px down the document before anything readable
appeared. That is what made it a drawer.

Two things worth knowing before you edit it.

**The marks are CSS, not SVG, and that is deliberate.** A pattern's markup goes
through `wp_kses` for any user without `unfiltered_html`, and kses strips
`<svg>` outright. The first version used inline SVG and the app mark, the
chevrons and the avatar all vanished on insert, leaving a text-only header that
looked like a styling bug. A letter on a token background and a border-drawn
chevron cannot be stripped, and read as the placeholders they are.

**The journey rows must never get leading icons.** The reference forbids them
on numbered journey rows, child tools and documentation links, because numbers
already identify journey order. Icons belong on the identity, on stable places
like Overview and Search, and on the deterministic utilities, which is exactly
where the section patterns put them. `wpds/button` renders a text label
only, and inventing SVG icons here would hit the same kses problem. This is a
known deviation rather than an oversight; closing it properly means an icon
affordance on the block, not markup around it.

The shell pins itself to the viewport, so drop it on a page using the **Blank
canvas** template. It cancels the theme's root padding the same way the other
two shells do.

## The canvas is not a page

The Site Editor gets the other implementation of the same promise, and it has
to. On the front end the shell is pinned to the viewport and the main column
scrolls inside it; in the canvas that produced two faults at once. The editor's
own root spacing sits above and below a 100vh box, so the canvas document ended
up taller than its iframe and scrolled, and when it scrolled it carried the
sidebar with it. The sidebar was then the one thing on screen that should not
move and did.

In the canvas the shell stops being a fixed-height box and the sidebar becomes
`position: sticky`, which is WordPress's own mechanism for staying put while
the thing beside you scrolls, and the one the block editor already understands.
Editing keeps normal document scrolling and the sidebar still holds. The
editor's root spacing is cancelled for the shell separately, because
`.wp-site-blocks` does not exist in the canvas and the front-end rule cannot
reach it.

Verified against a replica of the canvas DOM rather than the live editor, since
that needs a login this theme's author does not have: root spacing 32px and
16px both cancelled to zero, shell height auto, sidebar `sticky` at 100vh, main
column no longer its own scroll region, and the sidebar's top holding at 0
after scrolling 900px. The real canvas may add spacing this replica does not,
so it is worth a glance in the editor.

## Testing the shell

Insert **App page, long** on a page and scroll it. The contract is three
things, and only one of them was right the first time:

```
main column          scrolls        1052 of 1813, viewport 760
sidebar              top 0 -> 0     stays
page header          top 0 -> 0     sticky
document             does not scroll
sidebar body         scrolls independently of the main column
```

The page header failing that test is the reason the pattern exists. A template
part renders inside a wrapper element exactly as tall as the part, and
`position: sticky` is bounded by its containing block, so the header had
nowhere to travel and scrolled away with the content: measured, it went from
top 0 to top **-1052** while the sidebar correctly held at 0. Nothing shorter
than a scrolling page would have shown it. The wrapper is now
`display: contents` in the main column, and the templates stopped asking for
`tagName: "header"` on it, since the part's own root is already a `<header>`.

The copy is generic product writing rather than lorem on purpose: real
sentence lengths break differently from Latin filler, and a measure that reads
well in lorem can read badly in English.

## What WordPress already provides, and what it does not

Two things in this theme look like candidates for existing block settings, and
they come out differently.

**`position: fixed` is real and I had it switched off.** Core's position
support reads `settings.position.fixed` as well as `settings.position.sticky`,
and this theme only enabled sticky, which is why a Group block offered
Default and Sticky and nothing else. It is on now, so the control offers Fixed
where an author wants it. The shell itself still uses flex plus a viewport
height rather than a fixed sidebar, because fixed positioning takes the sidebar
out of flow and the main column then needs a hand-maintained offset to avoid
sitting underneath it. Available, deliberately unused.

**The admin bar height is core's, not ours.** `admin-bar.css` publishes
`--wp-admin--admin-bar--height` at 32px and 46px at its own breakpoint, and
core's sticky positioning reads that same variable. The first version of the
shell offset restated 32/46 in a media query of our own, which is a second copy
of a number WordPress already maintains. It now reads the variable.

## Deliberate omissions

- **No `wp-block-styles` support.** Core's opinionated layer restyles quotes,
  separators, buttons and captions with values that are not WPDS tokens.
  Everything it would have given is stated in `theme.json` instead.
- **`defaultFontSizes` is off**, so the legacy core slugs (`has-huge-font-size`,
  `has-regular-font-size`, `has-larger-font-size`) resolve to nothing and the
  element inherits 13px. If you run the plugin's `tests/m12-no-js.js` against
  this theme, its `has-huge-font-size` assertion is measuring a slug this theme
  does not publish; give the paragraph `has-xx-large-font-size` instead.
- **Light only.** `design-tokens.css` carries no `prefers-color-scheme` block
  today, so the theme declares `color-scheme: light` rather than letting the UA
  paint form controls dark and disagree with the canvas.
- **No fonts bundled.** The WPDS stack is the system stack.

## Verified

On `ci-uiplayground` (WordPress 7.1, PHP 8.3), front end, computed styles, not
class names:

```
body            13px / 20px, #fcfcfc on #1e1e1e, system stack
h1              32px / 40px / 600      h2   20px / 24px / 600
h3              15px / 20px            h6   11px / 16px / uppercase
button          #3858e9, #eff0f2, radius 2px, min-height 40px, 12px inline
input           min-height 32px, radius 2px, 1px #8d8d8d
link            #3858e9
table head      1px #dbdbdb   (core's 3px #1e1e1e rule restated)
root gutter     24px, content 720px
```

The admin page shell, measured against admin-ui's own values:

```
page            #fcfcfc
header          padding 16px 24px, #fff, border-bottom 1px #f0f0f0, sticky, row-gap 0
header row      min-height 32px, gap 8px, center, space-between
title           15px / 20px / 600, nowrap, margin 0
subtitle        13px / 20px, #707070, padding-bottom 4px
content         padding 16px 24px, max-width none
```

The canvas half was verified server side: `get_block_editor_settings()` carries
both the generated global styles and this theme's editor stylesheet, and
`_wp_get_iframed_editor_assets()` carries `theme/design-tokens.min.css`.

## Where the design language came from

Found through the Automattic Field Guide, then read at the source:

- [Design System](https://fieldguide.automattic.com/design-handbook/how-we-design/design-system/)
  (Field Guide) names the packages and links every library.
- [system.automattic.design](https://system.automattic.design/) is the hub, and
  the component **Library** there carries each component's status.
- Gutenberg's Storybook is the engineering reference: the
  [Admin UI section](https://wordpress.github.io/gutenberg/?path=/docs/admin-ui-page--docs)
  is the page language this theme ports, and
  [DataViews](https://wordpress.github.io/gutenberg/?path=/docs/dataviews-dataviews--best-practices)
  is the one for tables, lists and grids of records.
- Figma: `@wordpress/ui`, `@wordpress/theme` and `@wordpress/admin-ui` libraries.
- [Best practices for building UIs using WordPress UI components](https://fieldguide.automattic.com/automatticians-and-the-wordpress-community/developer-guide-for-working-in-the-wordpress-org-community/best-practices-for-building-uis-using-wordpress-ui-components/)
  is the rule set: follow the specs, do not override a component's *internal*
  styles, and never target its class names or DOM. This theme obeys it by
  reading admin-ui's tokens and restating them on its own markup, rather than
  reaching into any component.
- `#design-systems` is where to ask.

There is also a design-system MCP server, which serves the stable component list
and the token set to an agent:

```bash
npx -y @wordpress/design-system-mcp@next
```

It does not know about admin-ui or DataViews yet, so the page-level language
still has to be read from Storybook.

## Switching away

```bash
php -r '$_SERVER["HTTP_HOST"]="localhost:8884"; define("WP_USE_THEMES", false); require "./wp-load.php"; switch_theme("twentytwentyfive");'
```

Or Appearance, Themes, Activate. Nothing outside the theme directory was
changed to install it.

### The `heading` font family

The theme registers `heading` beside `body` and `mono`, bound to `--wpds-typography-font-family-heading`, and `styles.elements.heading` reads it. It equals the body stack until a site binds a display face there: a child theme restates the slug with its own `fontFamily` and `fontFace` (asiamannequin's Nunito over a system body was the first) instead of inventing a slug or overriding every heading element by hand.
