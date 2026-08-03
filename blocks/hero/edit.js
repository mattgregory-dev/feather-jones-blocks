import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
	BlockControls,
	MediaPlaceholder,
	MediaReplaceFlow,
	MediaUpload,
	MediaUploadCheck,
	ColorPalette,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, Button, BaseControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Editor UI. Mirrors render.php's markup/classes so the theme stylesheet
 * (loaded into the canvas via add_editor_style) styles the preview identically —
 * including the cover background and overlay tint, which are visual
 * configuration and so must render live in the canvas.
 *
 * A sibling of Spotlight: same two-column shell, column image, and controls,
 * plus a full-cover background image and an alpha-capable overlay. The column
 * image, background image, and overlay are independent — any combination is
 * valid. `verticalAlignment` keeps its spotlight meaning and is simply moot when
 * no column image is set (not hidden).
 */

const ALLOWED_BLOCKS = [ 'core/paragraph', 'core/buttons' ];
const TEMPLATE = [ [ 'core/paragraph', { placeholder: __( 'Add hero text…', 'fj-blocks' ) } ] ];

export default function Edit( { attributes, setAttributes } ) {
	const {
		imageId,
		imageAlt,
		imagePosition,
		verticalAlignment,
		eyebrow,
		level,
		title,
		backgroundImageId,
		overlayColor,
		textScheme,
	} = attributes;

	const TitleTag = 'h1' === level ? 'h1' : 'h2';
	const hasBackground = !! backgroundImageId;
	const hasOverlay = hasBackground && !! overlayColor;

	// Media source URLs for the canvas preview, plus the theme palette for the
	// overlay control. getSettings().colors is the deprecation-free, all-versions
	// way to read the theme palette (no useSetting/useSettings churn).
	const { image, backgroundUrl, themeColors } = useSelect(
		( select ) => {
			const core = select( 'core' );
			return {
				image: imageId ? core.getMedia( imageId ) : null,
				backgroundUrl: backgroundImageId ? core.getMedia( backgroundImageId )?.source_url || '' : '',
				themeColors: select( 'core/block-editor' ).getSettings().colors || [],
			};
		},
		[ imageId, backgroundImageId ]
	);

	const blockProps = useBlockProps( {
		className:
			`is-position-${ imagePosition } is-valign-${ verticalAlignment }` +
			( imageId ? '' : ' has-no-media' ) +
			( hasBackground ? ' has-cover-background' : '' ) +
			( hasOverlay ? ' has-overlay' : '' ) +
			( 'light' === textScheme ? ' has-text-light' : '' ) +
			( 'dark' === textScheme ? ' has-text-dark' : '' ),
		style: {
			...( hasBackground && backgroundUrl ? { backgroundImage: `url(${ backgroundUrl })` } : {} ),
			...( hasOverlay ? { '--hero-overlay': overlayColor } : {} ),
		},
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'hero__body' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE, templateLock: false }
	);

	const onSelectImage = ( media ) =>
		setAttributes( { imageId: media.id, imageAlt: media.alt || '' } );

	const media = (
		<figure className="hero__media">
			{ imageId ? (
				<img src={ image?.source_url } alt={ imageAlt } />
			) : (
				<MediaPlaceholder
					icon="format-image"
					labels={ { title: __( 'Column image', 'fj-blocks' ) } }
					accept="image/*"
					allowedTypes={ [ 'image' ] }
					onSelect={ onSelectImage }
				/>
			) }
		</figure>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'fj-blocks' ) }>
					{ /* Framed around the always-present content column, not the optional
					     image. Stored as `imagePosition` (the image side), so the control
					     works in the inverse: content-left ⇄ image-right. */ }
					<SelectControl
						label={ __( 'Content position', 'fj-blocks' ) }
						help={ __( 'Which side the text column sits on. A column image, if set, takes the other side.', 'fj-blocks' ) }
						value={ 'left' === imagePosition ? 'right' : 'left' }
						options={ [
							{ label: __( 'Left', 'fj-blocks' ), value: 'left' },
							{ label: __( 'Right', 'fj-blocks' ), value: 'right' },
						] }
						onChange={ ( value ) =>
							setAttributes( { imagePosition: 'left' === value ? 'right' : 'left' } )
						}
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Vertical alignment', 'fj-blocks' ) }
						help={ __( 'Applies above 1160px; narrower screens stack and top-align.', 'fj-blocks' ) }
						value={ verticalAlignment }
						options={ [
							{ label: __( 'Center', 'fj-blocks' ), value: 'center' },
							{ label: __( 'Top', 'fj-blocks' ), value: 'top' },
						] }
						onChange={ ( value ) => setAttributes( { verticalAlignment: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Background', 'fj-blocks' ) }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( selected ) => setAttributes( { backgroundImageId: selected.id } ) }
							allowedTypes={ [ 'image' ] }
							value={ backgroundImageId }
							render={ ( { open } ) => (
								<BaseControl __nextHasNoMarginBottom>
									<Button variant="secondary" onClick={ open } __next40pxDefaultSize>
										{ backgroundImageId
											? __( 'Replace background image', 'fj-blocks' )
											: __( 'Set background image', 'fj-blocks' ) }
									</Button>
									{ hasBackground && (
										<Button
											variant="link"
											isDestructive
											onClick={ () =>
												setAttributes( { backgroundImageId: 0, overlayColor: '' } )
											}
										>
											{ __( 'Remove', 'fj-blocks' ) }
										</Button>
									) }
								</BaseControl>
							) }
						/>
					</MediaUploadCheck>

					{ /* An overlay over nothing is meaningless — only offer it with a background. */ }
					{ hasBackground && (
						<BaseControl
							label={ __( 'Overlay tint', 'fj-blocks' ) }
							help={ __( 'Semi-transparent tint over the background. Adjust the alpha for strength.', 'fj-blocks' ) }
							__nextHasNoMarginBottom
						>
							<ColorPalette
								colors={ themeColors }
								value={ overlayColor }
								onChange={ ( value ) => setAttributes( { overlayColor: value || '' } ) }
								enableAlpha
								clearable
							/>
						</BaseControl>
					) }

					{ /* Applies with or without a background — a legibility response to the
					     backdrop, not gated on has-cover-background. */ }
					<SelectControl
						label={ __( 'Text color scheme', 'fj-blocks' ) }
						help={ __( 'Light for dark backgrounds, Dark to force standard colors over a light one.', 'fj-blocks' ) }
						value={ textScheme }
						options={ [
							{ label: __( 'Default', 'fj-blocks' ), value: 'default' },
							{ label: __( 'Light', 'fj-blocks' ), value: 'light' },
							{ label: __( 'Dark', 'fj-blocks' ), value: 'dark' },
						] }
						onChange={ ( value ) => setAttributes( { textScheme: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Content', 'fj-blocks' ) }>
					<TextControl
						label={ __( 'Eyebrow (optional)', 'fj-blocks' ) }
						value={ eyebrow }
						onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Title', 'fj-blocks' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Title heading level', 'fj-blocks' ) }
						help={ __( 'Sets the heading tag only: H1 for a page hero, H2 for a mid-page feature.', 'fj-blocks' ) }
						value={ level }
						options={ [
							{ label: __( 'H2 — in-page feature', 'fj-blocks' ), value: 'h2' },
							{ label: __( 'H1 — page hero', 'fj-blocks' ), value: 'h1' },
						] }
						onChange={ ( value ) => setAttributes( { level: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				{ imageId && (
					<PanelBody title={ __( 'Column image', 'fj-blocks' ) }>
						<TextControl
							label={ __( 'Alt text', 'fj-blocks' ) }
							help={ __( 'Describe the column image for screen readers.', 'fj-blocks' ) }
							value={ imageAlt }
							onChange={ ( value ) => setAttributes( { imageAlt: value } ) }
							__nextHasNoMarginBottom
						/>
						<Button
							variant="link"
							isDestructive
							onClick={ () => setAttributes( { imageId: undefined, imageAlt: '' } ) }
						>
							{ __( 'Remove column image', 'fj-blocks' ) }
						</Button>
					</PanelBody>
				) }
			</InspectorControls>

			{ imageId && (
				<BlockControls>
					<MediaReplaceFlow
						mediaId={ imageId }
						mediaURL={ image?.source_url }
						allowedTypes={ [ 'image' ] }
						accept="image/*"
						onSelect={ onSelectImage }
					/>
				</BlockControls>
			) }

			<section { ...blockProps }>
				<div className="hero__inner">
					<div className="hero__text">
						{ eyebrow && <p className="hero__eyebrow">{ eyebrow }</p> }
						{ title && <TitleTag className="hero__title">{ title }</TitleTag> }
						<div { ...innerBlocksProps } />
					</div>
					{ media }
				</div>
			</section>
		</>
	);
}
