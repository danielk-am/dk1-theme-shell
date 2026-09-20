#!/usr/bin/env node
/*
 * The drift gate.
 *
 * This theme states WPDS token VALUES as literals in theme.json, because a
 * var() reference in a preset does not survive the editor's color and size
 * pickers (they render the raw string, and the sidebar sits outside the canvas
 * document where the tokens live). Literals are the right call for the UI and
 * the wrong call for maintenance, so the maintenance half is automated here:
 * every literal declares its token in tools/token-map.json, and this script
 * compares it against the token file WordPress itself is serving.
 *
 * It fails when core's tokens move, when a preset is added without saying
 * where it comes from, and when the stylesheet references a --wpds-* name that
 * does not exist. Run it after any WordPress update.
 *
 *   node tools/check-tokens.mjs [ --tokens=<path to design-tokens.css> ]
 */

import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join, relative } from 'node:path';

const here = dirname( fileURLToPath( import.meta.url ) );
const themeDir = join( here, '..' );

const tokensArgs = process.argv.filter( ( a ) => a.startsWith( '--tokens=' ) ).map( ( a ) => a.slice( '--tokens='.length ) );
const tokensPath =
	tokensArgs[ 0 ] ||
	process.env.WPDS_TOKENS ||
	// wp-content/themes/<theme>/tools -> the WordPress root is four levels up.
	join( here, '..', '..', '..', '..', 'wp-includes', 'css', 'dist', 'theme', 'design-tokens.css' );
/*
 * The block plugin defines a few names core does not (the content brand, the
 * appearance scopes' extras). A preset may mirror one of those, so its token
 * file is read after core's when it is installed beside the theme, or named
 * with a second --tokens=. Core's values win where both declare a name.
 */
const extraTokensPaths = tokensArgs.slice( 1 );
const pluginTokens = join( here, '..', '..', '..', 'plugins', 'dk1-blocks-wordpress-ui', 'assets', 'design-tokens.css' );
if ( extraTokensPaths.length === 0 && existsSync( pluginTokens ) ) {
	extraTokensPaths.push( pluginTokens );
}

const errors = [];
const notes = [];

function read( path, what ) {
	try {
		return readFileSync( path, 'utf8' );
	} catch ( e ) {
		console.error( `Cannot read ${ what }: ${ path }\n${ e.message }` );
		process.exit( 2 );
	}
}

/* ---------------------------------------------------------------- tokens */

const tokensCss = read( tokensPath, 'the design token file' );
const tokens = new Map();
for ( const [ , name, value ] of tokensCss.matchAll( /(--wpds-[a-z0-9-]+)\s*:\s*([^;}]+)/g ) ) {
	// The file redeclares the radius scale per corner-radius mode; the :root
	// block comes first, and that is the one the presets are cut from.
	if ( ! tokens.has( name ) ) {
		tokens.set( name, value.trim().replace( /\s+/g, ' ' ) );
	}
}
if ( tokens.size === 0 ) {
	console.error( `No --wpds-* tokens found in ${ tokensPath }` );
	process.exit( 2 );
}
for ( const extra of extraTokensPaths ) {
	const css = read( extra, 'an extra token file' );
	for ( const [ , name, value ] of css.matchAll( /(--wpds-[a-z0-9-]+)\s*:\s*([^;}]+)/g ) ) {
		if ( ! tokens.has( name ) ) {
			tokens.set( name, value.trim().replace( /\s+/g, ' ' ) );
		}
	}
	notes.push( `extra tokens read from ${ relative( themeDir, extra ) }` );
}

/* ------------------------------------------------------------- normalize */

const normalizeColor = ( v ) => {
	const m = /^#([0-9a-f])([0-9a-f])([0-9a-f])$/i.exec( v );
	return m ? `#${ m[ 1 ] }${ m[ 1 ] }${ m[ 2 ] }${ m[ 2 ] }${ m[ 3 ] }${ m[ 3 ] }` : v;
};

