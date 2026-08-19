document.addEventListener("DOMContentLoaded", function () {

    const imageInput = document.getElementById("profileImage");
    const profilePreview = document.getElementById("profilePreview");
    const navProfileImage = document.getElementById("navProfileImage");

    if (imageInput) {

        imageInput.addEventListener("change", function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            if (!allowedTypes.includes(file.type)) {

                alert(
                    "Please upload a JPG, PNG or WEBP image."
                );

                this.value = "";
                return;
            }

            if (file.size > 5 * 1024 * 1024) {

                alert(
                    "Image size must be less than 5MB."
                );

                this.value = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                profilePreview.src = event.target.result;

                if (navProfileImage) {
                    navProfileImage.src =
                        event.target.result;
                }
            };

            reader.readAsDataURL(file);

        });

    }

});


function resetProfile() {

    if (
        confirm(
            "Discard your changes?"
        )
    ) {

        window.location.reload();

    }

}