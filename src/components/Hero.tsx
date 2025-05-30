
import React, { useEffect, useRef } from 'react';
import { ArrowDown } from 'lucide-react';

const Hero = () => {
  const heroRef = useRef<HTMLDivElement>(null);
  const particlesRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const createParticle = () => {
      if (!particlesRef.current) return;
      
      const particle = document.createElement('div');
      particle.className = 'particle';
      particle.style.cssText = `
        position: absolute;
        width: 4px;
        height: 4px;
        background: #fecd02;
        border-radius: 50%;
        opacity: 0.7;
        left: ${Math.random() * 100}%;
        top: 100%;
        animation: float-up 8s linear infinite;
        animation-delay: ${Math.random() * 8}s;
      `;
      
      particlesRef.current.appendChild(particle);
      
      setTimeout(() => {
        if (particle.parentNode) {
          particle.parentNode.removeChild(particle);
        }
      }, 8000);
    };

    const interval = setInterval(createParticle, 300);
    
    return () => clearInterval(interval);
  }, []);

  return (
    <section ref={heroRef} className="relative min-h-screen flex items-center justify-center overflow-hidden">
      {/* Animated Background */}
      <div className="absolute inset-0">
        <div className="absolute inset-0 bg-gradient-to-br from-[rgba(31,32,41,0.8)] via-[rgba(31,32,41,0.9)] to-[rgba(31,32,41,1)]"></div>
        <div 
          ref={particlesRef}
          className="absolute inset-0"
          style={{
            background: `
              radial-gradient(circle at 20% 80%, rgba(254, 205, 2, 0.1) 0%, transparent 50%),
              radial-gradient(circle at 80% 20%, rgba(254, 205, 2, 0.1) 0%, transparent 50%)
            `
          }}
        ></div>
      </div>

      {/* Content */}
      <div className="relative z-10 text-center px-6 max-w-6xl mx-auto">
        <div className="animate-fade-in">
          <h1 className="text-6xl md:text-8xl font-bold mb-6 bg-gradient-to-r from-white via-[#fecd02] to-white bg-clip-text text-transparent animate-pulse">
            Saving Secure
          </h1>
          <p className="text-xl md:text-2xl mb-8 text-gray-300 max-w-3xl mx-auto leading-relaxed">
            Tu fintech colombiana de confianza para gestionar tus finanzas personales y empresariales con seguridad y simplicidad
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <button className="bg-[#fecd02] text-black px-8 py-4 rounded-full font-semibold text-lg hover:bg-yellow-400 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-[#fecd02]/25">
              Comienza Ahora
            </button>
            <button className="border-2 border-[#fecd02] text-[#fecd02] px-8 py-4 rounded-full font-semibold text-lg hover:bg-[#fecd02] hover:text-black transition-all duration-300 hover:scale-105">
              Conoce Más
            </button>
          </div>
        </div>
        
        {/* Scroll Indicator */}
        <div className="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
          <ArrowDown className="text-[#fecd02] w-8 h-8" />
        </div>
      </div>

      {/* Floating Cards Animation */}
      <div className="absolute top-20 left-10 w-20 h-20 bg-[#fecd02]/20 rounded-lg animate-[float_6s_ease-in-out_infinite] hidden lg:block"></div>
      <div className="absolute top-40 right-20 w-16 h-16 bg-[#fecd02]/30 rounded-full animate-[float_8s_ease-in-out_infinite_reverse] hidden lg:block"></div>
      <div className="absolute bottom-40 left-20 w-12 h-12 bg-[#fecd02]/25 rounded-lg animate-[float_7s_ease-in-out_infinite] hidden lg:block"></div>
    </section>
  );
};

export default Hero;
