$(function () {
	$('.uploadButton').click(function (e) {
		$('#fileupload').trigger("click");
		e.preventDefault();
	});

	$('#fileupload').fileupload({
		url: '/core/upload.php',
		dataType: 'json',
		disableImageResize: /Android(?!.*Chrome)|Opera/
				.test(window.navigator && navigator.userAgent),
		previewMaxWidth: 128,
		previewMinWidth: 128,
		previewMaxHeight: 128,
		previewMinHeight: 128,
		previewCrop: true,
		acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		maxFileSize: 10000000, // 10 MB
		imageMaxWidth: 1024,
		imageMaxHeight: 5000000,
		disableExifThumbnail: true,
		messages: {
			acceptFileTypes: 'Ten typ plików nie jest akceptowany.',
			maxFileSize: 'Rozmiar pliku jest za duży.'
		}
	}).on('fileuploadprocessalways', function (e, data) {
		var index = data.index,
				file = data.files[index];
		if (file.error) {
			showError(file.error);
		}
		else if (file.preview) {
			$(".uploadButton").html(file.preview);
			$(".uploadButton > canvas").addClass("img-circle");
		}
	}).on('fileuploaddone', function (e, data) {
		$.each(data.files, function (index, file) {
			if (!data.result.status == 'success') {
				if (typeof (data.result.msg) != "undefined")
					showError(data.result.msg);
				else
					showError('Serwer się obraził i nie przyjął pliku ;(');
				console.log("ERROR");
			}
		});
	}).on('fileuploadfail', function () {
		showError('Błąd połączenia z serwerem, spróbuj ponownie.');
	}).on('fileuploadstop', function () {
		$('.submitButton').removeClass("image");
		if (!$('.submitButton').hasClass('video')) {
			$('.submitButton').trigger("loading");
			$('.submitButton').attr("disabled", false);
		}
	});
});