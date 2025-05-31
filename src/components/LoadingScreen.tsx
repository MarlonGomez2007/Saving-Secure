import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { Wallet, TrendingUp, Shield, Target } from 'lucide-react';
import '../styles/fonts.css';
import LoadingParticles from './LoadingParticles';

interface LoadingScreenProps {
  onComplete: () => void;
}

const LoadingScreen: React.FC<LoadingScreenProps> = ({ onComplete }) => {
  const [progress, setProgress] = useState(0);
  const [isMobile, setIsMobile] = useState(false);

  useEffect(() => {
    // Detectar si es dispositivo móvil
    const checkMobile = () => {
      setIsMobile(window.innerWidth <= 768);
    };
    
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    const timer = setInterval(() => {
      setProgress((prev) => {
        const newProgress = prev + 1.5;
        if (newProgress >= 100) {
          clearInterval(timer);
          onComplete();
          return 100;
        }
        return newProgress;
      });
    }, 60);

    return () => {
      clearInterval(timer);
      window.removeEventListener('resize', checkMobile);
    };
  }, [onComplete]);

  return (
    <motion.div 
      className="fixed inset-0 z-[9999] bg-[rgba(31,32,41,1)] flex items-center justify-center overflow-hidden"
      style={{ 
        fontFamily: "'Helvetica Neue', Helvetica, Arial, sans-serif",
        perspective: "1000px",
        transformStyle: "preserve-3d"
      }}
      initial={{ scale: 1, filter: "blur(0px)", rotateX: 0, y: 0 }}
      exit={{ 
        scale: 0.5,
        rotateX: -30,
        y: -100,
        opacity: 0,
        filter: "blur(20px) brightness(2)",
        backgroundColor: "rgba(31,32,41,1)"
      }}
      transition={{ 
        duration: 1,
        ease: [0.645, 0.045, 0.355, 1]
      }}
    >
      <LoadingParticles />
      
      {/* Efecto de resplandor sutil en el fondo */}
      <motion.div
        className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[20rem] sm:w-[30rem] md:w-[40rem] h-[20rem] sm:h-[30rem] md:h-[40rem] bg-[#fecd02]/10 rounded-full blur-3xl"
        animate={{
          scale: [1, 1.2, 1],
          opacity: [0.3, 0.6, 0.3],
        }}
        transition={{
          duration: 4,
          repeat: Infinity,
          ease: "easeInOut",
        }}
      />

      <div className="text-center z-10 px-4 scale-100 sm:scale-105 md:scale-110 w-full max-w-[90vw] sm:max-w-[85vw] md:max-w-[80vw]">
        <motion.div
          initial={{ y: -20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.6 }}
          className="mb-2"
        >
          <h1 className="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-helvetiker font-black tracking-[0.05em] text-white relative">
            <motion.span
              className="inline-block"
              animate={{
                textShadow: [
                  "0 0 40px rgba(255, 255, 255, 0.4)",
                  "0 0 60px rgba(255, 255, 255, 0.6)",
                  "0 0 40px rgba(255, 255, 255, 0.4)"
                ]
              }}
              transition={{
                duration: 2,
                repeat: Infinity,
                ease: "easeInOut"
              }}
            >
              Saving
            </motion.span>
          </h1>

          <motion.h2
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-helvetiker font-black tracking-[0.05em] -mt-2 sm:-mt-3 md:-mt-4"
          >
            <span 
              className="text-[#fecd02]"
              style={{
                textShadow: "0 0 30px rgba(254, 205, 2, 0.6)"
              }}
            >
              Secure
            </span>
          </motion.h2>

          <motion.p
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="text-lg sm:text-xl md:text-2xl font-helvetiker font-light tracking-wide mt-1 text-white/70"
          >
            Plataforma Financiera 
          </motion.p>
        </motion.div>

        {/* Spinner animado */}
        <motion.div
          className="relative w-16 h-16 sm:w-18 sm:h-18 md:w-20 md:h-20 mx-auto mb-4"
          initial={{ scale: 0.8, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ duration: 0.6, delay: 0.2 }}
        >
          <motion.div
            className="absolute inset-0 border-3 border-transparent border-t-[#fecd02] border-r-[#fecd02] rounded-full"
            animate={{ rotate: 360 }}
            transition={{
              duration: 2,
              repeat: Infinity,
              ease: "linear",
            }}
          />
        </motion.div>

        {/* Barra de progreso */}
        <div className="w-full max-w-[280px] sm:max-w-[340px] md:max-w-[28rem] mx-auto mb-4">
          <div className="h-2 bg-[rgba(31,32,41,1)] rounded-full overflow-hidden border border-white/5">
            <motion.div
              className="h-full bg-gradient-to-r from-[#fecd02] to-[#ffd700] rounded-full"
              style={{ width: `${progress}%` }}
              transition={{ duration: 0.3 }}
            />
          </div>
          
          <div className="flex justify-between items-center mt-2">
            <p className="text-white/60 text-sm sm:text-base md:text-lg font-light">
              Preparando experiencia
            </p>
            <p className="text-[#fecd02] text-sm sm:text-base md:text-lg font-medium">
              {Math.round(progress)}%
            </p>
          </div>
        </div>

        {/* Iconos */}
        <motion.div
          className="flex flex-wrap justify-center gap-4 sm:gap-6 md:gap-8 mt-6"
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.6, delay: 0.6 }}
        >
          {[
            { icon: Wallet, label: 'Finanzas' },
            { icon: TrendingUp, label: 'Análisis' },
            { icon: Shield, label: 'Seguridad' },
            { icon: Target, label: 'Objetivos' }
          ].map((item, index) => (
            <div key={index} className="flex flex-col items-center space-y-2">
              <motion.div
                className="p-2 sm:p-2.5 md:p-3 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10"
                animate={{ y: [-2, 2, -2] }}
                transition={{
                  duration: 2,
                  repeat: Infinity,
                  delay: index * 0.2,
                  ease: "easeInOut"
                }}
              >
                <item.icon size={isMobile ? 20 : 28} className="text-[#fecd02]" strokeWidth={1.5} />
              </motion.div>
              <span className="text-white/40 text-xs sm:text-sm font-light">
                {item.label}
              </span>
            </div>
          ))}
        </motion.div>
      </div>
    </motion.div>
  );
};

export default LoadingScreen;