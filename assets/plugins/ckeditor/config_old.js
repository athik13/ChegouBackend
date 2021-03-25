/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
        //config.removeButtons = 'Underline,JustifyCenter';
        config.removeButtons = 'Save,Checkbox,Radio,TextField,Textarea,HiddenField,Button,ImageButton,Print,Flash,Subscript,Superscript,CopyFormatting,RemoveFormat,Link,Unlink,Anchor,Table,Smiley,Iframe';
        
        config.removePlugins = 'pagebreak';
        config.toolbarGroups = [
		//{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		//{ name: 'editing',     groups: [ 'find', 'selection' ] },
		//{ name: 'links' },
		{ name: 'insert'},
		//{ name: 'forms' },
		//{ name: 'tools' },
		//{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		//{ name: 'others' },
		//'/',
		{ name: 'basicstyles', groups: [ 'basicstyles' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'align' ] },
                
		//{ name: 'styles' },
		//{ name: 'colors' },
		//{ name: 'about' }
	];
        
};


$('.ckeditor').each(function(e){
            CKEDITOR.replace( this.id, {
					height: 300,

					// Configure your file manager integration. This example uses CKFinder 3 for PHP.
					filebrowserBrowseUrl: '../assets/plugins/ckfinder/ckfinder.html',
					filebrowserImageBrowseUrl: '../assets/plugins/ckfinder/ckfinder.html?type=Images',
					filebrowserUploadUrl: '../assets/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
					filebrowserImageUploadUrl: '../assets/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images'
				} );
                                } );		
