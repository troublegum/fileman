jQuery(document).ready(function() {

var currentGridRowId;

	//
	//	Init sort direct highlight
	//
	var direction = /&direction=(ASC|DESC)/.exec(location.href);
	var sort = /&sort=([a-z]+)/.exec(location.href);
		
	if (direction != null && sort != null) {
		var th = jQuery("table thead th[field=" + sort[1] + "]");
		var iconClass = (direction[1] == "DESC") ? "icon-arrow-down" : "icon-arrow-up";		
		jQuery(th).find("a").append("&nbsp;<i class=\"" + iconClass + "\"></i>");
	}

	// 
	// Init tree loader
	//
	jQuery("a[href=#tree-load]").click(function(e) {
		var dialogId = jQuery(e.target).parents("div.modal").attr("id");
		jQuery("#" +  dialogId).find(".fileTree").fileTree({
			root: currentDir,
			script: urlAjaxTree,
			folderEvent: "click"
		});		
		jQuery("#" +  dialogId).find(".fileTree").delegate( "li > a", "dblclick", function(e) {
			var dir = jQuery(e.target).attr("rel");
			jQuery("#" +  dialogId).find("input[name=destination]").val(dir);
		});
	});

	//
	//	Init waiting
	//
	jQuery("[waiting=waiting]").click(function(e) {
		jQuery("#waiting").modal({show: false, keyboard: false});
		jQuery("#waiting").modal("show");
	});

	// 
	// Init modal dialogs
	//
	var modalOptions = {show: false};
	jQuery("#context-menu").modal(modalOptions).on("show", function() {
		var gridRow = jQuery("#" + currentGridRowId);

		//Download menu item
		var type = jQuery(gridRow).find("[data=type]").text();			
		var downloadMenuItem = jQuery("#context-menu").find("a[href=#download]").parent("li");			
		if (type == "dir") {
			jQuery(downloadMenuItem).hide();
		} else {
			jQuery(downloadMenuItem).show();
		}

		//Unzip menu item
		var extension = jQuery(gridRow).find("[data=extension]").text();
		var unzipMenuItem = jQuery("#context-menu").find("a[href=#unzip]").parent("li");
		if (extension == "zip") {
			jQuery(unzipMenuItem).show();
		} else {
			jQuery(unzipMenuItem).hide();
		}
	});
	jQuery("#upload-file").modal(modalOptions);
	jQuery("#upload-file").find("input[name=select]").change(function(e) {
		if (jQuery(e.target).val() == "local") {
			jQuery("form[name=fileupload]").find("input[name='files[]']").attr("disabled", false);
			jQuery("form[name=fileupload]").find("input[name=url]").attr("disabled", "disabled").val("");
		} else {
			jQuery("form[name=fileupload]").find("input[name='files[]']").attr("disabled", "disabled").val("");
			jQuery("form[name=fileupload]").find("input[name=url]").attr("disabled", false);
		}
	});
	jQuery("#mkdir").modal(modalOptions);
	jQuery("#rmdir").modal(modalOptions).on("show", function() {
		prepareDialogHeader("rmdir");
	});
	jQuery("#unlink").modal(modalOptions).on("show", function() {
		prepareDialogHeader("unlink");
	});
	jQuery("#group-delete").modal(modalOptions);
	jQuery("#rename").modal(modalOptions).on("show", function() {
		prepareDialogHeader("rename");
	});
	jQuery("#move").modal(modalOptions).on("show", function() {
		prepareDialogHeader("move");
	});
	jQuery("#group-move").modal(modalOptions);
	jQuery("#tree").modal(modalOptions);
	jQuery("#copy").modal(modalOptions).on("show", function() {
		prepareDialogHeader("copy");
	});
	jQuery("#group-copy").modal(modalOptions);
	jQuery("#chmod").modal(modalOptions).on("show", function() {
		prepareDialogHeader("chmod");
	});
	jQuery("#unzip").modal(modalOptions).on("show", function() {
		prepareDialogHeader("unzip");
	});
	jQuery("#alert-nothing-selected").modal(modalOptions);
	

	// 
	// Init grid
	//
	jQuery("#table input[name=checkAll]").change(function(e) {
		var checked = (jQuery(e.target).attr("checked") == "checked") ? "checked" : null;
		jQuery("#table tbody input[type=checkbox]").attr("checked", checked);
	});
	jQuery("#table a[href=#context-menu]").click(function(e) { //Set currentGridRowId value
		currentGridRowId = jQuery(e.target).attr("rowId");
	});

	// 
	// Context menu item "Remove"
	//
	jQuery("#context-menu a[href=#remove]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var type = jQuery(gridRow).find("[data=type]").text();
		if (type == "dir") {
			jQuery("#rmdir").modal("show");
		} else {
			jQuery("#unlink").modal("show");
		}
	});

	// 
	// Rmdir
	//
	jQuery("#rmdir div.modal-footer a[href=#doRmdir]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var path = jQuery(gridRow).find("[data=path]").text();
		location.href = urlRmdir + "?path=" + encodeURIComponent(path);
	});

	// 
	// Unlink
	//
	jQuery("#unlink div.modal-footer a[href=#doUnlink]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var path = jQuery(gridRow).find("[data=path]").text();
		location.href = urlUnlink + "?path=" + encodeURIComponent(path);
	});

	// 
	// Group delete
	//
	jQuery("#group-actions a[href=#groupDelete]").click(function(e) {
		var files = getCheckedFiles();
		if (files.length > 0) {
			appendCheckedFilesToForm("form[name=group-delete]", files);				
			jQuery("#group-delete a[href=#doGroupDelete]").click(function () {
				jQuery("form[name=group-delete]").submit();
			});
			jQuery("#group-delete").modal("show");
		} else {
			jQuery("#alert-nothing-selected").modal("show");
		}
	});

	// 
	// Context menu item "Rename"
	//
	jQuery("#context-menu a[href=#rename]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var label = jQuery(gridRow).find("[data=label]").text();
		jQuery("form[name=rename] input[name=oldName]").val(label);
		jQuery("form[name=rename] input[name=newName]").val(label);
		jQuery("#rename").modal("show");
	});

	// 
	// Context menu item "Move"
	//
	jQuery("#context-menu a[href=#move]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var source = jQuery(gridRow).find("[data=path]").text();
		jQuery("form[name=move] input[name=source]").val(source);
		jQuery("#move").modal("show");
	});

	// 
	// Group Move
	//
	jQuery("#group-actions a[href=#groupMove]").click(function(e) {
		var files = getCheckedFiles();
		if (files.length > 0) {
			appendCheckedFilesToForm("form[name=group-move]", files);
			jQuery("#group-move").modal("show");
		} else {
			jQuery("#alert-nothing-selected").modal("show");
		}	
	});

	// 
	// Context menu item "Copy"
	//
	jQuery("#context-menu a[href=#copy]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var path = jQuery(gridRow).find("[data=path]").text();
		jQuery("form[name=copy] input[name=source]").val(path);
		jQuery("#copy").modal("show");
	});

	// 
	// Group copy
	//
	jQuery("#group-actions a[href=#groupCopy]").click(function(e) {			
		var files = getCheckedFiles();
		if (files.length > 0) {
			appendCheckedFilesToForm("form[name=group-copy]", files);
			jQuery("#group-copy").modal("show");
		} else {
			jQuery("#alert-nothing-selected").modal("show");
		}
	});

	// 
	// Context menu item "Chmod"
	//
	jQuery("#context-menu a[href=#chmod]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var permissions = jQuery(gridRow).find("[data=permissions]").text();
		var file = jQuery(gridRow).find("[data=path]").text();
		jQuery("form[name=chmod] input[name=file]").val(file);
		jQuery("form[name=chmod] input[name=mode]").val(permissions);
		jQuery("#chmod").modal("show");
	});

	// 
	// Context menu item "Zip"
	//
	jQuery("#context-menu a[href=#zip]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var file = jQuery(gridRow).find("[data=path]").text();
		location.href = urlZip + "?file=" + encodeURIComponent(file) + "&dir=" + encodeURIComponent(currentDir);
	});

	// 
	// Context menu item "Unzip"
	//
	jQuery("#context-menu a[href=#unzip]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var file = jQuery(gridRow).find("[data=path]").text();
		jQuery("form[name=unzip] input[name=file]").val(file);
		jQuery("#unzip").modal("show");
	});

	// 
	// Context menu item "Download"
	//
	jQuery("#context-menu a[href=#download]").click(function(e) {
		var gridRow = jQuery("#" + currentGridRowId);
		var file = jQuery(gridRow).find("[data=path]").text();
		location.href = urlDownload + "?file=" + encodeURIComponent(file);
	});

	//
	//	Misc. functions
	//
	function getCheckedFiles() {
		var files = [];
		jQuery("input[type=checkbox].select:checked").each(function(index, el) {
			files[index] = jQuery(el).parents("tr.grid-row").find("[data=path]").text();
		});
		return files;
	}

	function appendCheckedFilesToForm(formId, files) {
		for (var key in files) {
			var htmlInput = "<input type='hidden' value='" + files[key] + "' name='files[" + key + "]' />";
			jQuery(formId).append(htmlInput);
		}
	}

	function prepareDialogHeader(dialogId) {
		var gridRow = jQuery("#" + currentGridRowId);
		var label = jQuery(gridRow).find("[data=label]").text();
		jQuery("#" + dialogId).find(".modal-header h3 span.placeholder-label").text(label);
	}

	function selectFolderOnTree(target) {
		var dir = jQuery(target).parent().attr("rel");
		var dialogId = jQuery(target).parents("div.modal").attr("id");
		jQuery("#" + dialogId).find("input[name=destination]").value(dir);
	}
});

//
//	Submit form function for dialogs
//
function submitForm(formName) {
	jQuery("form[name=" + formName + "]").submit();
}