<!DOCTYPE html>
<html lang="en" class="light scroll-smooth " dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta content="HelpDesk - Online Ticket Support" name="description" />
    <meta name="website" content="https://w3bd.com" />
    <meta name="email" content="info@w3bd.com" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut" href="/favicon.png">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <script src="https://polyfill.io/v3/polyfill.min.js?features=smoothscroll,NodeList.prototype.forEach,Promise,Object.values,Object.assign,String.prototype.startsWith" defer></script>

    <script src="{{ mix('/js/app.js') }}" defer></script>
    <script>
    // Open the modal
function openModal() {
  var modal = document.getElementById("imageModal");
  var modalImg = document.getElementById("modalImg");
  var imgSrc = document.getElementById("modalImage").src;
  
  modal.style.display = "block";
  modalImg.src = imgSrc;
}

    </script>
    @routes
    @inertiaHead
</head>
<body class="font-inter leading-none antialiased">
    @inertia

    <!-- Modal Structure -->
<div id="imageModal" class="modal">
  <span class="close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="modalImg">
</div>
</body>
</html>
