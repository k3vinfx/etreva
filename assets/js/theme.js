// theme.js

document.addEventListener("DOMContentLoaded", () => {
    const switchTheme = document.querySelector("#slider");
    
    // Activar/desactivar dark mode
    if(switchTheme) {
        switchTheme.addEventListener("change", () => {
            document.body.classList.toggle("dark-mode");
            localStorage.setItem(
                'theme', 
                document.body.classList.contains('dark-mode') ? 'dark' : 'light'
            );
        });
    }

    // Aplicar preferencia guardada al cargar
    const savedTheme = localStorage.getItem('theme');
    if(savedTheme === 'dark'){
        document.body.classList.add('dark-mode');
        if(switchTheme) switchTheme.checked = true;
    }
});