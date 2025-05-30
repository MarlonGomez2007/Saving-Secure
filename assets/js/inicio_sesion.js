// Fuad Suleymanli

document.addEventListener("DOMContentLoaded", function() {
    const sign_in_btn = document.querySelector("#sign-in-btn");
    const sign_up_btn = document.querySelector("#sign-up-btn");
    const container = document.querySelector(".container");

    // Verificar si hay un parámetro 'register' en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('register')) {
        container.classList.add("sign-up-mode");
    }

    sign_up_btn.addEventListener("click", () => {
      container.classList.add("sign-up-mode");
    });

    sign_in_btn.addEventListener("click", () => {
      container.classList.remove("sign-up-mode");
    });
});