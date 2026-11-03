


<style>
    .main-footer {
        background-color: var(--dark);
    color: white;
    padding-top: 0.5rem;
    position: sticky;  /* ¡Esto es lo clave! */
    bottom: 0;         /* Lo pega al fondo */
    z-index: 100;      /* Asegura que esté sobre otros elementos */
    width: 100%;       /* Ocupa todo el ancho */
    margin-top: auto;  /* Empuja el footer hacia abajo */
    }
    

    
    .footer-content {
        position: relative;
        z-index: 0;
    }
    
    /* Mantén todos tus estilos actuales aquí */
    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }
    
    /* ... (conserva todos tus estilos existentes) ... */
    
    .payment-methods-wrapper {
        position: relative;
        margin: 0.5rem auto;
        max-width: 900px;
    }
    
    .payment-methods {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
        align-items: center;
        padding: 10px;
        background: rgba(0,0,0,0.3);
        border-radius: 10px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        transition: all 0.5s ease;
    }
    
    .payment-methods:hover {
        background: rgba(0,0,0,0.4);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.3);
    }
    
    .payment-methods img {
        height: auto;
        width: auto;
        max-height: 50px;
        max-width: 80px;
        object-fit: contain;
        transition: all 0.3s ease;
        filter: grayscale(20%) brightness(90%);
    }
    
    .payment-methods img:hover {
        transform: scale(1.15);
        filter: grayscale(0%) brightness(110%);
    }
    
    @media (max-width: 768px) {
        .payment-methods {
            padding: 15px 10px;
            gap: 10px;
        }
        
        .payment-methods img {
            max-width: 60px;
            max-height: 30px;
        }
    }
</style>

<footer class="main-footer">


        

             
             
           <div id="particles-js" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 0;"></div>

          
         

    </div>
</footer>



<!-- Agrega esto antes de cerrar el body -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    particlesJS('particles-js', {
        "particles": {
            "number": {
                "value": 60,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#ffffff"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                }
            },
            "opacity": {
                "value": 0.5,
                "random": true,
                "anim": {
                    "enable": true,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": true,
                    "speed": 2,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.2,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 1,
                "direction": "none",
                "random": true,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "grab"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 140,
                    "line_linked": {
                        "opacity": 0.5
                    }
                },
                "push": {
                    "particles_nb": 4
                }
            }
        },
        "retina_detect": true
    });
});
</script>