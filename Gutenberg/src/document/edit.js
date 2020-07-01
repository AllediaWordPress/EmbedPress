/**
 * Internal dependencies
 */

import Iframe from '../common/Iframe';

/**
 * WordPress dependencies
 */
import classnames from 'classnames';

const {__} = wp.i18n;
const {getBlobByURL, isBlobURL, revokeBlobURL} = wp.blob;
const {BlockIcon, MediaPlaceholder ,InspectorControls} = wp.editor;
const {Component, Fragment} = wp.element;
const { RangeControl,PanelBody, ExternalLink,Placeholder } = wp.components;
import {DocumentIcon} from '../common/icons'

const ALLOWED_MEDIA_TYPES = [
	'application/pdf',
	'application/msword',
	'application/vnd.ms-powerpoint',
	'application/vnd.ms-excel',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation'
];

class DocumentEdit extends Component {
	constructor() {
		super(...arguments);
		this.onSelectFile = this.onSelectFile.bind(this);

		this.onUploadError = this.onUploadError.bind(this);
		this.onLoad = this.onLoad.bind(this);
		this.hideOverlay = this.hideOverlay.bind(this);
		this.state = {
			hasError: false,
			fetching:false,
			interactive: false,
			loadPdf: true,
		};
	}


	componentDidMount() {
		const {
			attributes,
			mediaUpload,
			noticeOperations
		} = this.props;
		const {href} = attributes;

		// Upload a file drag-and-dropped into the editor
		if (isBlobURL(href)) {
			const file = getBlobByURL(href);

			mediaUpload({
				filesList: [file],
				onFileChange: ([media]) => this.onSelectFile(media),
				onError: (message) => {
					this.setState({hasError: true});
					noticeOperations.createErrorNotice(message);
				},
			});

			revokeBlobURL(href);
		}

		if(this.props.attributes.href && this.props.attributes.mime === 'application/pdf' && this.state.loadPdf){
			this.setState({loadPdf: false});
			PDFObject.embed(this.props.attributes.href, "."+this.props.attributes.id);
		}

	}

	componentDidUpdate(prevProps) {

		// Reset copy confirmation state when block is deselected
		if (prevProps.isSelected && !this.props.isSelected) {
			this.setState({showCopyConfirmation: false});
		}

	}

	static getDerivedStateFromProps(nextProps, state) {
		if (!nextProps.isSelected && state.interactive) {
			return {interactive: false};
		}

		return null;
	}

	hideOverlay() {
		this.setState({interactive: true});
	}

	onLoad() {
		this.setState({
			fetching:false
		})
	}

	onSelectFile(media) {
		if (media && media.url) {
			this.setState({hasError: false});
			this.props.setAttributes({
				href: media.url,
				fileName: media.title,
				id: 'embedpress-pdf-'+Date.now(),
				mime: media.mime,
			});
			if(media.mime === 'application/pdf'){
				this.setState({loadPdf: false});
				PDFObject.embed(media.url, "."+this.props.attributes.id);
			}
		}

	}

	onUploadError(message) {
		const {noticeOperations} = this.props;
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice(message);
	}




	render() {
		const {attributes, noticeUI,setAttributes} = this.props;
		const {href,mime,id,width,height} = attributes;
		const {hasError,interactive,fetching,loadPdf} = this.state;
		const min = 1;
		const max = 1000;
		const docLink = 'https://embedpress.com/docs/embed-docuemnt/'
		if (!href || hasError) {

			return (
				<div>
					<MediaPlaceholder
						icon={<BlockIcon icon={DocumentIcon}/>}
						labels={{
							title: __('Document'),
							instructions: __(
								'Upload a file or pick one from your media library for embed. Supported File Type: PDF, DOC/DOCX, PPT/PPTX, XLS/XLSX etc'
							),
						}}
						onSelect={this.onSelectFile}
						notices={noticeUI}
						allowedTypes={ALLOWED_MEDIA_TYPES}
						onError={this.onUploadError}

					>

						<div style={{width:'100%'}} className="components-placeholder__learn-more embedpress-doc-link">
							<ExternalLink href={docLink}>Learn more about Embedded document </ExternalLink>
						</div>
					</MediaPlaceholder>

				</div>

			);
		} else {
			const url = 'https://docs.google.com/viewer?url='+href+'&embedded=true';
			return (
				<Fragment>
					{ mime === 'application/pdf' && (
						<div style={{height:height,width:width}} className={'embedpress-embed-document-pdf'+' '+id} data-emid={id} data-emsrc={href}></div>
					) }
					{ mime !== 'application/pdf' && (
						<Iframe onMouseUponMouseUp={ this.hideOverlay } style={{height:height,width:width,display: fetching || !loadPdf ? 'none' : ''}} onLoad={this.onLoad} src={url}
								mozallowfullscreen="true" webkitallowfullscreen="true"/>
					) }
					{ ! interactive && (
						<div
							className="block-library-embed__interactive-overlay"
							onMouseUp={ this.hideOverlay }
						/>
					) }
					<InspectorControls key="inspector">
						<PanelBody
							title={ __( 'Embed Size', 'embedpress' ) }
						>
							<RangeControl
								label={ __(
									'Width',
									'embedpress'
								) }
								value={ width }
								onChange={ ( width ) =>
									setAttributes( { width } )
								}
								max={ max }
								min={ min }
							/>
							<RangeControl
								label={ __(
									'Height',
									'embedpress'
								) }
								value={height }
								onChange={ ( height ) =>
									setAttributes( { height } )
								}
								max={ max }
								min={ min }
							/>
							{ __(
								'Powered by EmbedPress',
								'embedpress'
							) }
						</PanelBody>
					</InspectorControls>
				</Fragment>
			);
		}

	}

};
export default DocumentEdit;
