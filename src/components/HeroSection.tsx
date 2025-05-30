import React, { useEffect, useRef } from 'react';
import { motion } from 'framer-motion';
import { ArrowDown, Sparkles } from 'lucide-react';
import Scene3D from './3D/Scene3D';

const HeroSection = () => {
  const heroRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // Crear partículas flotantes mejoradas
    const createParticle = () => {
      if (!heroRef.current) return;
      
      const particle = document.createElement('div');
      particle.className = 'absolute w-2 h-2 bg-[#fecd02] rounded-full opacity-60';
      particle.style.left = Math.random() * 100 + '%';
      particle.style.top = '100%';
      particle.style.animation = 'float-up 12s linear infinite';
      particle.style.animationDelay = Math.random() * 12 + 's';
      particle.style.boxShadow = '0 0 10px rgba(254, 205, 2, 0.6)';
      
      heroRef.current.appendChild(particle);
      
      setTimeout(() => {
        if (particle.parentNode) {
          particle.parentNode.removeChild(particle);
        }
      }, 12000);
    };

    const interval = setInterval(createParticle, 800);
    return () => clearInterval(interval);
  }, []);

  const titleVariants = {
    hidden: { opacity: 0, y: 80 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 1.2,
        ease: "easeOut",
        staggerChildren: 0.1
      }
    }
  };

  const letterVariants = {
    hidden: { opacity: 0, y: 80, rotateX: -90 },
    visible: {
      opacity: 1,
      y: 0,
      rotateX: 0,
      transition: { duration: 0.8, ease: "easeOut" }
    }
  };

  return (
    <section
      id="hero"
      ref={heroRef}
      className="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-[rgba(31,32,41,1)] via-[rgba(31,32,41,0.98)] to-[rgba(31,32,41,1)]"
    >
      {/* Fondo 3D */}
      <Scene3D />
      
      {/* Efectos visuales mejorados con colores específicos */}
      <div className="absolute inset-0">
        <motion.div 
          className="absolute top-20 left-10 w-40 h-40 bg-[#fecd02]/8 rounded-full blur-3xl"
          animate={{
            scale: [1, 1.3, 1],
            opacity: [0.08, 0.15, 0.08],
            x: [0, 30, 0],
            y: [0, -20, 0],
          }}
          transition={{
            duration: 8,
            repeat: Infinity,
            ease: "easeInOut",
          }}
        />
        <motion.div 
          className="absolute bottom-20 right-10 w-60 h-60 bg-[#fecd02]/5 rounded-full blur-3xl"
          animate={{
            scale: [1.2, 1, 1.2],
            opacity: [0.05, 0.12, 0.05],
            x: [0, -40, 0],
            y: [0, 30, 0],
          }}
          transition={{
            duration: 10,
            repeat: Infinity,
            ease: "easeInOut",
            delay: 4,
          }}
        />
        <motion.div 
          className="absolute top-1/2 left-1/2 w-[800px] h-[800px] bg-[#fecd02]/3 rounded-full blur-3xl transform -translate-x-1/2 -translate-y-1/2"
          animate={{
            scale: [1, 1.1, 1],
            rotate: [0, 180, 360],
            opacity: [0.03, 0.08, 0.03],
          }}
          transition={{
            duration: 25,
            repeat: Infinity,
            ease: "linear",
          }}
        />
      </div>

      {/* Contenido principal */}
      <div className="relative z-10 text-center px-6 max-w-7xl mx-auto">
        <motion.div
          initial="hidden"
          animate="visible"
          variants={titleVariants}
        >
          <motion.h1 
            className="text-7xl md:text-8xl lg:text-9xl font-bold mb-8 text-white leading-tight"
            animate={{
              textShadow: [
                "0 0 20px rgba(254, 205, 2, 0.3)",
                "0 0 40px rgba(254, 205, 2, 0.6)",
                "0 0 20px rgba(254, 205, 2, 0.3)",
              ],
            }}
            transition={{
              duration: 4,
              repeat: Infinity,
              ease: "easeInOut",
            }}
          >
            Saving <span className="text-[#fecd02] drop-shadow-2xl">Secure</span>
          </motion.h1>

          <motion.p
            variants={letterVariants}
            className="text-2xl md:text-3xl lg:text-4xl mb-16 text-gray-200 max-w-5xl mx-auto leading-relaxed font-light"
          >
            Gestiona tus gastos de manera fácil y segura. 
            <motion.span 
              className="text-[#fecd02] font-semibold"
              animate={{
                opacity: [0.8, 1, 0.8],
              }}
              transition={{
                duration: 2,
                repeat: Infinity,
                ease: "easeInOut",
                delay: 1,
              }}
            >
              {" "}Descubre cómo podemos ayudarte.
            </motion.span>
          </motion.p>

          <motion.div
            variants={letterVariants}
            className="flex flex-col sm:flex-row gap-8 justify-center items-center"
          >
            <motion.button
              whileHover={{ 
                scale: 1.08,
                boxShadow: "0 25px 50px rgba(254, 205, 2, 0.4)",
                y: -5,
              }}
              whileTap={{ scale: 0.95 }}
              className="bg-gradient-to-r from-[#fecd02] to-[#e6b800] text-[#1F2029] px-16 py-8 rounded-full font-bold text-2xl transition-all duration-500 shadow-2xl relative overflow-hidden group border-2 border-[#fecd02]"
              onClick={() => window.location.href = '/login.html?register'}
              animate={{
                boxShadow: [
                  "0 20px 40px rgba(254, 205, 2, 0.3)",
                  "0 25px 50px rgba(254, 205, 2, 0.5)",
                  "0 20px 40px rgba(254, 205, 2, 0.3)",
                ],
              }}
              transition={{
                duration: 2,
                repeat: Infinity,
                ease: "easeInOut",
              }}
            >
              <span className="relative z-10">¡Regístrate Aquí!</span>
              <motion.div
                className="absolute inset-0 bg-white/20"
                initial={{ x: "-100%" }}
                whileHover={{ x: "100%" }}
                transition={{ duration: 0.6 }}
              />
            </motion.button>

            <motion.button
              whileHover={{ 
                scale: 1.08,
                backgroundColor: "rgba(254, 205, 2, 0.1)",
                borderColor: "rgba(254, 205, 2, 0.8)",
                y: -5,
              }}
              whileTap={{ scale: 0.95 }}
              className="border-3 border-[#fecd02]/60 text-[#fecd02] px-16 py-8 rounded-full font-bold text-2xl hover:bg-[#fecd02] hover:text-[#1F2029] transition-all duration-500 backdrop-blur-lg bg-[#1F2029]/20"
              onClick={() => {
                const aboutSection = document.getElementById('about');
                aboutSection?.scrollIntoView({ behavior: 'smooth' });
              }}
            >
              Conoce Más
            </motion.button>
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
};

export default HeroSection;
