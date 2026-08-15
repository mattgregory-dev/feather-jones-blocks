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
 * (loaded into the canvas via add_editor_style) styles the preview identically.
 *
 * The title is always a typed attribute; `level` only chooses the heading tag
 * (H1 for a page hero, H2 for a mid-page feature). Unlike intro-section, there
 * is no page-title binding — spotlight headlines never match the page title.
 */

const ALLOWED_BLOCKS = [ 'core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/table', 'core/buttons', 'core/separator', 'core/group' ];
const TEMPLATE = [ [ 'core/paragraph', { placeholder: __( 'Add spotlight text…', 'fj-blocks' ) } ] ];

export default function Edit( { attributes, setAttributes } ) {
	const {
		imageId,
		imageAlt,
		imagePosition,
		verticalAlignment,
		mobileRatio,
		mobileFocal,
		mobileImageId,
		backgroundImageId,
		overlayColor,
		eyebrow,
		level,
		title,
	} = attributes;
	const isH1 = 'h1' === level;
	const TitleTag = isH1 ? 'h1' : 'h2';
	const hasBackground = !! backgroundImageId;
	const hasOverlay = hasBackground && !! overlayColor;

	// Background image source (for the live canvas cover) and the theme palette
	// for the overlay color control.
	const { backgroundUrl, themeColors } = useSelect(
		( select ) => ( {
			backgroundUrl: backgroundImageId
				? select( 'core' ).getMedia( backgroundImageId )?.source_url || ''
				: '',
			themeColors: select( 'core/block-editor' ).getSettings().colors || [],
		} ),
		[ backgroundImageId ]
	);

	const blockProps = useBlockProps( {
		className:
			`is-position-${ imagePosition } is-valign-${ verticalAlignment }` +
			( imageId ? '' : ' has-no-media' ) +
			( imageId && mobileImageId ? ' has-mobile-image' : '' ) +
			( hasBackground ? ' has-cover-background' : '' ) +
			( hasOverlay ? ' has-overlay' : '' ),
		// Mobile crop controls surface as custom properties the <= 800px rule reads;
		// set here too so a narrowed editor canvas previews them. The cover
		// background and overlay tint are visual config, so they render live too.
		style: {
			'--sb-spot-ratio': mobileRatio,
			'--sb-spot-focal': mobileFocal,
			...( hasBackground && backgroundUrl ? { backgroundImage: `url(${ backgroundUrl })` } : {} ),
			...( hasOverlay ? { '--sb-spot-overlay': overlayColor } : {} ),
		},
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'spotlight__body' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE, templateLock: false }
	);

	// Media objects for the canvas preview source URLs.
	const image = useSelect(
		( select ) => ( imageId ? select( 'core' ).getMedia( imageId ) : null ),
		[ imageId ]
	);
	const mobileImage = useSelect(
		( select ) => ( mobileImageId ? select( 'core' ).getMedia( mobileImageId ) : null ),
		[ mobileImageId ]
	);

	const onSelectImage = ( selected ) =>
		setAttributes( { imageId: selected.id, imageAlt: selected.alt || '' } );

	const media = (
		<figure className="spotlight__media">
			{ imageId ? (
				<>
					<img className="spotlight__img spotlight__img--desktop" src={ image?.source_url } alt={ imageAlt } />
					{ mobileImageId && (
						<img className="spotlight__img spotlight__img--mobile" src={ mobileImage?.source_url } alt={ imageAlt } />
					) }
				</>
			) : (
				<MediaPlaceholder
					icon="format-image"
					labels={ { title: __( 'Spotlight image', 'fj-blocks' ) } }
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
						help={ __( 'Which side the text column sits on. An image, if set, takes the other side.', 'fj-blocks' ) }
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
						help={ __( 'Applies above 1120px; narrower screens stack and top-align.', 'fj-blocks' ) }
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
										<Button variant="secondary" onClick={ open }>
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
					<PanelBody title={ __( 'Image', 'fj-blocks' ) }>
						<TextControl
							label={ __( 'Alt text', 'fj-blocks' ) }
							help={ __( 'Describe the image for screen readers.', 'fj-blocks' ) }
							value={ imageAlt }
							onChange={ ( value ) => setAttributes( { imageAlt: value } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Mobile crop ratio', 'fj-blocks' ) }
							help={ __( 'Aspect ratio the image is cropped to below 800px. Keep 3:2 unless the crop fails.', 'fj-blocks' ) }
							value={ mobileRatio }
							options={ [
								{ label: __( '3:2 (default)', 'fj-blocks' ), value: '3 / 2' },
								{ label: __( '4:3', 'fj-blocks' ), value: '4 / 3' },
								{ label: __( '1:1 (square)', 'fj-blocks' ), value: '1 / 1' },
							] }
							onChange={ ( value ) => setAttributes( { mobileRatio: value } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Mobile focal point', 'fj-blocks' ) }
							help={ __( 'Which part of the image the crop keeps below 800px. Try this before changing ratio.', 'fj-blocks' ) }
							value={ mobileFocal }
							options={ [
								{ label: __( 'Center', 'fj-blocks' ), value: 'center' },
								{ label: __( 'Top', 'fj-blocks' ), value: 'center top' },
								{ label: __( 'Bottom', 'fj-blocks' ), value: 'center bottom' },
							] }
							onChange={ ( value ) => setAttributes( { mobileFocal: value } ) }
							__nextHasNoMarginBottom
						/>
						<p className="components-base-control__help" style={ { marginBottom: '0.25rem' } }>
							{ __( 'Optional mobile image — shown below 800px instead of the cropped desktop one. Supply a pre-cropped landscape version when the crop can’t be rescued.', 'fj-blocks' ) }
						</p>
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={ [ 'image' ] }
								value={ mobileImageId }
								onSelect={ ( selected ) => setAttributes( { mobileImageId: selected.id } ) }
								render={ ( { open } ) => (
									<Button variant="secondary" onClick={ open }>
										{ mobileImageId
											? __( 'Replace mobile image', 'fj-blocks' )
											: __( 'Set mobile image', 'fj-blocks' ) }
									</Button>
								) }
							/>
							{ mobileImageId && (
								<Button
									variant="link"
									isDestructive
									onClick={ () => setAttributes( { mobileImageId: undefined } ) }
								>
									{ __( 'Remove mobile image', 'fj-blocks' ) }
								</Button>
							) }
						</MediaUploadCheck>
						<Button
							variant="link"
							isDestructive
							onClick={ () => setAttributes( { imageId: undefined, imageAlt: '', mobileImageId: undefined } ) }
						>
							{ __( 'Remove image', 'fj-blocks' ) }
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
				<div className="spotlight__inner">
					<div className="spotlight__text">
						{ eyebrow && <p className="spotlight__eyebrow">{ eyebrow }</p> }
						{ title && (
							<TitleTag className="spotlight__title">{ title }</TitleTag>
						) }
						<div { ...innerBlocksProps } />
					</div>
					{ media }
				</div>
			</section>
		</>
	);
}
