function q_smilies_insert(addition) {
	try {
		tinyMCE.execCommand("mceInsertContent", false, " " + addition + " ")
	} catch (e) {
		var content = document.getElementById('content');
		var startPos = content.selectionStart;
		var endPos = content.selectionEnd;
		content.value = content.value.substring(0, startPos) + addition + content.value.substring(endPos, content.value.length);
		content.selectionStart = endPos + addition.length;
		content.selectionEnd = content.selectionStart;
		content.focus();
	}
	return false;
}

function q_smilies_input_disable(element, state) {
	var input = document.getElementById(element).getElementsByTagName("input");
	for ( var i = 0; i < input.length; i++ ) input[i].disabled = state;
}