/* ============================================================
   Online Job Portal - small helper scripts (plain JavaScript)
   All real validation happens on the server in PHP;
   these are only for a nicer user experience.
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {

    // 1. Register page: show "Company Name" only when Employer is chosen
    var companyField = document.getElementById('companyField');
    var roleRadios = document.querySelectorAll('input[name="role"]');
    function toggleCompany() {
        var employer = document.getElementById('roleEmployer');
        if (companyField && employer) {
            companyField.style.display = employer.checked ? 'block' : 'none';
        }
    }
    roleRadios.forEach(function (r) { r.addEventListener('change', toggleCompany); });
    toggleCompany();

    // 2. Profile page: preview the chosen photo before upload
    var photoInput = document.getElementById('photoInput');
    var photoPreview = document.getElementById('photoPreview');
    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                photoPreview.src = URL.createObjectURL(this.files[0]);
                photoPreview.classList.remove('d-none');
                var icon = document.getElementById('photoIcon');
                if (icon) { icon.classList.add('d-none'); }
            }
        });
    }

    // 3. Resume page: warn early if the file is not a PDF or larger than 2 MB
    var resumeInput = document.getElementById('resumeInput');
    if (resumeInput) {
        resumeInput.addEventListener('change', function () {
            var f = this.files[0];
            if (!f) { return; }
            if (!/\.pdf$/i.test(f.name)) {
                alert('Please choose a PDF file.');
                this.value = '';
            } else if (f.size > 2 * 1024 * 1024) {
                alert('File is larger than 2 MB. Please choose a smaller file.');
                this.value = '';
            }
        });
    }

    // 4. Auto-hide success / info alerts after 5 seconds
    document.querySelectorAll('.alert-success.alert-dismissible, .alert-info.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            if (window.bootstrap) { bootstrap.Alert.getOrCreateInstance(el).close(); }
        }, 5000);
    });
});
