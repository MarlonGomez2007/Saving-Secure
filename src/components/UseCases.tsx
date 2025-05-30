
import React, { useEffect, useRef, useState } from 'react';

const UseCases = () => {
  const useCasesRef = useRef<HTMLDivElement>(null);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
        }
      },
      { threshold: 0.2 }
    );

    if (useCasesRef.current) {
      observer.observe(useCasesRef.current);
    }

    return () => observer.disconnect();
  }, []);

  const useCases = [
    {
      title: "Para Familias",
      description: "Gestiona el presupuesto familiar, ahorra para metas comunes y educa financieramente a tus hijos",
      features: ["Presupuesto familiar compartido", "Metas de ahorro grupales", "Educación financiera", "Control de gastos"],
      color: "from-blue-500/20 to-purple-500/20",
      accent: "border-blue-400/30 hover:border-blue-400/60"
    },
    {
      title: "Para Amigos",
      description: "Divide gastos de viajes, eventos y actividades grupales de manera justa y transparente",
      features: ["División de gastos", "Pagos grupales", "Recordatorios automáticos", "Historial transparente"],
      color: "from-green-500/20 to-teal-500/20",
      accent: "border-green-400/30 hover:border-green-400/60"
    },
    {
      title: "Para Empresas",
      description: "Optimiza la gestión financiera empresarial con herramientas profesionales y reporting avanzado",
      features: ["Gestión de flujo de caja", "Reportes financieros", "Facturación digital", "Control de inventarios"],
      color: "from-orange-500/20 to-red-500/20",
      accent: "border-orange-400/30 hover:border-orange-400/60"
    }
  ];

  return (
    <section ref={useCasesRef} className="py-20 px-6 relative">
      <div className="max-w-7xl mx-auto">
        <div className={`text-center mb-16 transition-all duration-1000 ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
          <h2 className="text-5xl font-bold mb-6 text-white">
            Casos de <span className="text-[#fecd02]">Uso</span>
          </h2>
          <p className="text-xl text-gray-300 max-w-3xl mx-auto">
            Descubre cómo Saving Secure se adapta a tus necesidades específicas
          </p>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {useCases.map((useCase, index) => (
            <div
              key={index}
              className={`relative group transition-all duration-700 ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-20 opacity-0'}`}
              style={{ transitionDelay: `${index * 200}ms` }}
            >
              <div className={`bg-gradient-to-br ${useCase.color} backdrop-blur-sm border ${useCase.accent} rounded-2xl p-8 h-full hover:scale-105 transition-all duration-500 hover:shadow-2xl hover:shadow-[#fecd02]/10`}>
                <h3 className="text-2xl font-bold mb-4 text-white group-hover:text-[#fecd02] transition-colors duration-300">
                  {useCase.title}
                </h3>
                <p className="text-gray-300 mb-6 leading-relaxed">
                  {useCase.description}
                </p>
                
                <ul className="space-y-3">
                  {useCase.features.map((feature, featureIndex) => (
                    <li
                      key={featureIndex}
                      className={`flex items-center text-gray-300 transition-all duration-500 ${isVisible ? 'translate-x-0 opacity-100' : '-translate-x-4 opacity-0'}`}
                      style={{ transitionDelay: `${index * 200 + featureIndex * 100 + 400}ms` }}
                    >
                      <div className="w-2 h-2 bg-[#fecd02] rounded-full mr-3 group-hover:scale-125 transition-transform duration-300"></div>
                      {feature}
                    </li>
                  ))}
                </ul>

                <button className="mt-8 w-full bg-[#fecd02]/10 border border-[#fecd02]/30 text-[#fecd02] py-3 rounded-full font-semibold hover:bg-[#fecd02] hover:text-black transition-all duration-300 hover:scale-105">
                  Explorar Más
                </button>
              </div>
              
              {/* Floating indicator */}
              <div className="absolute -top-4 -right-4 w-8 h-8 bg-[#fecd02] rounded-full flex items-center justify-center text-black font-bold text-sm opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:scale-110">
                {index + 1}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default UseCases;
