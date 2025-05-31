import React, { useEffect, useState, Suspense } from 'react';
import { motion } from 'framer-motion';
import Spline from '@splinetool/react-spline';

const SplineRobot = () => {
  const [currentSection, setCurrentSection] = useState('hero');
  const [windowWidth, setWindowWidth] = useState(window.innerWidth);
  const [isLoading, setIsLoading] = useState(true);
  const [isMobileDevice, setIsMobileDevice] = useState(false);

  useEffect(() => {
    const checkDeviceCapabilities = () => {
      // Detectar si es móvil
      const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
      setIsMobileDevice(isMobile);
    };

    const handleResize = () => {
      setWindowWidth(window.innerWidth);
    };

    const handleScroll = () => {
      if (!window.requestAnimationFrame) {
        setTimeout(handleScroll, 66);
        return;
      }
      
      window.requestAnimationFrame(() => {
        const sections = [
          { id: 'hero', element: document.getElementById('robot-hero') },
          { id: 'about', element: document.getElementById('robot-about') },
          { id: 'features', element: document.getElementById('robot-features') },
          { id: 'contact', element: document.getElementById('robot-contact') },
          { id: 'footer', element: document.getElementById('robot-footer') }
        ];

        for (const section of sections) {
          if (section.element) {
            const rect = section.element.getBoundingClientRect();
            if (rect.top <= window.innerHeight / 2 && rect.bottom >= window.innerHeight / 2) {
              setCurrentSection(section.id);
              break;
            }
          }
        }
      });
    };

    checkDeviceCapabilities();
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('resize', handleResize, { passive: true });
    handleScroll();
    
    return () => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('resize', handleResize);
    };
  }, []);

  // No renderizar nada si es móvil o tablet
  if (isMobileDevice || windowWidth <= 768) {
    return null;
  }

  const getRobotPosition = () => {
    const basePositions = {
      hero: { right: '-5%', left: 'auto', top: '20vh' },
      about: { right: 'auto', left: '-1%', top: '110vh' },
      features: { right: '-3%', left: 'auto', top: '310vh' },
    };

    // Small desktop positions (769px - 1024px)
    if (windowWidth <= 1024) {
      return {
        hero: { right: '-12%', left: 'auto', top: '25vh', transform: 'scale(0.75)' },
        about: { right: 'auto', left: '-8%', top: '115vh', transform: 'scale(0.75)' },
        features: { right: '-10%', left: 'auto', top: '315vh', transform: 'scale(0.75)' },
      }[currentSection] || basePositions.hero;
    }

    return basePositions[currentSection] || basePositions.hero;
  };

  const getRobotSize = () => {
    return {
      width: '450px',
      height: '450px'
    };
  };

  const handleSplineLoad = () => {
    setIsLoading(false);
  };

  const LoadingFallback = () => (
    <div 
      style={{
        ...getRobotSize(),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'rgba(254, 205, 2, 0.1)',
        borderRadius: '10px'
      }}
    >
      <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#fecd02]" />
    </div>
  );

  return (
    <motion.div 
      className="fixed pointer-events-none"
      style={{
        ...getRobotSize(),
        zIndex: 50,
        ...getRobotPosition(),
        willChange: 'transform',
        transformOrigin: 'center center',
      }}
      animate={{
        ...getRobotSize(),
        x: currentSection === 'about' || currentSection === 'contact' ? -10 : 10,
        transition: {
          duration: 0.8,
          ease: "easeInOut"
        }
      }}
    >
      <Suspense fallback={<LoadingFallback />}>
        <Spline 
          scene="https://prod.spline.design/j-sllIB0BlSyKVCV/scene.splinecode"
          onLoad={handleSplineLoad}
        />
      </Suspense>
    </motion.div>
  );
};

export default SplineRobot; 