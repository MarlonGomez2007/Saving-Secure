import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import Spline from '@splinetool/react-spline';

const SplineRobot = () => {
  const [currentSection, setCurrentSection] = useState('hero');

  useEffect(() => {
    const handleScroll = () => {
      const scrollY = window.scrollY;
      const sections = [
        { id: 'hero', element: document.getElementById('robot-hero') },
        { id: 'about', element: document.getElementById('robot-about') },
        { id: 'features', element: document.getElementById('robot-features') },
        { id: 'contact', element: document.getElementById('robot-contact') },
        { id: 'footer', element: document.getElementById('robot-footer') }
      ];

      // Encontrar la sección actual
      for (const section of sections) {
        if (section.element) {
          const rect = section.element.getBoundingClientRect();
          // Si más de la mitad de la sección está visible
          if (rect.top <= window.innerHeight / 2 && rect.bottom >= window.innerHeight / 2) {
            setCurrentSection(section.id);
            break;
          }
        }
      }
    };

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Check initial position
    
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const getRobotPosition = () => {
    const positions = {
      hero: { right: '-5%', left: 'auto', top: '20vh' },
      about: { right: 'auto', left: '-1%', top: '110vh' },
      features: { right: '-3%', left: 'auto', top: '310vh' },
    };
    return positions[currentSection] || positions.hero;
  };

  const getRobotSize = () => {
    return currentSection === 'hero' ? {
      width: '450px',
      height: '450px'
    } : {
      width: '450px',
      height: '450px'
    };
  };

  return (
    <motion.div 
      className="fixed pointer-events-none"
      style={{
        ...getRobotSize(),
        zIndex: 50,
        ...getRobotPosition()
      }}
      animate={{
        ...getRobotSize(),
        x: currentSection === 'about' || currentSection === 'contact' ? -30 : 30,
        transition: {
          duration: 0.5,
          ease: "easeInOut"
        }
      }}
    >
      <Spline scene="https://prod.spline.design/j-sllIB0BlSyKVCV/scene.splinecode" />
    </motion.div>
  );
};

export default SplineRobot; 