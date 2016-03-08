/**
 * @file resources/tinymce.js Initialize the tinymce plugin
 *
 * @author Gerkin
 * @copyright 2016 iThoughts Informatique
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GPLv2
 * @package ithoughts\html_snippets
 *
 * @version 1.0.1
 */

/**
 * @namespace tinymce-plugin
 */
/**
 * @function __autoexec
 * @memberof tinymce-plugin
 * @desc Add the plugin button to TinyMCE and declare {@link tinymce-plugin} functions in scope.
 * @private
 * @author Gerkin
 */
(function(){
	
	/**
	 * @function initSelectSnippet
	 * @memberof tinymce-plugin
	 * @desc Retrieves a list of HTML Snippets available via ajax and display a TinyMCE dropdown to select one of them
	 * @private
	 * @author Gerkin
	 */
	function initSelectSnippet(){
		var snippetId = null;
		jQuery.post(ithoughts_html_snippets.admin_ajax, {
			action: "ithoughts_html_snippets_get_list",
			search: ""
		}, function(data){
			// Open window
			var tinyMceBody = new tinyMCE.ui.Factory.create({
				type:"form",
				title: editor.getLang('ithoughts_html_snippets_tinymce.list'),
				items:[
					{
						type:"listbox",
						label:editor.getLang('ithoughts_html_snippets_tinymce.snippet'),
						name:"snippet_id",
						values:data.data.snippets,
						value: snippetId,
						tooltip:editor.getLang('ithoughts_html_snippets_tinymce.snippet_explain')
					}
				]
			});
			editor.windowManager.open({
				title: editor.getLang('ithoughts_html_snippets_tinymce.chose_snippet'),
				body: [ tinyMceBody ],
				onsubmit: initPlaceholdersInput
			});
		})
	}

	/**
	 * @function initPlaceholdersInput
	 * @memberof tinymce-plugin
	 * @desc Get the selected snippet content and check if placeholders are found. If there are, display inputs to set them
	 * @param {Object} tinyMceSubmitEvent event emitted by the "Submit" action triggered from {@link tinymce-plugin.initSelectSnippet}
	 * @private
	 * @author Gerkin
	 */
	function initPlaceholdersInput(tinyMceSubmitEvent){
		snippetId = tinyMceSubmitEvent.data.snippet_id;
		jQuery.post(ithoughts_html_snippets.admin_ajax, {
			action: "ithoughts_html_snippets_get_snippet",
			id: snippetId
		}, function(data){
			// Open window
			var content = data.data.content;
			var matches = content.match(/%([^\s]+?)%/g);
			if(matches == null || typeof matches == "undefined")
				matches = [];
			console.log(matches);
			var form = [];
			for(var i = 0, j = matches.length; i < j; i++){
				var name = content.match(/%([^\s]+?)%/)[1];
				if(!name.match(/$if-/) && [
					"snippet-id",
					"snippet-name",
					"user_login",
					"user_email",
					"user_firstname",
					"user_lastname",
					"user_pseudo"
				].indexOf(name) === -1){
					form.push({
						type   : 'textbox',
						name   : name,
						label  : name,
						value  : ''
					});
				}
			}
			if(matches.length > 0){
				editor.windowManager.open({
					title: editor.getLang('ithoughts_html_snippets_tinymce.edit_snippet') + '"' + data.data.title + '"',
					body: form,
					onsubmit: function(e){
						finish(snippetId, e.data);
					}
				});
			} else {
				finish(snippetId,{});
			}
		});
	}
	
	/**
	 * @function finish
	 * @memberof tinymce-plugin
	 * @desc Compose & insert the shortcode
	 * @param {Number} snippetId Id of the snippet
	 * @param {Object} data Key-values data of placeholders
	 * @private
	 * @author Gerkin
	 */
	function finish(snippetId, data){
		console.log(e.data);
		// Insert content when the window form is submitted
		var str = "";
		for(var i in data){
			str += i + "=\"" + e.data[i].replace('"', '&aquot;') + '"'; 
		}
		editor.insertContent('[html_snippet snippet-id="' + snippetId + '" ' + str + "]");
	}

	tinymce.PluginManager.add('ithoughts_html_snippets', function(editor, url) {
		// Add a button that opens a window
		editor.addButton('ithoughts_html_snippet', {
			text: false,
			image: ithoughts_html_snippets.base_url + "/resources/icon-tinymce.png",
			onclick: initSelectSnippet
		});
	});
})();