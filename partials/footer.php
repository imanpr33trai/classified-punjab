<!-- footer -->
<!-- footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-12 text-center text-md-start">
                <img src="<?php echo $base_url; ?>assets/images/pnb-logo-full.svg" alt="" class="img-fluid">
            </div>
            <div
                class="col-12 d-flex flex-md-row flex-column d-xl-flex  gap-3 align-items-xl-center pt-3 justify-content-xl-between justify-content-sm-centers">
                <div class="col-lg-7 footer-links flex-wrap">
                    <a href="#">Vehicle</a>
                    <a href="#">Townhouses</a>
                    <a href="#">Video Game</a>
                    <a href="#">Boats</a>
                    <a href="#">Campers</a>
                    <a href="#">Car</a>
                    <a href="#">Clothes</a>
                    <a href="#">All</a>
                </div>
                <div class="col-lg-5 text-white  footer-newletter-con ">
                    <h6 class="poppins-bold fos-14">Send me updates & offers.</h6>
                    <div class="form-newsletter text-center text-md-end">
                        <img src="<?php echo $base_url; ?>assets/images/send.svg" alt="">
                        <input type="email" name="newsletter" id="newsletter" class="newsletter">
                    </div>
                    <h6 class="poppins-regular fos-12 mt-2">Unsubscribe any time. Privacy Policy</h6>
                </div>
            </div>
            <hr class="bg-light mt-4 mb-4">
            <div class="col-12 text-white d-flex flex-md-row flex-column ">
                <div class="fos-14 col-md-6 text-sm-center text-xxl-start">

                    © 2024 WhatNWhere. All Rights Reserved Worldwide.

                </div>
                <div class="col-md-6 lang-select text-sm-center text-xxl-end text-md-end">
                    <select name="lang" id="lang">
                        <option value="1">English</option>
                        <option value="1">Franch</option>
                        <option value="1">Hindi</option>
                        <option value="1">Chinese</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- footer -->
<!-- footer -->

<script src="<?php echo $base_url; ?>assets/js/script.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const input = document.querySelector(".head-input input");
        const categorySelect = document.getElementById("cats");
        const resultBox = document.getElementById("search-results");

        input.addEventListener("input", function () {
            const query = input.value.trim();
            const categoryId = categorySelect.value;

            if (query.length < 2) {
                resultBox.innerHTML = "";
                resultBox.classList.add("d-none");
                return;
            }

            fetch(`search.php?q=${encodeURIComponent(query)}&cat=${categoryId}`)
                .then(res => res.text())
                .then(data => {
                    resultBox.innerHTML = data;
                    resultBox.classList.remove("d-none");
                });
        });

        // Optional: hide on click outside
        document.addEventListener("click", function (e) {
            if (!resultBox.contains(e.target) && !input.contains(e.target)) {
                resultBox.classList.add("d-none");
            }
        });
    });
</script>

</body>

</html>