const normalize = ( v ) =>
	normalizeColor(
		String( v )
			.trim()
			.toLowerCase()
			.replace( /["']/g, '"' ) // core quotes font names with ', the package with "
			.replace( /\s+/g, ' ' )
			.replace( /\s*,\s*/g, ', ' )
	);

const px = ( v ) => {
	const m = /^(-?[\d.]+)px$/.exec( String( v ).trim() );
	return m ? parseFloat( m[ 1 ] ) : NaN;
};

/* ---------------------------------------------------------------- lookup */

// Pointer grammar: dot separated keys, with [slug] to pick an array member by
// its `slug`. No key in theme.json contains a dot, so a plain split is safe.
function resolve( root, pointer ) {
	let node = root;
	for ( const rawKey of pointer.split( '.' ) ) {
		if ( node === undefined || node === null ) {
			return undefined;
		}
		const m = /^([^[]+)\[([^\]]+)\]$/.exec( rawKey );
		if ( m ) {
			const list = node[ m[ 1 ] ];
			if ( ! Array.isArray( list ) ) {
				return undefined;
			}
			node = list.find( ( item ) => item.slug === m[ 2 ] );
		} else {
			node = node[ rawKey ];
		}
	}
	return node;
}

/* ------------------------------------------------------------- the check */

const themeJson = JSON.parse( read( join( themeDir, 'theme.json' ), 'theme.json' ) );
const { map, $unmapped: unmapped = {} } = JSON.parse(
	read( join( here, 'token-map.json' ), 'tools/token-map.json' )
);

for ( const [ pointer, expected ] of Object.entries( map ) ) {
	const actual = resolve( themeJson, pointer );

	if ( actual === undefined ) {
		errors.push( `${ pointer }: mapped, but theme.json has no such value` );
		continue;
	}

	if ( expected === null ) {
		if ( ! unmapped[ pointer ] ) {
			errors.push( `${ pointer }: declared unmapped, but $unmapped says nothing about why` );
		} else {
			notes.push( `${ pointer } = ${ actual } (not a token: ${ unmapped[ pointer ] })` );
		}
		continue;
	}

	if ( typeof expected === 'object' && Array.isArray( expected.ratio ) ) {
		const [ numeratorName, denominatorName ] = expected.ratio;
		const numerator = px( tokens.get( numeratorName ) );
		const denominator = px( tokens.get( denominatorName ) );
		if ( Number.isNaN( numerator ) || Number.isNaN( denominator ) ) {
			errors.push( `${ pointer }: ${ numeratorName } / ${ denominatorName } is not a px pair in the token file` );
			continue;
		}
		const want = numerator / denominator;
		const got = parseFloat( actual );
		if ( Math.abs( want - got ) > 0.0005 ) {
			errors.push(
				`${ pointer }: theme.json says ${ actual }, ${ numeratorName } / ${ denominatorName } is ${ want.toFixed( 6 ) }`
			);
		}
		continue;
	}

	if ( ! tokens.has( expected ) ) {
		errors.push( `${ pointer }: maps to ${ expected }, which is not in the token file` );
		continue;
	}

	const want = normalize( tokens.get( expected ) );
	const got = normalize( actual );
	if ( want !== got ) {
		errors.push( `${ pointer }: theme.json says ${ actual }, ${ expected } is ${ tokens.get( expected ) }` );
	}
}

/* --------------------------------------------- no preset without a source */

const presetGroups = [
	[ 'settings.color.palette', 'color' ],
	[ 'settings.typography.fontSizes', 'size' ],
	[ 'settings.typography.fontFamilies', 'fontFamily' ],
	[ 'settings.spacing.spacingSizes', 'size' ],
];

for ( const [ pointer, prop ] of presetGroups ) {
	for ( const preset of resolve( themeJson, pointer ) ?? [] ) {
		const key = `${ pointer }[${ preset.slug }].${ prop }`;
		if ( ! ( key in map ) ) {
			errors.push( `${ key }: preset has no entry in tools/token-map.json` );
		}
	}
}

/* ------------------------------------- every --wpds-* the stylesheet uses */

const cssDir = join( themeDir, 'assets', 'css' );
const used = new Set();
for ( const file of readdirSync( cssDir ).filter( ( f ) => f.endsWith( '.css' ) ).sort() ) {
	const stylesheet = read( join( cssDir, file ), `assets/css/${ file }` );
	for ( const [ , name ] of stylesheet.matchAll( /var\(\s*(--wpds-[a-z0-9-]+)/g ) ) {
		used.add( name );
		/*
		 * Component knobs are not design tokens. The wpds/* blocks expose
		 * their own custom properties under a component prefix (the plugin's
		 * --wpds-button-* precedent), and a theme is allowed to set and read
		 * those. Each prefix is DECLARED in token-map.json rather than
		 * inferred, so a typo'd token name cannot hide behind a plausible
		 * component-looking prefix.
		 */
		const isComponentKnob = ( map.componentPrefixes || [] ).some( ( p ) => name.startsWith( p ) );
		if ( ! tokens.has( name ) && ! isComponentKnob ) {
			errors.push( `assets/css/${ file }: ${ name } is not in the token file` );
		}
	}
}

/* ---------------------------------------------------------------- report */

console.log( `tokens: ${ relative( process.cwd(), tokensPath ) } (${ tokens.size } values)` );
console.log( `checked: ${ Object.keys( map ).length } mappings, ${ used.size } stylesheet references` );
for ( const note of notes ) {
	console.log( `note: ${ note }` );
}

if ( errors.length ) {
	console.error( `\n${ errors.length } problem(s):` );
	for ( const e of errors ) {
		console.error( `  ${ e }` );
	}
	process.exit( 1 );
}

console.log( 'OK, every literal in theme.json still matches the token it was cut from.' );
