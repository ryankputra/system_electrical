document.addEventListener("DOMContentLoaded", function () {
	const uploadBtn = document.getElementById("uploadBtn");
	const fileInput = document.getElementById("formFile");
	const uploadForm = document.getElementById("uploadForm");

	if (!uploadBtn) return; // Tidak ada tombol upload di halaman ini

	uploadBtn.addEventListener("click", function (e) {
		e.preventDefault();

		if (!fileInput) {
			alert("Form file tidak ditemukan.");
			return;
		}

		const file = fileInput.files && fileInput.files[0];

		// Check if a file is uploaded
		if (!file) {
			alert("Harap pilih file sebelum mengupload!");
			return;
		}

		// Validate file type (Only allow .xlsx)
		if (
			file.type !==
			"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
		) {
			alert("Format file tidak valid! Hanya file .xlsx yang diperbolehkan.");
			return;
		}

		// If validation passes, submit the form
		if (uploadForm) {
			uploadForm.submit();
		} else {
			alert("Form upload tidak ditemukan.");
		}
	});
});