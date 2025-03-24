const audio = document.getElementById("audio");
const volumeSlider = document.getElementById("volume-slider");
const volumeLabel = document.getElementById("volume-label");

// Mettre à jour le volume en fonction du slider
volumeSlider.addEventListener("input", function () {
    audio.volume = volumeSlider.value;
    volumeLabel.textContent = Math.round(volumeSlider.value * 100) + "%";
});

document.addEventListener("DOMContentLoaded", () => {
    const selector = document.getElementById("languageSelector");
    const selectedLanguage = document.getElementById("selectedLanguage");
    const options = document.querySelector(".options");

    // Langues disponibles
    const languages = {
        en: "ENGLISH",
        fr: "FRANÇAIS"
    };

    // Langue par défaut
    let currentLang = "en";
    selectedLanguage.textContent = languages[currentLang];

    // Met à jour l'affichage des options
    function updateOptions() {
        options.innerHTML = ""; // Nettoie les anciennes options
        for (const [code, name] of Object.entries(languages)) {
            if (code !== currentLang) { // Ne pas afficher la langue actuelle
                const option = document.createElement("div");
                option.dataset.lang = code;
                option.textContent = name;

                // Ajout d'un écouteur d'événement pour changer la langue
                option.addEventListener("click", () => {
                    currentLang = code;
                    selectedLanguage.textContent = languages[currentLang];
                    selector.classList.remove("show");
                    updateOptions(); // Met à jour les options après sélection
                });

                options.appendChild(option);
            }
        }
    }

    // Affiche ou cache les options de langue
    selector.addEventListener("click", () => {
        selector.classList.toggle("show");
        updateOptions(); // Met à jour les options chaque fois que le sélecteur est ouvert
    });

    // Ferme le sélecteur si on clique à l'extérieur
    document.addEventListener("click", (event) => {
        if (!selector.contains(event.target)) {
            selector.classList.remove("show");
        }
    });

    updateOptions(); // Initialisation correcte au chargement
});
