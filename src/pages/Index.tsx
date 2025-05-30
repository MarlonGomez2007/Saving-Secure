import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import Header from '../components/Header';
import HeroSection from '../components/HeroSection';
import AboutSection from '../components/AboutSection';
import FeaturesSection from '../components/FeaturesSection';
import ContactSection from '../components/ContactSection';
import FooterSection from '../components/FooterSection';
import LoadingScreen from '../components/LoadingScreen';
import ChatBot from '../components/ChatBot';
import SplineRobot from '../components/SplineRobot';

const Index = () => {
  const [isLoading, setIsLoading] = useState(true);

  const handleLoadingComplete = () => {
    setIsLoading(false);
  };

  return (
    <div className="min-h-screen w-full bg-[rgba(31,32,41,1)]">
      <AnimatePresence mode="wait">
        {isLoading ? (
          <LoadingScreen onComplete={handleLoadingComplete} />
        ) : (
          <motion.div 
            className="min-h-screen w-full bg-[rgba(31,32,41,1)] text-white overflow-x-hidden relative"
            initial={{ 
              scale: 0.8,
              opacity: 0,
              filter: "blur(20px)"
            }}
            animate={{ 
              scale: 1,
              opacity: 1,
              filter: "blur(0px)"
            }}
            transition={{ 
              duration: 1.5,
              ease: "easeOut",
              delay: 0
            }}
          >
            <SplineRobot />
            <Header />
            
            {/* Control sections for robot */}
            <div id="robot-hero" className="min-h-screen">
              <HeroSection />
            </div>

            <div id="robot-about" className="min-h-screen">
              <AboutSection />
            </div>

            <div id="robot-features" className="min-h-screen">
              <FeaturesSection />
            </div>

            <div id="robot-contact" className="min-h-screen">
              <ContactSection />
            </div>

            <div id="robot-footer" className="min-h-screen">
              <FooterSection />
            </div>

            <ChatBot />
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default Index;
