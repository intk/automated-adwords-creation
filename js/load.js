function copyText(target){
  function selectElementText(element) {
    if (document.selection) {
      var range = document.body.createTextRange();
      range.moveToElementText(element);
      range.select();
    } else if (window.getSelection) {
      var range = document.createRange();
      range.selectNode(element);
      window.getSelection().removeAllRanges();
      window.getSelection().addRange(range);
    }
  }
  var element = document.getElementById(target);
  selectElementText(element);
  document.execCommand('copy', function() {
  });
  //element.remove();
}

$(document).ready(function() {
	$('.wrapper form').on('submit', function(e) {
		e.preventDefault();
		$('.loader').fadeIn(200);
		$(this).find('input[type=submit]').remove();
		$('.wrapper form').append('<span class="status">Campaigns will be created</span>');
		$.ajax({
			url: "https://picturage.nl/intk/theaterads/automateAdWords.php?getNumber=true&"+queryString,
			type: "GET",
			dataType: "json",
			success: function(data) {
				$.ajax({
						url: data.csvOutput,
						type: "GET",
						success: function(dataCSV) {
							$('.wrapper form span.status').fadeOut(200);
							$('.wrapper form .loader').fadeOut(200, function(){
								//$(this).remove();
								var textLines = dataCSV.split("\n");
								for(var i = 0; i < textLines.length; i++) {
									$('#csvInput').append(textLines[i]+"<br>");
								} 								
								
								$('#csvInput').show();
								$('.wrapper form').prepend('<span class="feedback">Campaigns have been created successfully.</span>');
								$('.wrapper form input.copyOutput').show();
							});
						}
					});
			}
		});
	});
	
	$('.wrapper form input.copyOutput').on('click', function(e) {
		copyText('csvInput');
		$('span.feedback').text('Campaigns copied to clipboard');

	});
				  
});