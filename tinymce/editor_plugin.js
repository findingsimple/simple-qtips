// Docu : http://www.tinymce.com/wiki.php/API3:tinymce.api.3.x

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('simple_qtips');
	
	tinymce.create('tinymce.plugins.simple_qtips', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			// Register the command so that it can be invoked from the button
			ed.addCommand('mce_simple_qtips', function() {
				simple_qtips_canvas = ed;
				simple_qtips_caller = 'visual';
				jQuery( "#simple-qtips-dialog" ).dialog( "open" );
			});

			// Register example button
			ed.addButton('simple_qtips', {
				title : 'simple_qtips.desc',
				cmd : 'mce_simple_qtips',
				image : url + '/simple-qtips.png'
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 */
		getInfo : function() {
			return {
					longname  : 'Simple qTips',
					author 	  : 'Finding Simple',
					authorurl : 'http://findingsimple.com/',
					infourl   : 'http://findingsimple.com/',
					version   : '1.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('simple_qtips', tinymce.plugins.simple_qtips);
})();


