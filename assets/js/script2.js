$(document).ready(function() {
    // Inicializar el carrusel con opciones personalizadas
    $('#theCarousel').carousel({
        interval: 5000,  // Tiempo entre slides
        pause: "hover",  // Pausar en hover
        wrap: true       // Ciclo continuo
    });

    // Añadir efecto de fade suave entre slides
    $('.carousel').addClass('carousel-fade');

    // Mejorar la animación de los captions
    $('.carousel').on('slide.bs.carousel', function(e) {
        $(e.relatedTarget)
            .find('.animate-caption')
            .css({opacity: 0, transform: 'translateY(20px)'});
        
        setTimeout(function() {
            $(e.relatedTarget)
                .find('.animate-caption')
                .css({opacity: 1, transform: 'translateY(0)'});
        }, 600);
    });

    // Añadir soporte para gestos táctiles
    let touchStartX = 0;
    let touchEndX = 0;

    $('.carousel').on('touchstart', function(event) {
        touchStartX = event.originalEvent.touches[0].pageX;
    });

    $('.carousel').on('touchend', function(event) {
        touchEndX = event.originalEvent.changedTouches[0].pageX;
        handleSwipe();
    });

    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            $('#theCarousel').carousel('next');
        }
        if (touchEndX > touchStartX + 50) {
            $('#theCarousel').carousel('prev');
        }
    }
});

  // script.js
  document.addEventListener("DOMContentLoaded", function () {
    const questions = document.querySelectorAll(".faq-question");

    questions.forEach((question) => {
        question.addEventListener("click", function () {
            const answer = this.nextElementSibling;
            const isActive = this.classList.contains("active");

            // Ocultar todas las respuestas
            document.querySelectorAll(".faq-answer").forEach((ans) => {
                ans.classList.remove("show");
            });

            // Alternar la respuesta activa
            if (!isActive) {
                this.classList.add("active");
                answer.classList.add("show");
            } else {
                this.classList.remove("active");
                answer.classList.remove("show");
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.sparkling-stars .fa-star');
    const thanksMessage = document.getElementById('xanadu-thanks');
    let rated = false;

    stars.forEach(star => {
        star.addEventListener('click', function() {
            if (!rated) {
                const value = this.dataset.value;
                
                // Resetear todas las estrellas
                stars.forEach(s => s.classList.remove('active'));
                
                // Activar las estrellas hasta la seleccionada
                for(let i = 0; i < value; i++) {
                    stars[i].classList.add('active');
                }
                
                // Mostrar mensaje de agradecimiento
                thanksMessage.style.display = 'block';
                setTimeout(() => {
                    thanksMessage.classList.add('show');
                }, 50);
                
                rated = true;
            }
        });

        // Efecto hover antes de calificar
        star.addEventListener('mouseenter', function() {
            if (!rated) {
                const value = this.dataset.value;
                stars.forEach((s, index) => {
                    if (index < value) {
                        s.style.color = '#fecd02';
                        s.style.transform = 'scale(1.2)';
                    } else {
                        s.style.color = 'rgba(254, 205, 2, 0.3)';
                        s.style.transform = 'scale(1)';
                    }
                });
            }
        });

        star.addEventListener('mouseleave', function() {
            if (!rated) {
                stars.forEach(s => {
                    s.style.color = 'rgba(254, 205, 2, 0.3)';
                    s.style.transform = 'scale(1)';
                });
            }
        });
    });
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'ArrowLeft') {
        
        document.getElementById('carousel-prev').click();
    }
    if (event.key === 'ArrowRight') {
       
        document.getElementById('carousel-next').click();
    }
});

























$(document).ready(function() {
    $('.fa-star').on('click', function() {
        var rating = $(this).data('value');
        
      
        $.ajax({
            url: 'save_rating.php',
            type: 'POST',
            data: { rating: rating },
            success: function(response) {
                if (response === 'success') {
                        $('#xanadu-thanks').show();
                    } else {

                        Swal.fire({
                            title: '¡Gracias por Calificarnos!',
                            text: 'Tu opinión es muy importante para nosotros.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#f0b700',
                            background: '#f0f8ff',
                        });
                    }
            }
        });
        
      
        $('.fa-star').removeClass('checked');
        $(this).prevAll().addBack().addClass('checked');
    });
});



