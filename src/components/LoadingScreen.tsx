import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { Wallet, TrendingUp, Shield, Target } from 'lucide-react';
import '../styles/fonts.css';
import ChatBotParticles from './ChatBotParticles';

interface LoadingScreenProps {
  onComplete: () => void;
}

const LoadingScreen: React.FC<LoadingScreenProps> = ({ onComplete }) => {
  const [progress, setProgress] = useState(0);

  useEffect(() => {
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

    return () => clearInterval(timer);
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
      <ChatBotParticles />
      
      {/* Efecto de resplandor sutil en el fondo */}
      <motion.div
        className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40rem] h-[40rem] bg-[#fecd02]/10 rounded-full blur-3xl"
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

      <div className="text-center z-10 px-4 scale-110">
        <motion.div
          initial={{ y: -20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.6 }}
          className="mb-2"
        >
          <h1 className="text-6xl md:text-7xl lg:text-8xl font-helvetiker font-black tracking-[0.05em] text-white relative">
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
            className="text-5xl md:text-6xl lg:text-7xl font-helvetiker font-black tracking-[0.05em] -mt-4"
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
            className="text-xl md:text-2xl font-helvetiker font-light tracking-wide mt-1 text-white/70"
          >
            Plataforma Financiera 
          </motion.p>
        </motion.div>

        {/* Spinner animado */}
        <motion.div
          className="relative w-20 h-20 mx-auto mb-4"
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
        <div className="w-96 md:w-[28rem] mx-auto mb-4">
          <div className="h-2 bg-[rgba(31,32,41,1)] rounded-full overflow-hidden border border-white/5">
            <motion.div
              className="h-full bg-gradient-to-r from-[#fecd02] to-[#ffd700] rounded-full"
              style={{ width: `${progress}%` }}
              transition={{ duration: 0.3 }}
            />
          </div>
          
          <div className="flex justify-between items-center mt-2">
            <p className="text-white/60 text-lg font-light">
              Preparando experiencia
            </p>
            <p className="text-[#fecd02] text-lg font-medium">
              {Math.round(progress)}%
            </p>
          </div>
        </div>

        {/* Iconos */}
        <motion.div
          className="flex justify-center space-x-8 mt-6"
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
                className="p-3 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10"
                animate={{ y: [-2, 2, -2] }}
                transition={{
                  duration: 2,
                  repeat: Infinity,
                  delay: index * 0.2,
                  ease: "easeInOut"
                }}
              >
                <item.icon size={28} className="text-[#fecd02]" strokeWidth={1.5} />
              </motion.div>
              <span className="text-white/40 text-sm font-light">
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