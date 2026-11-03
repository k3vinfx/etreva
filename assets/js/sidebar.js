// sidebar.js

document.addEventListener("DOMContentLoaded", () => {
    const logo = document.getElementById("logo");
    const barraLateral = document.querySelector(".barra-lateral");
    const spans = document.querySelectorAll(".barra-lateral span");
    const menu = document.querySelector(".menu");
    const main = document.querySelector("main");

    // Abrir/Cerrar barra lateral con el menú
    if(menu && barraLateral) {
        menu.addEventListener("click", () => {
            barraLateral.classList.toggle("max-barra-lateral");
            const isMax = barraLateral.classList.contains("max-barra-lateral");
            menu.children[0].style.display = isMax ? "none" : "block";
            menu.children[1].style.display = isMax ? "block" : "none";

            if(window.innerWidth <= 320){
                barraLateral.classList.add("mini-barra-lateral");
                main.classList.add("min-main");
                spans.forEach(span => span.classList.add("oculto"));
            }
        });
    }

    // Minimizar barra lateral al hacer clic en el logo
    if(logo && barraLateral && main){
        logo.addEventListener("click", () => {
            barraLateral.classList.toggle("mini-barra-lateral");
            main.classList.toggle("min-main");
            spans.forEach(span => span.classList.toggle("oculto"));
        });
    }
});