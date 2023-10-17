

// Création d'un listener sur tous les boutons répondre
document.querySelectorAll("[data-reply]").forEach(element => {
    element.addEventListener("click", function () {
        console.log(this);
    });
});
