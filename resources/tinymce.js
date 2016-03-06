(function(){
	tinymce.PluginManager.add('ithoughts_html_snippets', function(editor, url) {
		// Add a button that opens a window
		editor.addButton('ithoughts_html_snippet', {
			text: false,
			image: ithoughts_html_snippets.base_url + "/resources/icon-tinymce.png",
			onclick: function() {
				var snippetId = null;
				jQuery.post(ithoughts_html_snippets.admin_ajax, {
					action: "ithoughts_html_snippets_get_list",
					search: ""
				}, function(data){
					console.log(data);
					// Open window
					editor.windowManager.open({
						title: editor.getLang('ithoughts_html_snippets_tinymce.chose_snippet'),
						body: [
							new tinyMCE.ui.Factory.create({
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
							})
						],
						onsubmit: function(e) {
							console.log(e);
							snippetId = e.data.snippet_id;
							jQuery.post(ithoughts_html_snippets.admin_ajax, {
								action: "ithoughts_html_snippets_get_snippet",
								id: snippetId
							}, function(data){
								console.log(data);
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
								function finish(snippetId, data){
										console.log(e.data);
										// Insert content when the window form is submitted
										var str = "";
										for(var i in data){
											str += i + "=\"" + e.data[i].replace('"', '&aquot;') + '"'; 
										}
										editor.insertContent('[html_snippet snippet-id="' + snippetId + '" ' + str + "]");
								}
							});
						}
					});
				})
			}
		});
	});
})();