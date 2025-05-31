import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { createPortal } from 'react-dom';

const ScrollToTop = () => {
  const [isButtonHovered, setIsButtonHovered] = useState(false);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const checkAboutVisibility = () => {
      const aboutSection = document.getElementById('robot-about');
      if (aboutSection) {
        const rect = aboutSection.getBoundingClientRect();
        // Mostrar el botón cuando hayamos pasado el inicio de la sección about
        setIsVisible(window.scrollY >= rect.top + window.pageYOffset);
      }
    };

    window.addEventListener('scroll', checkAboutVisibility);
    // Verificar visibilidad inicial
    checkAboutVisibility();

    return () => window.removeEventListener('scroll', checkAboutVisibility);
  }, []);

  const scrollToTop = () => {
    const heroSection = document.getElementById('robot-hero');
    if (heroSection) {
      heroSection.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  };

  const buttonVariants = {
    initial: { scale: 1 },
    hover: { 
      scale: 1.1,
      rotate: -10,
      boxShadow: '0 0 35px rgba(254, 205, 2, 0.7)',
      transition: { 
        duration: 0.3, 
        ease: "easeOut",
        rotate: {
          duration: 0.2,
          type: "spring",
          stiffness: 300
        }
      }
    },
    tap: { scale: 0.95, rotate: 0 }
  };

  return createPortal(
    <div className="relative" style={{ position: 'relative', zIndex: 9998 }}>
      <AnimatePresence>
        {isVisible && (
          <motion.button
            onClick={scrollToTop}
            className="fixed bottom-6 left-6 bg-[#fecd02] text-black p-3 md:p-4 rounded-full shadow-lg hover:bg-yellow-400 transition-all duration-300 z-[9998] group overflow-hidden"
            style={{ position: 'fixed' }}
            variants={buttonVariants}
            initial={{ 
              opacity: 0, 
              scale: 0.3, 
              y: 50,
              rotate: -180,
              filter: 'blur(10px)'
            }}
            animate={{ 
              opacity: 1, 
              scale: 1,
              y: 0,
              rotate: 0,
              filter: 'blur(0px)',
              transition: {
                duration: 0.6,
                type: "spring",
                stiffness: 200,
                damping: 20,
                filter: {
                  duration: 0.4
                }
              }
            }}
            exit={{ 
              opacity: 0,
              scale: 0.3,
              y: 20,
              rotate: 180,
              filter: 'blur(10px)',
              transition: { 
                duration: 0.5,
                ease: "easeInOut"
              }
            }}
            whileHover="hover"
            whileTap="tap"
            onHoverStart={() => setIsButtonHovered(true)}
            onHoverEnd={() => setIsButtonHovered(false)}
          >
            {/* Efecto de brillo mejorado */}
            <motion.div
              className="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-transparent -skew-x-45"
              initial={{ x: -200, opacity: 0 }}
              animate={{
                x: [null, 200],
                opacity: [0, 1, 0],
              }}
              transition={{
                duration: 1.5,
                delay: 0.3,
                ease: "easeOut"
              }}
            />

            {/* Contenedor del ícono con animación */}
            <motion.div
              className="relative flex items-center justify-center w-8 h-8"
              animate={{
                y: [0, -2, 0],
              }}
              transition={{
                duration: 2,
                repeat: Infinity,
                repeatType: "reverse",
                ease: "easeInOut"
              }}
            >
              {/* Triángulo innovador simplificado */}
              <svg 
                viewBox="0 0 24 24" 
                className="w-full h-full"
              >
                <defs>
                  <filter id="glow">
                    <feGaussianBlur stdDeviation="0.3" result="coloredBlur"/>
                    <feMerge>
                      <feMergeNode in="coloredBlur"/>
                      <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                  </filter>
                </defs>
                
                {/* Triángulo limpio */}
                <path
                  d="M12 4L20 18H4L12 4Z"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  filter="url(#glow)"
                />

                {/* Puntos decorativos en las esquinas */}
                <circle cx="12" cy="4" r="1" fill="currentColor" />
                <circle cx="4" cy="18" r="0.5" fill="currentColor" />
                <circle cx="20" cy="18" r="0.5" fill="currentColor" />
              </svg>
            </motion.div>

            {/* Tooltip mejorado */}
            <motion.div
              initial={{ opacity: 0, scale: 0.5, y: 10 }}
              animate={{ 
                opacity: isButtonHovered ? 1 : 0,
                scale: isButtonHovered ? 1 : 0.5,
                y: isButtonHovered ? 0 : 10
              }}
              transition={{ 
                duration: 0.3,
                type: "spring",
                stiffness: 200
              }}
              className="absolute -top-12 left-1/2 -translate-x-1/2 bg-black text-white px-4 py-2 rounded-full text-sm whitespace-nowrap shadow-lg"
            >
              Volver arriba ↑
            </motion.div>
          </motion.button>
        )}
      </AnimatePresence>
    </div>,
    document.body
  );
};

export default ScrollToTop;