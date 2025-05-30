
import React, { useEffect, useRef, useState } from 'react';

const About = () => {
  const aboutRef = useRef<HTMLDivElement>(null);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
        }
      },
      { threshold: 0.3 }
    );

    if (aboutRef.current) {
      observer.observe(aboutRef.current);
    }

    return () => observer.disconnect();
  }, []);

  return (
    <section ref={aboutRef} className="py-20 px-6 relative">
      <div className="max-w-7xl mx-auto">
        {/* Section Header */}
        <div className={`text-center mb-16 transition-all duration-1000 ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
          <h2 className="text-5xl font-bold mb-6 text-white">
            Sobre <span className="text-[#fecd02]">Nosotros</span>
          </h2>
          <p className="text-xl text-gray-300 max-w-3xl mx-auto">
            Somos la fintech colombiana que revoluciona la gestión financiera
          </p>
        </div>

        {/* Mission & Vision */}
        <div className="grid md:grid-cols-2 gap-12 mb-20">
          <div className={`bg-gradient-to-br from-gray-800/50 to-gray-900/50 p-8 rounded-2xl backdrop-blur-sm border border-gray-700/50 hover:border-[#fecd02]/50 transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-[#fecd02]/10 ${isVisible ? 'translate-x-0 opacity-100' : '-translate-x-10 opacity-0'}`} style={{ transitionDelay: '200ms' }}>
            <h3 className="text-3xl font-bold mb-6 text-[#fecd02]">Misión</h3>
            <p className="text-gray-300 text-lg leading-relaxed">
              Democratizar el acceso a servicios financieros digitales en Colombia, 
              proporcionando herramientas seguras e intuitivas que permitan a familias 
              y empresas gestionar sus finanzas de manera eficiente y transparente.
            </p>
          </div>
          
          <div className={`bg-gradient-to-br from-gray-800/50 to-gray-900/50 p-8 rounded-2xl backdrop-blur-sm border border-gray-700/50 hover:border-[#fecd02]/50 transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-[#fecd02]/10 ${isVisible ? 'translate-x-0 opacity-100' : 'translate-x-10 opacity-0'}`} style={{ transitionDelay: '400ms' }}>
            <h3 className="text-3xl font-bold mb-6 text-[#fecd02]">Visión</h3>
            <p className="text-gray-300 text-lg leading-relaxed">
              Ser la fintech líder en Colombia, reconocida por transformar la 
              experiencia financiera digital y contribuir al crecimiento económico 
              del país a través de la innovación y la inclusión financiera.
            </p>
          </div>
        </div>

        {/* Team Section */}
        <div className={`transition-all duration-1000 ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`} style={{ transitionDelay: '600ms' }}>
          <h3 className="text-4xl font-bold text-center mb-12 text-white">
            Nuestro <span className="text-[#fecd02]">Equipo</span>
          </h3>
          
          <div className="grid md:grid-cols-3 gap-8">
            {[
              { name: "María González", role: "CEO & Fundadora", expertise: "Fintech Strategy" },
              { name: "Carlos Rodríguez", role: "CTO", expertise: "Blockchain & Security" },
              { name: "Ana Martínez", role: "Head of Product", expertise: "UX/UI Design" }
            ].map((member, index) => (
              <div
                key={index}
                className={`bg-gradient-to-br from-gray-800/30 to-gray-900/30 p-6 rounded-2xl backdrop-blur-sm border border-gray-700/30 hover:border-[#fecd02]/50 transition-all duration-500 hover:scale-105 group ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}
                style={{ transitionDelay: `${800 + index * 200}ms` }}
              >
                <div className="w-24 h-24 bg-gradient-to-br from-[#fecd02] to-yellow-500 rounded-full mx-auto mb-4 flex items-center justify-center text-black font-bold text-2xl group-hover:scale-110 transition-transform duration-300">
                  {member.name.split(' ').map(n => n[0]).join('')}
                </div>
                <h4 className="text-xl font-semibold text-white text-center mb-2">{member.name}</h4>
                <p className="text-[#fecd02] text-center mb-2 font-medium">{member.role}</p>
                <p className="text-gray-400 text-center text-sm">{member.expertise}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export default About;
